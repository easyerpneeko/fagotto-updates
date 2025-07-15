<?php

namespace App\Http\Controllers\Controllers_local;

// Controllers
use App\Http\Controllers\SIIController;

// Models
use App\models_local\ProductGuia; // productos en la guia de despacho
use App\models_local\Product; // productos
use App\models_local\ProductSell;
use App\models_local\UserApp;
use App\models_local\Client;
use App\models_local\Folio;
use App\models_local\Sell;
use App\models_local\Order;
use App\models_local\GuiasDespacho;
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

class GuiaController extends Controller
{

  public function nuevaGuia(Request $request, $order_id = false)
  {
    $_request = $request->all();

    //return response()->json($request, 400); //debug request, comment to check info

    $validaciones = [
      'total' => 'required',
      'products' => 'required|json',
    ];

    //Submodulos
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes') && !isset($_request['other_type'])) {
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio')) {
        $validaciones = [
          'name' => 'required|string|max:32|min:2',
          'lastname' => 'required|string|max:32|min:2',
          'rut' => 'required|string|max:12',
          'city' => 'required|string|max:20',
          'comuna' => 'required|string|max:20',
          'razon_social' => 'required|string|max:100',
          //'comment'=>'required|string',
          'translado' => 'required|numeric',
          'despacho' => 'required|numeric'
        ];
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
          $validaciones['direction'] =  'required|string|max:70';
          $validaciones['giro'] =  'required|string|max:40';
        }
      } else {
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
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono')) {
        $validaciones['phone'] =  'string|max:16';
      }
    }

    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) $validaciones['gananciaTotal'] =  'required';
    else $validaciones['gananciaTotal'] =  'nullable';

    $validator = Validator::make($_request, $validaciones);
    if ($validator->fails()) {
      if (isset($_request['ticket'])) return $validator->errors();
      else return response()->json($validator->errors(), 400);
    }

    $user = Auth::user()->id;
    if (!$user) {
      if (isset($_request['ticket'])) return "Usuario no encontrado";
      else return response()->json("Usuario no encontrado", 404);
    }
    $_request['user'] = $user;

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes') && !isset($_request['other_type'])) {
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio')) {
        // Verificando que el cliente no exista
        $clientNow = Client::where('rut', $_request['rut'])->first();
        if (!$clientNow) {
          $client = Client::createClient($_request);
          if (!$client) {
            if (isset($_request['ticket'])) return "Error del servidor";
            else return response()->json("Error del servidor", 500);
          }
        } else {
          $client = Client::editClient($_request, $clientNow->id);
          if (!$client) {
            if (isset($_request['ticket'])) return "Error del servidor";
            else return response()->json("Error del servidor", 500);
          }
        }
        $_request['client'] = $client->id;
      } else {
        if (isset($_request['rut'])) {
          // Verificando que el cliente no exista
          $clientNow = Client::where('rut', $_request['rut'])->first();
          if (!$clientNow) {
            $client = Client::createClient($_request);
            if (!$client) {
              if (isset($_request['ticket'])) return "Error del servidor";
              else return response()->json("Error del servidor", 500);
            }
          } else {
            $client = Client::editClient($_request, $clientNow->id);
            if (!$client) {
              if (isset($_request['ticket'])) return "Error del servidor";
              else return response()->json("Error del servidor", 500);
            }
          }
          $_request['client'] = $client->id;
        }
      }
    }

    $items = json_decode($_request['products']);
    foreach ($items as $item) {
      $product = Product::find($item->id);
      if (!$product) {
        if (isset($_request['ticket'])) return "Producto no encontrado";
        else return response()->json("Producto no encontrado", 404);
      }
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')) {
        if ($product->stock < $item->quantity) {
          if (isset($_request['ticket'])) return "No hay suficiente stock";
          else return response()->json("No hay suficiente stock", 400);
        }
      }
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')) {
        if ($product->min_quantity > $item->quantity) {
          if (isset($_request['ticket'])) return "El producto " . $product->name . " permite comprar minimo " . $product->min_quantity . " productos";
          else return response()->json("El producto " . $product->name . " permite comprar minimo " . $product->min_quantity . " productos", 400);
        }
      }
    }

    // agregando campos faltantes a $_request
    $_request['translado'] = $request['translado'];
    $_request['despacho'] = $request['despacho'];
    $_request['comment'] = $request['comment'];

    // Creando venta
    $guia = GuiasDespacho::createGuiasDespacho($_request);
    if (!$guia) {
      if (isset($_request['ticket'])) return "Error del servidor";
      else return response()->json("Error del servidor", 500);
    }


    // Creando cada columna en la pivote de cada producto por cada venta
    foreach ($items as $item) {
      $product = Product::find($item->id);
      if (!$product) {
        if (isset($_request['ticket'])) return "Producto no encontrado";
        else return response()->json("Producto no encontrado", 404);
      }
      $price = floatval($item->price);
      $newProductSell = [
        "price" =>  $price,
        "quantity"  =>  $item->quantity,
        "unitary_price" =>  $item->price,
        "product" =>  $item->id,
        "guia"  =>  $guia->id,
      ];
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $newProductSell['gananciaTotal'] =  $item->ganancia;
      }
      $created = ProductGuia::createProductGuia($newProductSell);
      if (!$created) {
        if (isset($_request['ticket'])) return "Error del servidor";
        else return response()->json("Error del servidor", 500);
      }
      // if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')){
      //   $product->stock = (float) $product->stock - $item->quantity;
      //   if(!$product->save()){
      //     if(isset($_request['ticket'])) return 'Error en la base de datos';
      //     else return response()->json('Error en la base de datos',500);
      //   }
      // }
    }

    $query = array('id' => $guia->id, 'response_folio' => false);

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
      $asingFolio = SIIController::processGuiaDespacho($request, $guia->id);
      // if(CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura') || CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta')){
      //   if(isset($_request['type_sell']) && $_request['type_sell'] == 'factura') $asingFolio = SIIController::processFactura($request,$sell->id);
      //   if(isset($_request['type_sell']) && $_request['type_sell'] == 'boleta') $asingFolio = SIIController::processBoleta($request,$sell->id);
      //   if (isset($asingFolio)) {
      //     if(!$asingFolio['success']){
      //       $query['response_folio'] = $asingFolio['content'];
      //       if(isset($_request['ticket'])) return [$query, $asingFolio['code']];
      //       else return response()->json($query, $asingFolio['code']);
      //     }
      //     // $b64Doc = chunk_split(base64_encode(file_get_contents($asingFolio['content'])));
      $query['response_folio'] = $asingFolio['content'];
      //   }
      // }else{
      //   if(!isset($_request['type_sell']))  $query['response_folio'] = 'boleta_local';
      //   if(isset($_request['other_type'])) $query['response_folio_other'] = $_request['other_type'];
      // }

      //if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local')) {
      // impresion de boleta con formato SII (regulacion)
      //$query['response_folio'] = $this->printPDF($request,$guia->id,true,true);
      //}



    } // CurrentApp::ConfStr('modulos.ventas.submodulos.sii')
    // $sell = Sell::createSell($_request);

    $this->guiaDespachoVenta($_request, $guia);
    if (isset($_request['ticket'])) return $query;
    else return response()->json($query, 200);
  } ///////////////////////////////// newSells end ////////////////////////////////////////////////

  private function guiaDespachoVenta($_request, $guia)
  {
    $guiaFolio = GuiasDespacho::find($guia->id);
    $items = json_decode($_request['products']);
    if (!empty($guiaFolio->guia_folio)) {
      $verify = Folio::where('guia_id', $guia->id)->where('type', 'guia_de_despacho')->first();
      if ($verify && $verify->pdf_url) {
        $user = Auth::user()->id;
        $data = [
          'total' => $_request['total'],
          'fast_sell' => 0,
          'user'  => $user,
          'sell_folio' => $verify->folio,
          'typeSell' => 'other',
          'other_type' => 'guia_despacho'
        ];
        $createSell = Sell::createSell($data);
        if ($createSell) {
          $verify->sell_id = $createSell->id;
          $verify->save();
          // Creando cada columna en la pivote de cada producto por cada venta
          foreach ($items as $item) {
            $product = Product::find($item->id);
            if (!$product) {
              if (isset($_request['ticket'])) return "Producto no encontrado";
              else return response()->json("Producto no encontrado", 404);
            }
            $price = floatval($item->price);
            $newProducts = [
              "price" =>  $price,
              "quantity"  =>  $item->quantity,
              "unitary_price" =>  $item->price,
              "product" =>  $item->id,
              "sell"  =>  $createSell->id,
            ];
            if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
              $newProducts['gananciaTotal'] =  $item->ganancia;
            }
            $created = ProductSell::createProductSell($newProducts);
            if (!$created) {
              if (isset($_request['ticket'])) return "Error del servidor";
              else return response()->json("Error del servidor", 500);
            }
            if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')) {
              $product->stock = (float) $product->stock - $item->quantity;
              if (!$product->save()) {
                if (isset($_request['ticket'])) return 'Error en la base de datos';
                else return response()->json('Error en la base de datos', 500);
              }
            }
          }
        }
      }
    }
  }

  public function printPDF(Request $request, $id, $boleta = false, $self = false)
  {
    $database = Config::get('database.connections.mysql_local.database');

    //$sell = $this->getGuia($id,true);
    $folio = Folio::where('guia_id', $id)->where('type', 'guia_de_despacho')->first();
    if (!$folio) {
      $folio = Folio::where('sell_id', $id)->where('type', 'guia_de_despacho')->first();
    }
    //$order = DB::table($database.'.orders')->where('id', $sell['order_id'])->first();
    //$waiter = DB::table($database.'.waiters')->where('id', $order['waiter_id'])->first();
    $app = CurrentApp::App();
    //$sell['envs'] = json_decode($app->environment_vars);
    //$sell['waiter_name'] = $waiter['name'];
    //$size = array(0,0,227,600);
    // if ($boleta || $request->input('boleta_local') == 1) {
    //   $pdf = \PDF::loadView('boleta',compact('sell'))->setPaper($size);
    //   $name = 'boleta_'.uniqid().'.pdf';
    // }else{
    //$pdf = \PDF::loadView('guiaDespacho',compact('sell'))->setPaper($size);
    //$name = 'guia_de_despacho_'.uniqid().'.pdf';
    //}
    //Storage::put('public/pdf/'.$name, $pdf->output());
    /*$path = Storage::url('public/pdf/'.$name);
        if ($boleta && $self) {
          return $path;
        }
        return response()->json($path,200);*/
    //$b64Doc = chunk_split(base64_encode($pdf->output()));
    $b64Doc = chunk_split(base64_encode(SIIController::getPutsContent($folio->pdf_url)));
    // if ($boleta && $self) {
    //   return $b64Doc;
    // }
    return response()->json($b64Doc, 200);
  }


  public function getGuia($id, $print = false)
  {
    $database2 = Config::get('database.connections.mysql_local.database');
    $find = GuiasDespacho::find($id);
    if (!$find) {
      return response()->json('Venta no encontrada', 404);
    }
    $pquery = DB::table($database2 . '.guias_despachos')
      ->where('guias_despachos.trash', 0)->where('guias_despachos.id', $id)
      ->leftJoin($database2 . '.users', 'users.id', 'guias_despachos.user')
      ->select('guias_despachos.*', 'users.username', 'users.avatar')->first();

    if (!$pquery) return response()->json('Error del servidor', 500);

    $products = [];
    $productSells = DB::table($database2 . '.products_guia_despacho')
      ->where('products_guia_despacho.guia', $pquery->id)
      ->leftJoin($database2 . '.products', 'products.id', 'products_guia_despacho.product')
      ->select('products_guia_despacho.price as totalPrice', 'products_guia_despacho.quantity', 'products.name', 'products_guia_despacho.unitary_price')
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
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
      $client = DB::table($database2 . '.clients')->where('id', $pquery->client)->first();
      if ($client) {
        $pquery->client = $client;
      }
    }
    if ($print) {
      return (array)$pquery;
    }
    return response()->json($pquery);
  }
}
