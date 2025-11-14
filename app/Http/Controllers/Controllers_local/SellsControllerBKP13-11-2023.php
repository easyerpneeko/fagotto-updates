<?php

namespace App\Http\Controllers\Controllers_local;

// Controllers
use App\Http\Controllers\SIIController;

// Models
use App\models_local\ProductSell;
use App\models_local\Product;
use App\models_local\UserApp;
use App\models_local\Client;
use App\models_local\Folio;
use App\models_local\Order;
use App\models_local\Sell;
use App\models_local\Waiter;

// Helpers
use App\Helpers\CurrentApp;
use App\Helpers\MPage;
use Config;

// Laravel
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Dompdf\Dompdf;
use DateTime;
use Auth;

//Services
use App\Classes\ServicesSII;

//EVENTS
use App\Events\Cafeteria\OrderProccesed;

class SellsController extends Controller
{
  public function fastSell(Request $request){
    $_request = $request->all();
    $_request['fast_sell'] = 1;

    $validaciones = [
      'total' => 'required',
    ];

    // Validator
    $validator = Validator::make($_request, $validaciones);
    if($validator->fails()) return response()->json($validator->errors(), 400);

    // Buscando usuario que realizao la venta
    $user = Auth::user()->id;
    if(!$user) return response()->json("Usuario no encontrado",404);
    $_request['user'] = $user;

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio')){
        $client = [
          'name',
          'lastname',
          'rut',
          'city',
          'comuna',
          'razon_social',
          'direction',
          'giro',
          'phone',
        ];
        foreach ($client as $key) {
          if($key == 'rut' || $key == 'phone') $_request[$key] = '00000000';
          else $_request[$key] = 'Venta rapida';
        }
        // Verificando que el cliente no exista
        $clientNow = Client::where('rut', $_request['rut'])->first();
        if(!$clientNow){
          $client = Client::createClient($_request);
          if(!$client) return response()->json("Error del servidor",500);
        }else{
          $client = Client::editClient($_request, $clientNow->id);
          if(!$client) return response()->json("Error del servidor",500);
        }
        $_request['client'] = $client->id;
      }
    }

    // Creando venta
    $sell = Sell::createSell($_request);
    if(!$sell) return response()->json("Error del servidor",500);

    // Creando cada columna en la pivote
    $database2 = Config::get('database.connections.mysql_local.database');
    $product = DB::table($database2.'.products')->where('key_system', 1)->first();
    if(!$product) return response()->json("Producto de venta rapida no encontrado",404);
    $price = floatval($product->price);
    $newProductSell = [
      "price" =>  $price,
      "quantity"  =>  round($request->input('total')),
      "unitary_price" =>  1,
      "product" =>  $product->id,
      "sell"  =>  $sell->id,
    ];
    // Creando pivote con el producto
    $created = ProductSell::createProductSell($newProductSell);
    if(!$created) return response()->json("Error del servidor",500);

    $query = array('id' => $sell->id, 'response_folio' => false);

    if(CurrentApp::ConfStr('modulos.ventas.submodulos.sii')){
      if(CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta')){
        $asingFolio = SIIController::processBoleta($request,$sell->id);
        if (isset($asingFolio)) {
          $query['response_folio'] = $asingFolio['content'];
          if(!$asingFolio['success']) return response()->json($query, $asingFolio['code']);
        }
      }else{
        $query['response_folio'] = $_request['type_sell'];;
      }
    }

    return response()->json($query,200);
  }

  /*
      Metodo para crear una nueva venta (Sell)
      Se llega desde la ruta /api/local/sell
  */
  public function newSell(Request $request, $order_id = false){
    //return response()->json($request, 400);
    $_request = $request->all();
    $_request['fast_sell'] = 0;

    $validaciones = [
      'total' => 'required',
      'products' => 'required|json',
    ];

    //Submodulos
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes') && !isset($_request['other_type'])) {
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio')){
        $validaciones = [
          'name' => 'required|string|max:32|min:2',
          'lastname' => 'required|string|max:32|min:2',
          'rut' => 'required|string|max:12',
          'city' => 'required|string|max:20',
          'comuna' => 'required|string|max:20',
          'razon_social' => 'required|string|max:100',
        ];
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
          $validaciones['direction'] =  'required|string|max:70';
          $validaciones['giro'] =  'required|string|max:80';
        }
      }else {
        $validaciones = [
          'name' => 'string|max:32|min:2',
          'lastname' => 'string|max:32|min:2',
          'rut' => 'string|max:12',
          'city' => 'string|max:20',
          'comuna' => 'string|max:20',
          'razon_social' => 'string|max:100',
        ];
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
          $validaciones['direction'] =  'string|max:70';
          $validaciones['giro'] =  'string|max:80';
        }
      }
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono')){
        $validaciones['phone'] =  'string|max:16';
      }
    }

    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) $validaciones['gananciaTotal'] =  'required';
    else $validaciones['gananciaTotal'] =  'nullable';

    $validator = Validator::make($_request, $validaciones);
    if($validator->fails()){
      if(isset($_request['ticket'])) return $validator->errors();
      else return response()->json($validator->errors(), 400);
    }

    $user = Auth::user()->id;
    if(!$user){
      if(isset($_request['ticket'])) return "Usuario no encontrado";
      else return response()->json("Usuario no encontrado",404);
    }
    $_request['user'] = $user;

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes') && !isset($_request['other_type'])) {
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio')){
        // Verificando que el cliente no exista
        $clientNow = Client::where('rut', $_request['rut'])->first();
        if(!$clientNow){
          $client = Client::createClient($_request);
          if(!$client){
            if(isset($_request['ticket'])) return "Error del servidor";
            else return response()->json("Error del servidor",500);
          }
        }else{
          $client = Client::editClient($_request, $clientNow->id);
          if(!$client){
            if(isset($_request['ticket'])) return "Error del servidor";
            else return response()->json("Error del servidor",500);
          }
        }
        $_request['client'] = $client->id;
      }else{
        if (isset($_request['rut'])) {
          // Verificando que el cliente no exista
          $clientNow = Client::where('rut', $_request['rut'])->first();
          if(!$clientNow){
            $client = Client::createClient($_request);
            if(!$client){
              if(isset($_request['ticket'])) return "Error del servidor";
              else return response()->json("Error del servidor",500);
            }
          }else{
            $client = Client::editClient($_request, $clientNow->id);
            if(!$client){
              if(isset($_request['ticket'])) return "Error del servidor";
              else return response()->json("Error del servidor",500);
            }
          }
          $_request['client'] = $client->id;
        }
      }
    }

    $items = json_decode($_request['products']);
    foreach ($items as $item){
      $product = Product::find($item->id);
      if(!$product){
        if(isset($_request['ticket'])) return "Producto no encontrado";
        else return response()->json("Producto no encontrado",404);
      }
      // if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')){
      //   if ($product->stock < $item->quantity){
      //     if(isset($_request['ticket'])) return "No hay suficiente stock";
      //     else return response()->json("No hay suficiente stock",400);
      //   }
      // }
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')){
        if ($product->min_quantity > $item->quantity) {
          if(isset($_request['ticket'])) return "El producto ".$product->name." permite comprar minimo ".$product->min_quantity." productos";
          else return response()->json("El producto ".$product->name." permite comprar minimo ".$product->min_quantity." productos",400);
        }
      }
    }

    if (!isset($_request['fecha'])) $_request['fecha'] = $request['fecha'];
    
    // Creando venta
    $sell = Sell::createSell($_request);
    if(!$sell){
      if(isset($_request['ticket'])) return "Error del servidor";
      else return response()->json("Error del servidor",500);
    }

    // Si la venta se crea con exito pasar el estado de la orden a procesada si existe una orden
    if(isset($_request['order'])) {
      $order = Order::find($_request['order']);
      if($order){
        $order->tip = $sell->tip;
        $order->discount = $sell->discount;
        $order->state = 'procesada';
        $order->save();
        if (CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')){
          $order->closeOrdersKitchens();
          broadcast(new OrderProccesed($order, $sell))->toOthers();
        }
      }
    }
    if($order_id){
      $order = Order::find($order_id);
      if($order){
        $order->state = 'procesada';
        $order->save();
        if (CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')){
          $order->closeOrdersKitchens();
          broadcast(new OrderProccesed($order, $sell))->toOthers();
        }
      }
    }

    // Creando cada columna en la pivote de cada producto por cada venta
    foreach ($items as $item){
      $product = Product::find($item->id);
      if(!$product){
        if(isset($_request['ticket'])) return "Producto no encontrado";
        else return response()->json("Producto no encontrado",404);
      }
      $price = floatval($item->price);
      $newProductSell = [
        "price" =>  $price,
        "quantity"  =>  $item->quantity,
        "unitary_price" =>  $item->price,
        "product" =>  $item->id,
        "sell"  =>  $sell->id,
      ];
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')){
        $newProductSell['gananciaTotal'] =  $item->ganancia;
      }
      $created = ProductSell::createProductSell($newProductSell);
      if(!$created){
        if(isset($_request['ticket'])) return "Error del servidor";
        else return response()->json("Error del servidor",500);
      }
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')){
        $product->stock = (float) $product->stock - $item->quantity;
        if(!$product->save()){
          if(isset($_request['ticket'])) return 'Error en la base de datos';
          else return response()->json('Error en la base de datos',500);
        }
      }
    }

    $query = array('id' => $sell->id, 'response_folio' => false);

    if(CurrentApp::ConfStr('modulos.ventas.submodulos.sii')){
      if(CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura') || CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta')){
        if(isset($_request['type_sell']) && $_request['type_sell'] == 'factura') $asingFolio = SIIController::processFactura($request,$sell->id);
        if(isset($_request['type_sell']) && $_request['type_sell'] == 'boleta') $asingFolio = SIIController::processBoleta($request,$sell->id);
        if (isset($asingFolio)) {
          if(!$asingFolio['success']){
            $query['response_folio'] = $asingFolio['content'];
            if(isset($_request['ticket'])) return [$query, $asingFolio['code']];
            else return response()->json($query, $asingFolio['code']);
          }
          // $b64Doc = chunk_split(base64_encode(file_get_contents($asingFolio['content'])));
          $query['response_folio'] = $asingFolio['content'];
        }
      }else{
        if(!isset($_request['type_sell']))  $query['response_folio'] = 'boleta_local';
        if(isset($_request['other_type'])) $query['response_folio_other'] = $_request['other_type'];
      }

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local') && !isset($_request['type_sell'])) {
        // impresion de boleta con formato SII (regulacion)
        $query['response_folio'] = $this->printPDF($request,$sell->id,true,true);
      }

      if (isset($_request['type_sell'])) {
        if ($_request['type_sell'] == "other") $query['response_folio'] = $this->printPDF($request,$sell->id,true,true);
      }

    } // CurrentApp::ConfStr('modulos.ventas.submodulos.sii')

    if(isset($_request['ticket'])) return $query;
    else return response()->json($query,200);
  } ///////////////////////////////// newSells end ////////////////////////////////////////////////

  public function getSells(Request $request){
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.sells')
    ->whereIn('sells.trash', [0,1])
    ->leftJoin($database2.'.users', 'users.id','sells.user')
    ->select('sells.*','users.fullname', 'users.avatar');
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')){
      $pquery->leftJoin($database2.'.clients', 'clients.id','sells.client');
    }
    if (!$pquery) return response()->json('Error del servidor',500);

    //Ordenamientos
    $orders = ['id','total','created_at','user'];
    // //Filtrados
    $filters = [];
    if ($request->input('todaySells')) {
      $today = new DateTime(now());
      $today->setTime(00,00,00);
      $pquery->where('sells.created_at','>=', $today);
    }
    if ($request->input('startDate')) {
      $pquery->where('sells.created_at','>=', $request->input('startDate'));
    }
    if ($request->input('endDate')) {
      $pquery->where('sells.created_at','<=', $request->input('endDate'));
    }
    if ($request->has('orderBy_date')) {
      $pquery->orderBy('sells.created_at', $request->input('orderBy_date'));
    }
    if ($request->input('searchInSell')) {
      $name = $request->input('searchInSell');
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')){
        $sellId = DB::table($database2.'.sells')->where('id',$name)->where('sells.trash', 0)->first();
        if(!$sellId){
          $pquery->whereRaw("(clients.name like '%$name%' OR clients.lastname like '%$name%' OR clients.rut like '%$name%')");
        }else{
          $pquery->where('sells.id',$name);
        }
      }else{
        $pquery->where('sells.id',$name);
      }
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,10,'','sells',$orders,$filters, false, null);
    foreach ($Paginated['items'] as $sell) {
      $user = DB::table($database2.'.users')->where('id',$sell->user_trash)->first();
      $sell->user_trash = $sell->user_trash;
      if (!empty($user)) { 
        $sell->user_trash = $user->fullname;
      }
      //Añadiendo productos individuales
      $products = [];
      $productSells = DB::table($database2.'.products_sells')
      ->where('products_sells.sell',$sell->id)
      ->leftJoin($database2.'.products','products.id','products_sells.product')
      ->select('products_sells.price as totalPrice','products_sells.quantity','products_sells.unitary_price','products.name')
      ->get();
      foreach ($productSells as $productSell) {
        $products[] = $productSell;
      }
      $sell->products = $products;

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')){
        $client = DB::table($database2.'.clients')->where('id',$sell->client)->first();
        if ($client) {
          $sell->client = $client;
        }
      }

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')){
        //Definiendo si ya fue procesado como factura o boleta o guia de despacho
        $folio = Folio::where('sell_id', $sell->id)->first();

        if($sell->other_type != null){
          if($sell->other_type == 'banco') $sell->type = 'Trasnbank';
          else $sell->type = $sell->other_type;
        }else if ($folio) {
          $sell->type = $folio->type;
          $sell->glosa_sii = $folio->glosa_sii;
        }else{
          $sell->type = null;
          $sell->glosa_sii = null;
          if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local')) {
            $sell->type = 'Boleta Local';
          }
        }

      }
    }
    return response()->json($Paginated);
  }

  public function getSell($id,$print=false){
    $database2 = Config::get('database.connections.mysql_local.database');
    $find = Sell::find($id);
    if (!$find) {
      return response()->json('Venta no encontrada',404);
    }
    $pquery = DB::table($database2.'.sells')
    ->where('sells.trash', 0)->where('sells.id', $id)
    ->leftJoin($database2.'.users', 'users.id','sells.user')
    ->select('sells.*','users.username', 'users.avatar')->first();

    if (!$pquery) return response()->json('Error del servidor',500);

    $products = [];
    $productSells = DB::table($database2.'.products_sells')
    ->where('products_sells.sell',$pquery->id)
    ->leftJoin($database2.'.products','products.id','products_sells.product')
    ->select('products_sells.price as totalPrice','products_sells.quantity','products.name', 'products_sells.unitary_price')
    ->get();

    foreach ($productSells as $productSell) {
      /*$number = $productSell->price;
      $number = (float)$number;
      $number = number_format($number, 2, ",", ".");
      $number = str_replace(',00', '', $number);
      $productSell->price = $number;

      $number = $productSell->totalPrice;
      $number = (float)$number;
      $number = number_format($number, 2, ",", ".");
      $number = str_replace(',00', '', $number);
      $productSell->totalPrice = $number;*/

      $products[] = $productSell;
    }
    $pquery->products = $products;
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')){
      $client = DB::table($database2.'.clients')->where('id',$pquery->client)->first();
      if ($client) {
        $pquery->client = $client;
      }
    }
    if ($print) {
      return (array)$pquery;
    }
    return response()->json($pquery);
  }

  public function removeSell($id){
    //usuario que elimino la venta;
    $user = Auth::user()->id;
    $sell = Sell::find($id);
    if(!$sell) return response()->json('Venta no encontrada',404);
    $sell->trash = 1;
    $sell->user_trash = 1;
    if(!$sell->save()) return response()->json('Error en la base de datos',500);
    return response()->json('Venta removida exitosamente',200);
  }

  public function getClient($rut) {

    $database2 = Config::get('database.connections.mysql_local.database');
    $client = DB::table($database2.'.clients')->where('rut', $rut)->first();

    if (!$client) {
      $response = ServicesSII::getPerson($rut);
      if ($response->ok)
        $client = $response->content;
    }

    if (!$client)
      return response()->json('Usuario no encontrado99',404);

    return response()->json($client);

  }

  public function editClient(Request $request,$sell){
    $sell = Sell::find($sell);
    if (!$sell) {
      return response()->json('Venta no encontrada',404);
    }
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii') && $sell->siiState != 'en_espera') {
      return response()->json('Esta venta se encuentra en proceso o ya fue procesada',400);
    }

    $_request = $request->all();

    $validaciones = [
      'name' => 'required|string|max:32|min:2',
      'lastname' => 'required|string|max:32|min:2',
      'rut' => 'required|string|max:12',
      'city' => 'required|string|max:20',
      'comuna' => 'required|string|max:20',
      'razon_social' => 'required|string|max:100'
    ];
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
      $validaciones['direction'] =  'required|string|max:70';
      $validaciones['giro'] =  'required|string|max:80';
    }
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono')){
      $validaciones['phone'] =  'string|max:16';
    }
    $validator = Validator::make($_request, $validaciones);
    if($validator->fails()) return response()->json($validator->errors(), 400);

    $clientNow = Client::where('rut', $_request['rut'])->first();
    if(!$clientNow){
      $client = Client::createClient($_request);
      if(!$client) return response()->json("Error del servidor",500);
    }else{
      $client = Client::editClient($_request, $clientNow->id);
      if(!$client) return response()->json("Error del servidor",500);
    }
    $sell->client = $client->id;
    $sell->save();
    return response()->json('Cliente editado exitosamente',200);
  }

  public function printPDF(Request $request,$id, $boleta = false, $self = false){
    $database = Config::get('database.connections.mysql_local.database');
    
    $sell = $this->getSell($id,true);
    //$order = DB::table($database.'.orders')->where('id', $sell['order_id'])->first();
    //$waiter = DB::table($database.'.waiters')->where('id', $order['waiter_id'])->first();
    $app = CurrentApp::App();
    $sell['envs'] = json_decode($app->environment_vars);
    //$sell['waiter_name'] = $waiter['name'];
    $size = array(0,0,227,600);
    if ($boleta || $request->input('boleta_local') == 1) {
      $pdf = \PDF::loadView('boleta',compact('sell'))->setPaper($size);
      $name = 'boleta_'.uniqid().'.pdf';
    }else{
      $pdf = \PDF::loadView('factura',compact('sell'))->setPaper($size);
      $name = 'recibo_'.uniqid().'.pdf';
    }
    Storage::put('public/pdf/'.$name, $pdf->output());
    /*$path = Storage::url('public/pdf/'.$name);
    if ($boleta && $self) {
      return $path;
    }
    return response()->json($path,200);*/
    $b64Doc = chunk_split(base64_encode($pdf->output()));
    if ($boleta && $self) {
      return $b64Doc;
    }
    return response()->json($b64Doc,200);
  }
}
