<?php

namespace App\Http\Controllers\Controllers_local;

use Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\models_local\Order;
use App\models_local\Cafeteria\OrderKitchen;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;

use App\Helpers\CurrentApp;
use App\models_local\Waiter;
use App\models_local\Board;
use App\Http\Controllers\Controllers_local\SellsController;
use App\Helpers\HistorialCafeteria\HistoryCoffeHelper;
// Illuminate
use Illuminate\Support\Facades\Log;

// EVENTS
use App\Events\Cafeteria\OrderCreated;
use App\Events\Cafeteria\OrderUpdated;
use App\Events\Cafeteria\OrderRemoved;

class OrderController extends Controller
{
  /*public function newOrderBase64(Request $request){
    Log::info("PETICION ORDEN JESUS:", $request->all());
    $b64 = $request->input('externalBase64');

    $b64Body = json_decode(base64_decode($b64));
    $request->merge($b64Body);
    Log::info("PRE.PETICION ORDEN JESUS:", $request->all());

    return OrderController::newOrder($request);
  }*/

  public function newOrder(Request $request){
    Log::info("NEW ORDEN, WITH BODY:", $request->all());
    $_request = $request->all();

    $validaciones = [
      'total' => 'required',
      'products' => 'required',
    ];
    if(CurrentApp::ConfStr('modulos.cafeteria')){
      $validaciones = [
        'total'     => 'required',
        'products'  => 'required',
        'waiter_id' => 'required',
        // Si el modo garzon NO esta activado, la mesa es obligatoria, por el contrario si esta activado, deja de ser obligatoria
        'board_id'  => (!CurrentApp::ConfStr('modulos.cafeteria.submodulos.garzon_mode')) ? 'required' : '',
      ];
    }

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description')) {
      $validaciones['description'] = 'string';
    }

    // Validator
    $validator = Validator::make($_request, $validaciones);
    if($validator->fails()) return response()->json($validator->errors(), 400);

    if(CurrentApp::ConfStr('modulos.cafeteria')) {

      // Verificando existencia de la mesa y que no este ocupada (Si es que se envio una mesa)
      if (isset($_request['board_id']) && $_request['board_id']) {
        $board = Board::find($_request['board_id']);
        if(!$board) return response()->json("Mesa no encontrada",404);
        $verifyStateBoard = Order::where('board_id', $_request['board_id'])->where('state', 'en espera')->first();
        if($verifyStateBoard) return response()->json("La mesa seleccionada ya se encuentra ocupada",404);
      }

      // Verificando existencia del mesero
      $waiter = Waiter::find($_request['waiter_id']);
      if(!$waiter) return response()->json("Mesero no encontrado",404);

    }

    // Creando orden
    $order = Order::createOrder($_request);
    if(!$order) return response()->json("Error del servidor",500);

    if (CurrentApp::ConfStr('modulos.cafeteria')) {
      if (CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')) {
        $productsOrder = OrderKitchen::orderProductsToKitchenProducts($request['products']);
        OrderKitchen::createNew([
          'order_id' => $order->id,
          'products' => $productsOrder,
          'kitchen_state' => 'pending',
        ]);
      }

      $order->waiter  = $waiter;
      $order->board   = (isset($board) && $board) ? $board : null;
    }

    $b64Doc = $this->printPDF($order, 'ticket');
    $result = [
      'ticket' => $b64Doc
    ];

    if(isset($_request['ticket']) && CurrentApp::ConfStr('modulos.cafeteria.ajustes.ticket_sell')){
      $sells = new SellsController();
      $result['order'] = $sells->newSell($request, $order->id);
    }

    HistoryCoffeHelper::create('coffe_order_on-create', 'Se ha creado la orden '.$order->id, $order);
    broadcast(new OrderCreated($order))->toOthers();

    return response()->json($result,200);
  }

  public function editOrder(Request $request, $id) {

    $order = Order::where('id', $id)->where('state', 'en espera')->first();
    if(!$order) return response()->json('La orden no existe o ya fue procesada',400);

    $_request = $request->all();

    // Verificando existencia de la mesa (si existe)
    if (isset($_request['board_id']) && $_request['board_id']) {
      $board = Board::find($_request['board_id']);
      if(!$board) return response()->json("Mesa no encontrada", 404);
    }
    // Verificando existencia del mesero
    $waiter = Waiter::find($_request['waiter_id']);
    if(!$waiter) return response()->json("Mesero no encontrado", 404);

    // Editando orden
    $order = Order::editOrder($_request, $order);
    if(!$order) return response()->json("Error del servidor",500);
    
    if (CurrentApp::ConfStr('modulos.cafeteria')) {
      if(CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')) {
        $productsOrder = OrderKitchen::orderProductsToKitchenProducts($request['products']);
        OrderKitchen::createNew([
          'order_id' => $order->id,
          'products' => $productsOrder,
          'kitchen_state' => 'pending',
        ]);
      }
    }

    $order->waiter    = $waiter;
    if (isset($board) && $board) {
      $order->board   = $board;
    }
    $order->products  = $_request['products'];
    $order->total     = $_request['total'];
    $b64Doc           = $this->printPDF($order, 'ticket');

    $result = [
      'ticket' => $b64Doc
    ];

    if(isset($_request['ticket']) && CurrentApp::ConfStr('modulos.cafeteria.ajustes.ticket_sell')){
      $sells = new SellsController();
      $result['order'] = $sells->newSell($request, $order->id);
    }

    HistoryCoffeHelper::create('coffe_order_on-update', 'Se ha editado la orden '.$order->id, $order);
    try {
      broadcast(new OrderUpdated($order))->toOthers(); // pusher qliao <3
    } catch (\Exception $e) {
      //
    }
  
    // Correcto
    return response()->json($result,200);
  }

  public function removeProduct(Request $request, $id) {

    $order = Order::where('id', $id)->where('state', 'en espera')->first();

    HistoryCoffeHelper::create('coffe_order_onremoveattempt', 'Se intentara eliminar productos de la orden '.$order->id, $order);

    if(!$order) return response()->json('La orden no existe o ya fue procesada',400);

    $_request = $request->all();

    // Editando orden
    $order = Order::removeProduct($_request, $order);
    if(!$order) return response()->json("Error del servidor",500);

    HistoryCoffeHelper::create('coffe_order_on-remove-product', 'Se ha eliminado un producto de la orden '.$order->id, $order);
    try {
      broadcast(new OrderUpdated($order))->toOthers();
    } catch (\Exception $e) {
      //
    }

    // Correcto
    return response()->json($order, 200);
  }

  public function getOrder($code) {
    $query = Order::where('barcode', $code)->where('state', 'en espera')->first();
    if(!$query) return response()->json('La orden no existe o ya fue procesada',400);
    return response()->json($query,200);
  }

  /*
    llamado desde cafeteria/printOrderTotal
    completeOrder.vue
  */

  public function printOrderTotal(Request $request, $id){
    $_request = $request->all();



    $order = Order::find($id);
    if(!$order) return response()->json('La orden no existe o ya fue procesada',400);

    // actualizar tip y discount (propina y descuento)

    Order::updateTipNDiscount($_request, $order);

    // Verificando existencia de la mesa (en caso de haber mesa)
    if ($order->board_id) {
      $order->board = Board::find($order->board_id);
      if(!$order->board) return response()->json("Mesa no encontrada",404);
    }

    // Verificando existencia del mesero
    $order->waiter = Waiter::find($order->waiter_id);
    if(!$order->waiter) return response()->json("Mesero no encontrado",404);

    $order['ticket_description'] = CurrentApp::ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description');
    $b64Doc = $this->printPDF($order, 'ticket_deuda');
    HistoryCoffeHelper::create('coffe_order_on-print-total', 'Se ha imprimido el total de una orden '.$order->id, $order);
    return response()->json($b64Doc,200);

  }

  public function printPDF($order, $type){
    $app = CurrentApp::App();
    $order['envs'] = json_decode($app->environment_vars);
    $order['products'] = json_decode($order['products']);
    $order['no_code_bar'] = CurrentApp::ConfStr('modulos.ventas.submodulo.ticket.ajustes.no_code_bar');
    $order['view_total'] = CurrentApp::ConfStr('modulos.ventas.submodulo.ticket.ajustes.view_total');
    $order['view_subtotal'] = CurrentApp::ConfStr('modulos.ventas.submodulo.ticket.ajustes.view_subtotal');
    $order['lower_case'] = CurrentApp::ConfStr('modulos.ventas.submodulo.ticket.ajustes.lower_case');
    $order['ticket_description'] = CurrentApp::ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description');

    $size = array(0,0,227,600);
    // Creando recibo
    $pdf = \PDF::loadView($type,compact('order'))->setPaper($size);
    $name = $type.'_'.uniqid().'.pdf';
    // Guardando archivo
    Storage::put('public/pdf/'.$name, $pdf->output());
    // Transformando archivo
    $b64Doc = chunk_split(base64_encode($pdf->output()));
    return $b64Doc;
  }

  /*
    board_id
    waiter_id
    ticket

  */

  public function newOrderApp(Request $request){
    Log::info("[OrderController] order/app:", $request->all());
    $_request = $request->all();

  
    if(CurrentApp::ConfStr('modulos.cafeteria')){
      $validaciones = [
        //'total'     => 'required',
        'products'  => 'required',
        'waiter_id' => 'required',
        // Si el modo garzon NO esta activado, la mesa es obligatoria, por el contrario si esta activado, deja de ser obligatoria
        'board_id'  => (!CurrentApp::ConfStr('modulos.cafeteria.submodulos.garzon_mode')) ? 'required' : '',
      ];
    }

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description')) {
      $validaciones['description'] = 'string';
    }

    // Validator
    $validator = Validator::make($_request, $validaciones);
    if($validator->fails()) return response()->json($validator->errors(), 400);

    // cacular total

    $total = 0;
    $products = json_decode ($_request['products']);

   //return json_encode($products);

     foreach ($products as $product) {

      $database2 = Config::get('database.connections.mysql_local.database');
      $pquery = DB::table($database2.'.products')
      ->select('products.price')
      ->where('products.trash', 0)
      ->where('id', $product->id)->get();

      $total += floatval ($product->quantity) * floatval ($pquery[0]->price);

     }
   
     $_request['total'] = $total;
    // return json_encode($total);



    if(CurrentApp::ConfStr('modulos.cafeteria')) {

      // Verificando existencia de la mesa y que no este ocupada (Si es que se envio una mesa)
      if (isset($_request['board_id']) && $_request['board_id']) {
        $board = Board::find($_request['board_id']);
        if(!$board) return response()->json("Mesa no encontrada",404);
        $verifyStateBoard = Order::where('board_id', $_request['board_id'])->where('state', 'en espera')->first();
        if($verifyStateBoard) return response()->json("La mesa seleccionada ya se encuentra ocupada",404);
      }

      // Verificando existencia del mesero
      $waiter = Waiter::find($_request['waiter_id']);
      if(!$waiter) return response()->json("Mesero no encontrado",404);

    }

    // Creando orden
    $order = Order::createOrder($_request);
    if(!$order) return response()->json("Error del servidor",500);

    if (CurrentApp::ConfStr('modulos.cafeteria')) {
      if (CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')) {
        $productsOrder = OrderKitchen::orderProductsToKitchenProducts($request['products']);
        OrderKitchen::createNew([
          'order_id' => $order->id,
          'products' => $productsOrder,
          'kitchen_state' => 'pending',
        ]);
      }

      $order->waiter  = $waiter;
      $order->board   = (isset($board) && $board) ? $board : null;
    }

    // $b64Doc = $this->printPDF($order, 'ticket');
    // $result = [
    //   'ticket' => $b64Doc
    // ];

    if(isset($_request['ticket']) && CurrentApp::ConfStr('modulos.cafeteria.ajustes.ticket_sell')){
      $sells = new SellsController();
      $result['order'] = $sells->newSell($request, $order->id);
    }

    HistoryCoffeHelper::create('coffe_order_on-create', 'Se ha creado la orden '.$order->id, $order);
    broadcast(new OrderCreated($order))->toOthers();

    return response()->json($result,200);
  }

}
