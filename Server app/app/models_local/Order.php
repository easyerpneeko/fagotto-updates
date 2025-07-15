<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

// Models
use App\models_local\Product;
use App\models_local\Board;
use App\models_local\Waiter;
use App\models_local\Cafeteria\OrderKitchen;

// Helpers
use App\Helpers\CurrentApp;

use Keygen;
use App\Events\Cafeteria\OrderRemoved;

class Order extends Model
{
  protected $connection = 'mysql_local';
  protected $fillable = [
    'barcode',
    'products',
    'state',
    'total',
    'gananciaTotal',
    'waiter_id',
    'board_id',
    'description',
    'client_ticket',
    'tip',
    'discount'
  ];

  /** Si tiene una mesa... */
  public function board()
  {
      return $this->belongsTo(Board::class);
  }

  public function waiterAssigned()
  {
      return $this->belongsTo(Waiter::class, 'waiter_id', 'id');
  }

  /** Obtener cantidad de productos (cantidad_productos) */
  public function getCantidadProductosAttribute()
  {
      $number = 0;
      if (!$this->products) return 0;
      $products = json_decode($this->products);
      if (!$products) return 0;

      //\Illuminate\Support\Facades\Log::info($products);

      foreach ($products as $product)
        $number += (float) $product->quantity;

      return $number;
  }

  public static function createOrder($request){
    $keysAllow = [
      'barcode',
      'products',
      'state',
      'total',
      'gananciaTotal',
      'tip',
      'discount'
    ];
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'gananciaTotal';
    }

    if (CurrentApp::ConfStr('modulos.cafeteria')) {
      $keysAllow[] = 'waiter_id';
      $keysAllow[] = 'board_id';
    }

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description')) {
      $keysAllow[] = 'description';
    }

    if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.ticket_sell_client')) {
      $keysAllow[] = 'client_ticket';
    }

    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }

    //Codigo unico de aplicacion
    $itemToSave['barcode'] = Order::generateCodeUPCA();

    //Estado de la orden
    $itemToSave['state'] = 'en espera';

    return Order::create($itemToSave);
  }

  public static function updateTipNDiscount($request, $order) {
    $keysAllow = [
      'tip',
      'discount'
    ];
    $order->tip = $request['tip'];
    $order->discount = $request['discount'];

    if(!$order->save()) return false;
    return $order;
  }

  public static function editOrder($request, $order){
    $keysAllow = [
      'barcode',
      'products',
      'state',
      'total',
      'gananciaTotal',
      'tip',
      'discount'
    ];

    if (CurrentApp::ConfStr('modulos.cafeteria')) {
      $keysAllow[] = 'waiter_id';
      $keysAllow[] = 'board_id';
    }

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description')) {
      $keysAllow[] = 'description';

      if (!$order->description) $order->description = '';

      if (isset($request['description']) && $request['description'])
        $order->description = $order->description.PHP_EOL.$request['description'];
    }

    if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.ticket_sell_client')) {
      $keysAllow[] = 'client_ticket';

      if (!$order->client_ticket) $order->client_ticket = '';

      if (isset($request['client_ticket']) && $request['client_ticket'])
        $order->client_ticket = $order->client_ticket.PHP_EOL.$request['client_ticket'];
    }

    // Calculate jsonProducts
    $newProducts = json_decode($request['products']);
    $oldProducts = json_decode($order->products);
    $products = Order::calculateJsonProducts($oldProducts, $newProducts);

    // Calculate total
    $order->total = 0;
    foreach ($products as $product) $order->total += $product->subtotal;

    // Asignando datos
    $order->products = json_encode($products);
    if (CurrentApp::ConfStr('modulos.cafeteria')) {
      $order->waiter_id = $request['waiter_id'];
      $order->board_id = $request['board_id'];
    }

    if(!$order->save()) return false;
    return $order;
  }

  public function ordersKitchen()
  {
      return $this->hasMany(OrderKitchen::class, 'order_id', 'id');
  }

  public function cancelOrdersKitchens() {
    $this->ordersKitchen()->get()->map(function ($orderKitchen) {
      $orderKitchen->markAsCancelled();
    });
  }

  public function closeOrdersKitchens() {
    $this->ordersKitchen()->get()->map(function ($orderKitchen) {
      $orderKitchen->markAsClosed();
    });
  }

  public static function removeProduct($request, $order){
    // Calculate jsonProducts
    $products = json_decode($request['products']);

    // Calculate total
    $order->total = 0;
    foreach ($products as $product) $order->total += $product->subtotal;

    // Asignando nuevos productos
    $order->products = $request['products'];

    if($order->total == 0 && count($products) == 0) {
      if (CurrentApp::ConfStr('modulos.cafeteria')) {
        if (CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')) {
          /*OrderKitchen::where('order_id', $order->id)->update([
            'order_id' => null,
            'kitchen_state' => 'cancelled'
          ]);*/
          $order->cancelOrdersKitchens();
        }
      }

      broadcast(new OrderRemoved($order))->toOthers();
      if(!$order->delete()) return false;
      return $order;
    }

    if(!$order->save()) return false;
    return $order;
  }

  //Esta logica debe ir dentro del modelo del crear aplicacion, no dentro del controlador
  //PD: Debe ir en el repositorio o en una clase de logica de negocio
  public static function generateCode(){
    return 'TICKET-' . Keygen::bytes()->generate(
      function($key) {
        // Generate a random numeric key
        $random = Keygen::numeric()->generate();

        // Manipulate the random bytes with the numeric key
        return substr(md5($key . $random . strrev($key)), mt_rand(0,8), 20);
      },
      function($key) {
        // Add a (-) after every fourth character in the key
        return join('-', str_split($key, 4));
      },
      'strtoupper'
    );
  }

  public static function generateCodeUPCA() {
    $digits = 11;
    do {
      $barCode = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
      $barCode = $barCode.Self::calculate_upc_check_digit((string) $barCode);
    } while (Order::where('barcode',$barCode)->first() || Product::where('barcode',$barCode)->first());
    return $barCode;
  }

  public static function calculate_upc_check_digit($upc_code) {
    $checkDigit = -1; // -1 == failure
    $upc = substr($upc_code,0,11);
    // send in a 11 or 12 digit upc code only
    if (strlen($upc) == 11 && strlen($upc_code) <= 12) {
    	$oddPositions = (integer) $upc[0] + (integer) $upc[2] + (integer) $upc[4] + (integer) $upc[6] + (integer) $upc[8] + (integer) $upc[10];
      $oddPositions *= 3;
      $evenPositions= (integer) $upc[1] + (integer) $upc[3] + (integer) $upc[5] + (integer) $upc[7] + (integer) $upc[9];
      $sumEvenOdd = $oddPositions + $evenPositions;
      $checkDigit = (10 - ($sumEvenOdd % 10)) % 10;
    }
    return $checkDigit;
  }

  public static function calculateJsonProducts($oldProducts, $newProducts){
    $products = [];
    foreach ($oldProducts as $oldProduct) {
      $productFind = false;
      foreach ($newProducts as $newProduct) {
        if($oldProduct->id == $newProduct->id){
          $productFind = $oldProduct;
          $productFind->quantity = $oldProduct->quantity + $newProduct->quantity;
          $productFind->subtotal = $productFind->quantity * $productFind->price;
        }
      }
      if($productFind) $products[] = $productFind;
      else $products[] = $oldProduct;
    }
    foreach ($newProducts as $newProduct) {
      $productFind = false;
      foreach ($products as $product) {
        if($newProduct->id == $product->id) $productFind = true;
      }
      if($productFind == false) $products[] = $newProduct;
    }
    return $products;
  }

}
