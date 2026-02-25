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
use File;

//Exports
use App\Exports\SellsExport;

use Maatwebsite\Excel\Facades\Excel;

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
use Carbon\Carbon;

//Services
use App\Classes\ServicesSII;

//EVENTS
use App\Events\Cafeteria\OrderProccesed;

class SellsController extends Controller
{
  public function fastSell(Request $request)
  {
    $_request = $request->all();
    $_request['fast_sell'] = 1;

    $validaciones = [
      'total' => 'required',
    ];

    // Validator
    $validator = Validator::make($_request, $validaciones);
    if ($validator->fails()) return response()->json($validator->errors(), 400);

    // Buscando usuario que realizao la venta
    $user = Auth::user()->id;
    if (!$user) return response()->json("Usuario no encontrado", 404);
    $_request['user'] = $user;

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio')) {
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
          if ($key == 'rut' || $key == 'phone') $_request[$key] = '00000000';
          else $_request[$key] = 'Venta rapida';
        }
        // Verificando que el cliente no exista
        $clientNow = Client::where('rut', $_request['rut'])->first();
        if (!$clientNow) {
          $client = Client::createClient($_request);
          if (!$client) return response()->json("Error del servidor", 500);
        } else {
          $client = Client::editClient($_request, $clientNow->id);
          if (!$client) return response()->json("Error del servidor", 500);
        }
        $_request['client'] = $client->id;
      }
    }

    // 🚀 FIX UBER EATS: Si es Uber Eats, Rappi o Pedidos Ya, NO usar paymode = 'boleta'
    if (isset($_request['other_type']) && in_array($_request['other_type'], ['uber_eats', 'uber', 'rappi', 'pedidos_ya'])) {
        $_request['paymode'] = $_request['other_type'];
    }
    
    // 🎯 FIX DESCUENTOS ESPECIALES: Si es Banco Chile 20%, Fagotto 10% o Turbus 10%, setear paymode
    if (isset($_request['other_type']) && in_array($_request['other_type'], ['banco_chile_20', 'fagotto_10', 'turbus_10'])) {
        $_request['paymode'] = $_request['other_type'];
    }

    // Creando venta
    $sell = Sell::createSell($_request);
    if (!$sell) return response()->json("Error del servidor", 500);

    // Creando cada columna en la pivote
    $database2 = Config::get('database.connections.mysql_local.database');
    $product = DB::table($database2 . '.products')->where('key_system', 1)->first();
    if (!$product) return response()->json("Producto de venta rapida no encontrado", 404);
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
    if (!$created) return response()->json("Error del servidor", 500);

    $query = array('id' => $sell->id, 'response_folio' => false);

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta')) {
        $asingFolio = SIIController::processBoleta($request, $sell->id);
        if (isset($asingFolio)) {
          $query['response_folio'] = $asingFolio['content'];
          if (!$asingFolio['success']) return response()->json($query, $asingFolio['code']);
        }
      } else {
        $query['response_folio'] = $_request['type_sell'];;
      }
    }

    return response()->json($query, 200);
  }

  /*
      Metodo para crear una nueva venta (Sell)
      Se llega desde la ruta /api/local/sell
  */
  public function newSell(Request $request, $order_id = false)
  {
    // return response()->json($request, 400);
    $_request = $request->all();
    $_request['fast_sell'] = 0;
    
    // ✅ COLACIÓN: Definir database para tabla colaciones_retiros
    $database2 = Config::get('database.connections.mysql_local.database');

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
        ];
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
          $validaciones['direction'] =  'required|string|max:70';
          $validaciones['giro'] =  'required|string|max:80';
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
      // ✅ FIX MERCHISE, COLACIÓN, CHEAF, MUVIFY Y MENU FAGOTTO: Saltear validación si es producto especial (no están en tabla products)
      $is_merchise = isset($item->is_merchise) && $item->is_merchise === true;
      $is_colacion = isset($item->is_colacion) && $item->is_colacion === true;
      $is_cheaf = isset($item->is_cheaf) && $item->is_cheaf === true;
      $is_muvify = isset($item->is_muvify) && $item->is_muvify === true;
      $is_menu_fagotto = isset($item->is_menu_fagotto) && $item->is_menu_fagotto === true;
      
      \Log::info('🍝 COLACIÓN VALIDACIÓN:', [
        'product_id' => $item->id,
        'is_colacion_isset' => isset($item->is_colacion),
        'is_colacion_value' => $item->is_colacion ?? 'NOT SET',
        'is_colacion_var' => $is_colacion,
        'type_of_is_colacion' => gettype($item->is_colacion ?? null)
      ]);
      
      if (!$is_merchise && !$is_colacion && !$is_cheaf && !$is_muvify && !$is_menu_fagotto) {
        $product = Product::find($item->id);
        if (!$product) {
          \Log::error('❌ PRODUCTO NO ENCONTRADO:', ['id' => $item->id, 'is_colacion' => $is_colacion]);
          if (isset($_request['ticket'])) return "Producto no encontrado";
          else return response()->json("Producto no encontrado", 404);
        }
        // if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')){
        //   if ($product->stock < $item->quantity){
        //     if(isset($_request['ticket'])) return "No hay suficiente stock";
        //     else return response()->json("No hay suficiente stock",400);
        //   }
        // }
        if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')) {
          if ($product->min_quantity > $item->quantity) {
            if (isset($_request['ticket'])) return "El producto " . $product->name . " permite comprar minimo " . $product->min_quantity . " productos";
            else return response()->json("El producto " . $product->name . " permite comprar minimo " . $product->min_quantity . " productos", 400);
          }
        }
      }
    }

    if (!isset($_request['fecha'])) $_request['fecha'] = $request['fecha'];

    // 🚀 FIX UBER EATS, RAPPI, PEDIDOS YA: Si es Uber Eats, Rappi o Pedidos Ya, NO usar paymode = 'boleta'
    // Usar paymode = 'uber_eats'/'rappi'/'pedidos_ya' para que aparezca en reportes correctamente
    if (isset($_request['other_type']) && in_array($_request['other_type'], ['uber_eats', 'uber', 'rappi', 'pedidos_ya'])) {
        $_request['paymode'] = $_request['other_type']; // Usar 'uber_eats', 'uber', 'rappi' o 'pedidos_ya' como paymode
        \Log::info('🚀 FIX PAYMENT - Ajustando paymode para reportes:', [
            'other_type' => $_request['other_type'],
            'paymode_nuevo' => $_request['paymode'],
            'payment_info' => $_request[($_request['other_type'] ?? 'uber') . '_payment_info'] ?? 'no disponible'
        ]);
    }

    // 🎯 FIX FAGOTTO 10%: Si es Fagotto 10%, setear paymode correcto
    if (isset($_request['other_type']) && $_request['other_type'] === 'fagotto_10') {
        $_request['paymode'] = 'fagotto_10';
    }

    // 🚌 FIX TURBUS 10%: Si es Turbus 10%, setear paymode correcto
    if (isset($_request['other_type']) && $_request['other_type'] === 'turbus_10') {
        $_request['paymode'] = 'turbus_10';
    }

    // ✅ FIX MERCHISE: Asegurar que order_id esté en el request para ventas desde ticket
    if ($order_id) {
        $_request['order_id'] = $order_id;
        \Log::info('✅ SellsController - ORDER_ID AGREGADO:', ['order_id' => $order_id]);
    } else {
        \Log::warning('⚠️ SellsController - NO HAY ORDER_ID');
    }

    \Log::info('🔍 SellsController - $_request ANTES DE createSell:', [
      'order_id' => $_request['order_id'] ?? 'NO SET',
      'total' => $_request['total'] ?? 'NO SET',
      'paymode' => $_request['paymode'] ?? 'NO SET'
    ]);

    // Creando venta
    $sell = Sell::createSell($_request);
    
    \Log::info('🔍 SellsController - SELL CREADO:', [
      'sell_id' => $sell ? $sell->id : 'NULL',
      'sell_order_id' => $sell ? $sell->order_id : 'NULL'
    ]);
    if (!$sell) {
      if (isset($_request['ticket'])) return "Error del servidor";
      else return response()->json("Error del servidor", 500);
    }

    // Procesar información especial del pago (Banco De Chile 20%)
    if (isset($_request['special_payment_info'])) {
      $specialPaymentInfo = json_decode($_request['special_payment_info'], true);
      if ($specialPaymentInfo) {
        $sell->special_payment_info = $_request['special_payment_info'];
        $sell->save();
      }
    }

    // Si la venta se crea con exito pasar el estado de la orden a procesada si existe una orden
    if (isset($_request['order'])) {
      $order = Order::find($_request['order']);
      if ($order) {
        $order->tip = $sell->tip;
        $order->discount = $sell->discount;
        $order->state = 'procesada';
        $order->save();
        if (CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')) {
          $order->closeOrdersKitchens();
          broadcast(new OrderProccesed($order, $sell))->toOthers();
        }
      }
    }
    if ($order_id) {
      $order = Order::find($order_id);
      if ($order) {
        $order->state = 'procesada';
        $order->save();
        if (CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')) {
          if (!CurrentApp::ConfStr('modulos.cafeteria.ajustes.order_kitchen_pending')) {
            $order->closeOrdersKitchens();
          }
          broadcast(new OrderProccesed($order, $sell))->toOthers();
        }
      }
    }

    // Creando cada columna en la pivote de cada producto por cada venta
    foreach ($items as $item) {
      // ✅ FIX MERCHISE, COLACIÓN, CHEAF, MUVIFY Y MENU FAGOTTO: Saltear validación de producto si es producto especial
      $is_merchise = isset($item->is_merchise) && $item->is_merchise === true;
      $is_colacion = isset($item->is_colacion) && $item->is_colacion === true;
      $is_cheaf = isset($item->is_cheaf) && $item->is_cheaf === true;
      $is_muvify = isset($item->is_muvify) && $item->is_muvify === true;
      $is_menu_fagotto = isset($item->is_menu_fagotto) && $item->is_menu_fagotto === true;
      
      if (!$is_merchise && !$is_colacion && !$is_cheaf && !$is_muvify && !$is_menu_fagotto) {
        $product = Product::find($item->id);
        if (!$product) {
          if (isset($_request['ticket'])) return "Producto no encontrado";
          else return response()->json("Producto no encontrado", 404);
        }
      }
      
      $price = floatval($item->price);

      if(isset($item->product_promo) && $item->product_promo){
        $promo_price = floatval($item->promo_price);

        $newProductSell = [
          "price" =>  $promo_price,
          "quantity"  =>  $item->quantity,
          "unitary_price" =>  $item->promo_price,
          "product" =>  $item->id,
          "sell"  =>  $sell->id,
        ];
      }
      $newProductSell = [
        "price" =>  $price,
        "quantity"  =>  $item->quantity,
        "unitary_price" =>  $item->price,
        "product" =>  $item->id,
        "sell"  =>  $sell->id,
      ];
      
      // ✅ FIX MERCHISE: Guardar nombre del producto en description_sii si es merchise
      if ($is_merchise && isset($item->name)) {
        $newProductSell['description_sii'] = $item->name;
        $newProductSell['product'] = null; // NULL para merchise (no existe en tabla products)
        \Log::info('✅ MERCHISE - Guardando description_sii:', [
          'product_id' => $item->id,
          'name' => $item->name,
          'is_merchise' => $is_merchise
        ]);
      } else {
        \Log::info('⚠️ MERCHISE - NO guardando description_sii:', [
          'is_merchise' => $is_merchise,
          'has_name' => isset($item->name),
          'name' => $item->name ?? 'NO SET'
        ]);
      }
      
      // ✅ COLACIÓN: Guardar nombre del producto en description_sii si es colación
      $is_colacion = isset($item->is_colacion) && $item->is_colacion === true;
      if ($is_colacion && isset($item->name)) {
        $newProductSell['description_sii'] = $item->name;
        $newProductSell['product'] = null; // NULL para colaciones (no existe en tabla products)
        \Log::info('✅ COLACIÓN - Guardando description_sii:', [
          'product_id' => $item->id,
          'name' => $item->name,
          'empleado' => $item->empleado_retira ?? 'NO SET'
        ]);
      }
      
      // ✅ CHEAF: Guardar nombre del producto en description_sii si es cheaf
      $is_cheaf = isset($item->is_cheaf) && $item->is_cheaf === true;
      if ($is_cheaf && isset($item->name)) {
        $newProductSell['description_sii'] = $item->name;
        $newProductSell['product'] = null; // NULL para cheaf (no existe en tabla products)
        \Log::info('✅ CHEAF - Guardando description_sii:', [
          'product_id' => $item->id,
          'name' => $item->name,
          'promo_type' => $item->promo_type ?? 'NO SET'
        ]);
      }
      
      // ✅ MUVIFY: Guardar nombre del combo en description_sii y detalles del cliente
      $is_muvify = isset($item->is_muvify) && $item->is_muvify === true;
      if ($is_muvify && isset($item->name)) {
        $newProductSell['description_sii'] = $item->name;
        $newProductSell['product'] = null; // NULL para muvify (no existe en tabla products)
        \Log::info('🚌 MUVIFY - Guardando description_sii:', [
          'product_id' => $item->id,
          'name' => $item->name,
          'rut_cliente' => $item->rut_cliente ?? 'NO SET',
          'muvify_details' => isset($item->muvify_details) ? json_encode($item->muvify_details) : 'NO SET'
        ]);
      }
      
      // ✅ MENU FAGOTTO: Guardar nombre del combo en description_sii
      if ($is_menu_fagotto && isset($item->name)) {
        $newProductSell['description_sii'] = $item->name;
        $newProductSell['product'] = null; // NULL para menu fagotto (no existe en tabla products)
        \Log::info('🍝 MENU FAGOTTO - Guardando description_sii:', [
          'product_id' => $item->id,
          'name' => $item->name,
        ]);
      }
      
      \Log::info('🔍 MERCHISE - newProductSell antes de crear:', $newProductSell);
      
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $newProductSell['gananciaTotal'] =  $item->ganancia;
      }
      $created = ProductSell::createProductSell($newProductSell);
      if (!$created) {
        if (isset($_request['ticket'])) return "Error del servidor";
        else return response()->json("Error del servidor", 500);
      }
      
      // ✅ COLACIÓN: Guardar registro de retiro si es colación
      $is_colacion = isset($item->is_colacion) && $item->is_colacion === true;
      if ($is_colacion && isset($item->empleado_retira)) {
        \Log::info('🍝 INTENTANDO GUARDAR COLACIÓN:', [
          'database' => $database2,
          'sell_id' => $sell->id,
          'product_sell_id' => $created->id,
          'empleado' => $item->empleado_retira,
          'pasta' => $item->colacion_details->pasta ?? null,
          'salsa' => $item->colacion_details->salsa ?? null
        ]);
        try {
          DB::table($database2 . '.colaciones_retiros')->insert([
            'sell_id' => $sell->id,
            'product_sell_id' => $created->id,
            'empleado_nombre' => $item->empleado_retira,
            'pasta' => $item->colacion_details->pasta ?? null,
            'salsa' => $item->colacion_details->salsa ?? null,
            'fecha_retiro' => now()
          ]);
          \Log::info('✅✅✅ COLACIÓN GUARDADA EXITOSAMENTE EN DB:', [
            'empleado' => $item->empleado_retira,
            'producto' => $item->name,
            'sell_id' => $sell->id,
            'tabla' => $database2 . '.colaciones_retiros'
          ]);
        } catch (\Exception $e) {
          \Log::error('❌❌❌ ERROR GUARDANDO COLACIÓN:', [
            'error' => $e->getMessage(),
            'empleado' => $item->empleado_retira,
            'line' => $e->getLine(),
            'file' => $e->getFile()
          ]);
        }
      }
      
      // ✅ FIX MERCHISE, COLACIÓN, CHEAF Y MUVIFY: Solo actualizar stock si NO es producto especial
      if (!$is_merchise && !$is_colacion && !$is_cheaf && !$is_muvify && !$is_menu_fagotto && CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')) {
        $product->stock = (float) $product->stock - $item->quantity;
        if (!$product->save()) {
          if (isset($_request['ticket'])) return 'Error en la base de datos';
          else return response()->json('Error en la base de datos', 500);
        }
      }
    }

    $query = array('id' => $sell->id, 'response_folio' => false, 'dataEnviada88' => $_request);

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura') || CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta')) {
        if (isset($_request['type_sell']) && $_request['type_sell'] == 'factura') $asingFolio = SIIController::processFactura($request, $sell->id);
        if (isset($_request['type_sell']) && $_request['type_sell'] == 'boleta') $asingFolio = SIIController::processBoleta($request, $sell->id);
        if (isset($asingFolio)) {
          if (!$asingFolio['success']) {
            $query['response_folio'] = $asingFolio['content'];
            if (isset($_request['ticket'])) return [$query, $asingFolio['code']];
            else return response()->json($query, $asingFolio['code']);
          }
          // $b64Doc = chunk_split(base64_encode(file_get_contents($asingFolio['content'])));
          $query['response_folio'] = $asingFolio['content'];
        }
      } else {
        if (!isset($_request['type_sell']))  $query['response_folio'] = 'boleta_local';
        if (isset($_request['other_type'])) $query['response_folio_other'] = $_request['other_type'];
      }

      // ✅ COLACIÓN: Verificar si todos los productos son colaciones ANTES de imprimir
      $allColaciones = true;
      foreach ($items as $item) {
        if (!isset($item->is_colacion) || $item->is_colacion !== true) {
          $allColaciones = false;
          break;
        }
      }
      
      \Log::info('🍝 COLACIÓN CHECK PDF:', [
        'allColaciones' => $allColaciones,
        'total_items' => count($items),
        'sell_id' => $sell->id
      ]);

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local') && !isset($_request['type_sell'])) {
        // impresion de boleta con formato SII (regulacion) - NO imprimir si son todas colaciones
        if (!$allColaciones) {
          \Log::info('📄 IMPRIMIENDO PDF para venta: ' . $sell->id);
          $query['response_folio'] = $this->printPDF($request, $sell->id, true, true);
        } else {
          \Log::info('🚫 NO SE IMPRIME PDF - Son todas colaciones, venta: ' . $sell->id);
        }
      }

      // Variables para controlar impresión y evitar duplicados
      $requiresSpecialPrint = false;
      $printedMethods = [];

      if (isset($_request['type_sell'])) {
        if ($_request['type_sell'] == "other" || $_request['type_sell'] == "rappi" || $_request['type_sell'] == "junaeb" || $_request['type_sell'] == "uber" || $_request['type_sell'] == "transferencia" || $_request['type_sell'] == "credito") {
          if (!$allColaciones) { // Solo imprimir si NO son todas colaciones
            $query['response_folio'] = $this->printPDF($request, $sell->id, true, true);
          }
          $requiresSpecialPrint = true;
          $printedMethods[] = $_request['type_sell'];
        }
      }

      // Condiciones de impresión específicas para métodos other_type que requieren doble impresión
      if (isset($_request['other_type']) && !$requiresSpecialPrint) {
        $methodsRequiringDoubleprint = ['amipass', 'banco_chile_20', 'fagotto_10', 'turbus_10', 'pluxee', 'pedidos_ya', 'uber_eats'];
        
        if (in_array($_request['other_type'], $methodsRequiringDoubleprint)) {
          // Imprimir boleta local para métodos que la requieren
          if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local') && !$allColaciones) {
            $query['response_folio'] = $this->printPDF($request, $sell->id, true, true);
          }
          // Marcar que se imprimió un método específico
          $printedMethods[] = $_request['other_type'];
        }
      }
    } // CurrentApp::ConfStr('modulos.ventas.submodulos.sii')

    if (isset($_request['ticket'])) return $query;
    else return response()->json($query, 200);
  } ///////////////////////////////// newSells end ////////////////////////////////////////////////

  public function newSell2(Request $request, $order_id = false)
    {
        $_request = $request->all();
        $_request['fast_sell'] = 0;

        // DEBUG: Log completo de la request para ver datos de Uber
        \Log::info("=== NEWSELL2 DEBUG START ===");
        \Log::info("Request completa:", $_request);
        \Log::info("other_type recibido: " . ($_request['other_type'] ?? 'NULL'));
        \Log::info("type_sell recibido: " . ($_request['type_sell'] ?? 'NULL'));
        \Log::info("uber_payment_info recibido: " . ($_request['uber_payment_info'] ?? 'NULL'));
        file_put_contents('/tmp/newsell_debug.log', 
            "=== NEWSELL2 DEBUG START ===\n" .
            "Request completa: " . json_encode($_request) . "\n" .
            "other_type: " . ($_request['other_type'] ?? 'NULL') . "\n" .
            "type_sell: " . ($_request['type_sell'] ?? 'NULL') . "\n" .
            "uber_payment_info: " . ($_request['uber_payment_info'] ?? 'NULL') . "\n",
            FILE_APPEND
        );

        $validaciones = [
            'total' => 'required',
            'products' => 'required|json',
        ];

        // Submodulos
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes') && !isset($_request['other_type'])) {
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio')) {
                $validaciones = array_merge($validaciones, [
                    'name' => 'required|string|max:32|min:2',
                    'lastname' => 'required|string|max:32|min:2',
                    'rut' => 'required|string|max:12',
                    'city' => 'required|string|max:20',
                    'comuna' => 'required|string|max:20',
                    'razon_social' => 'required|string|max:100',
                ]);
                if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
                    $validaciones['direction'] = 'required|string|max:70';
                    $validaciones['giro'] = 'required|string|max:80';
                }
            } else {
                $validaciones = array_merge($validaciones, [
                    'name' => 'string|max:32|min:2',
                    'lastname' => 'string|max:32|min:2',
                    'rut' => 'string|max:12',
                    'city' => 'string|max:20',
                    'comuna' => 'string|max:20',
                    'razon_social' => 'string|max:100',
                ]);
                if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
                    $validaciones['direction'] = 'string|max:70';
                    $validaciones['giro'] = 'string|max:80';
                }
            }
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono')) {
                $validaciones['phone'] = 'string|max:16';
            }
        }

        if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) $validaciones['gananciaTotal'] = 'required';
        else $validaciones['gananciaTotal'] = 'nullable';

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
            // ✅ FIX MENU FAGOTTO: Saltear validación si es producto especial (no está en tabla products)
            $is_menu_fagotto = isset($item->is_menu_fagotto) && $item->is_menu_fagotto === true;
            if (!$is_menu_fagotto) {
                $product = Product::find($item->id);
                if (!$product) {
                    if (isset($_request['ticket'])) return "Producto no encontrado";
                    else return response()->json("Producto no encontrado", 404);
                }
                // if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')){
                //   if ($product->stock < $item->quantity){
                //     if(isset($_request['ticket'])) return "No hay suficiente stock";
                //     else return response()->json("No hay suficiente stock",400);
                //   }
                // }
                if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')) {
                    if ($product->min_quantity > $item->quantity) {
                        if (isset($_request['ticket'])) return "El producto " . $product->name . " permite comprar minimo " . $product->min_quantity . " productos";
                        else return response()->json("El producto " . $product->name . " permite comprar minimo " . $product->min_quantity . " productos", 400);
                    }
                }
            }
        }

        if (!isset($_request['fecha'])) $_request['fecha'] = $request['fecha'];

        // 🚀 FIX UBER EATS: Si es Uber Eats, NO usar paymode = 'boleta'
        if (isset($_request['other_type']) && ($_request['other_type'] === 'uber_eats' || $_request['other_type'] === 'uber')) {
            $_request['paymode'] = $_request['other_type'];
        }

        // 🎯 FIX FAGOTTO 10%: Si es Fagotto 10%, setear paymode correcto
        if (isset($_request['other_type']) && $_request['other_type'] === 'fagotto_10') {
            $_request['paymode'] = 'fagotto_10';
        }

        // Creando venta
        $sell = Sell::createSell($_request);
        if (!$sell) {
            if (isset($_request['ticket'])) return "Error del servidor";
            else return response()->json("Error del servidor", 500);
        }

        // Procesar información especial del pago (Banco De Chile 20%)
        if (isset($_request['special_payment_info'])) {
            $specialPaymentInfo = json_decode($_request['special_payment_info'], true);
            if ($specialPaymentInfo) {
                $sell->special_payment_info = $_request['special_payment_info'];
                $sell->save();
            }
        }

        // Si la venta se crea con exito pasar el estado de la orden a procesada si existe una orden
        if (isset($_request['order'])) {
            $order = Order::find($_request['order']);
            if ($order) {
                $order->tip = $sell->tip;
                $order->discount = $sell->discount;
                $order->state = 'procesada';
                $order->save();
                if (CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')) {
                    $order->closeOrdersKitchens();
                    broadcast(new OrderProccesed($order, $sell))->toOthers();
                }
            }
        }
        if ($order_id) {
            $order = Order::find($order_id);
            if ($order) {
                $order->state = 'procesada';
                $order->save();
                if (CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')) {
                    if (!CurrentApp::ConfStr('modulos.cafeteria.ajustes.order_kitchen_pending')) {
                        $order->closeOrdersKitchens();
                    }
                    broadcast(new OrderProccesed($order, $sell))->toOthers();
                }
            }
        }

        // Creando cada columna en la pivote de cada producto por cada venta
        foreach ($items as $item) {
            // ✅ FIX MENU FAGOTTO: Saltear validación de producto si es producto especial
            $is_menu_fagotto = isset($item->is_menu_fagotto) && $item->is_menu_fagotto === true;
            if (!$is_menu_fagotto) {
                $product = Product::find($item->id);
                if (!$product) {
                    if (isset($_request['ticket'])) return "Producto no encontrado";
                    else return response()->json("Producto no encontrado", 404);
                }
            }

            $price = floatval($item->price);

            if (isset($item->product_promo) && $item->product_promo) {
                $promo_price = floatval($item->promo_price);

                $newProductSell = [
                    "price" => $promo_price,
                    "quantity" => $item->quantity,
                    "unitary_price" => $item->promo_price,
                    "product" => $item->id,
                    "sell" => $sell->id,
                ];
            } else { // Agregado else para asegurar que $newProductSell se define siempre
                $newProductSell = [
                    "price" => $price,
                    "quantity" => $item->quantity,
                    "unitary_price" => $item->price,
                    "product" => $item->id,
                    "sell" => $sell->id,
                ];
            }

            // ✅ MENU FAGOTTO: Guardar nombre del combo en description_sii
            if ($is_menu_fagotto && isset($item->name)) {
                $newProductSell['description_sii'] = $item->name;
                $newProductSell['product'] = null; // NULL para menu fagotto (no existe en tabla products)
                \Log::info('🍝 MENU FAGOTTO - Guardando description_sii:', [
                    'product_id' => $item->id,
                    'name' => $item->name,
                ]);
            }

            if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
                $newProductSell['gananciaTotal'] = $item->ganancia;
            }
            $created = ProductSell::createProductSell($newProductSell);
            if (!$created) {
                if (isset($_request['ticket'])) return "Error del servidor";
                else return response()->json("Error del servidor", 500);
            }

            // Descontar stock de ingredientes (solo para productos reales)
            if (!$is_menu_fagotto && CurrentApp::ConfStr('modulos.ingredients')) {

              // Cargar los ingredientes del producto.
              $product->load('ingredients');

              // Recolectar todos los IDs de los ingredientes de este producto para una consulta global eficiente.
              $ingredientIds = $product->ingredients->pluck('id')->unique()->all();

              $database = Config::get('database.connections.mysql.database');

              // Obtener los gramajes globales de estos ingredientes de una sola vez
              // de la tabla `global_ingredients` en la base de datos principal.
              // Usamos pluck('quantity_grams', 'ingredient_id') para obtener un array asociativo
              // donde la clave es 'ingredient_id' y el valor es 'quantity_grams'.
              $globalGrammages = DB::table($database . '.global_ingredients')
                  ->whereIn('ingredient_id', $ingredientIds)
                  ->pluck('quantity_grams', 'ingredient_id');

              // Verificar si algún ingrediente del producto no tiene un gramaje global definido
              foreach ($product->ingredients as $ingredient) {
                  // Ahora usamos $globalGrammages para verificar la existencia del gramaje.
                  if (!isset($globalGrammages[$ingredient->id])) {
                      $errorMessage = 'Error: El gramaje global para el ingrediente "' . $ingredient->name . '" (ID: ' . $ingredient->id . ') no está definido.';
                      if (isset($_request['ticket'])) {
                          return $errorMessage;
                      } else {
                          return response()->json($errorMessage, 500);
                      }
                  }
              }

              foreach ($product->ingredients as $ingredient) {
                  // Obtener la cantidad necesaria de gramos de este ingrediente del array de gramajes globales
                  // Accedemos directamente por el ID del ingrediente.
                  $quantityNeededPerProductUnit = (float) $globalGrammages[$ingredient->id]; // <-- ¡Corrección aquí!

                  // Calcular la cantidad total de gramos de este ingrediente usada en la venta
                  $totalGramsUsed = $quantityNeededPerProductUnit * $item->quantity;

                  // Convertir el stock actual del ingrediente a gramos para la resta
                  $currentStockGrams = (float) $ingredient->stock_quantity * 1000;

                  // Restar los gramos usados del stock actual
                  $newStockGrams = $currentStockGrams - $totalGramsUsed;

                  // Verificar si hay suficiente stock antes de deducir
                  if ($newStockGrams < 0) {
                      $errorMessage = 'Stock insuficiente para el ingrediente: ' . $ingredient->name;
                      if (isset($_request['ticket'])) {
                          return $errorMessage;
                      } else {
                          return response()->json($errorMessage, 400); // 400 Bad Request por stock insuficiente
                      }
                  }

                  // Convertir el nuevo stock de nuevo a kilogramos para guardarlo en la base de datos local
                  $ingredient->stock_quantity = $newStockGrams / 1000;

                  // Guardar el stock actualizado en la base de datos local
                  if (!$ingredient->save()) {
                      $errorMessage = 'Error al actualizar el stock del ingrediente: ' . $ingredient->name;
                      if (isset($_request['ticket'])) {
                          return $errorMessage;
                      } else {
                          return response()->json($errorMessage, 500);
                      }
                  }
              }
            }

            // El bloque de código original para el stock del producto (si aplica)
            if (!$is_menu_fagotto && CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')) {
                $product->stock = (float) $product->stock - $item->quantity;
                if (!$product->save()) {
                    if (isset($_request['ticket'])) return 'Error en la base de datos';
                    else return response()->json('Error en la base de datos', 500);
                }
            }
        }

        $query = array('id' => $sell->id, 'response_folio' => false, 'dataEnviada00' => $_request);

        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura') || CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta')) {
                if (isset($_request['type_sell']) && $_request['type_sell'] == 'factura') $asingFolio = SIIController::processFactura($request, $sell->id);
                if (isset($_request['type_sell']) && $_request['type_sell'] == 'boleta') $asingFolio = SIIController::processBoleta($request, $sell->id);
                if (isset($asingFolio)) {
                    if (!$asingFolio['success']) {
                        $query['response_folio'] = $asingFolio['content'];
                        if (isset($_request['ticket'])) return [$query, $asingFolio['code']];
                        else return response()->json($query, $asingFolio['code']);
                    }
                    $query['response_folio'] = $asingFolio['content'];
                }
            } else {
                if (!isset($_request['type_sell']))  $query['response_folio'] = 'boleta_local';
                if (isset($_request['other_type'])) $query['response_folio_other'] = $_request['other_type'];
            }

            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local') && !isset($_request['type_sell'])) {
                $query['response_folio'] = $this->printPDF($request, $sell->id, true, true);
            }

            // Variables para controlar impresión y evitar duplicados
            $requiresSpecialPrint = false;
            $printedMethods = [];

            if (isset($_request['type_sell'])) {
                if ($_request['type_sell'] == "other" || $_request['type_sell'] == "rappi" || $_request['type_sell'] == "junaeb" || $_request['type_sell'] == "uber" || $_request['type_sell'] == "transferencia" || $_request['type_sell'] == "credito") {
                    $query['response_folio'] = $this->printPDF($request, $sell->id, true, true);
                    $requiresSpecialPrint = true;
                    $printedMethods[] = $_request['type_sell'];
                }
            }

            // Condiciones de impresión específicas para métodos other_type que requieren doble impresión
            if (isset($_request['other_type']) && !$requiresSpecialPrint) {
                $methodsRequiringDoubleprint = ['amipass', 'banco_chile_20', 'fagotto_10', 'turbus_10', 'pluxee', 'pedidos_ya', 'uber_eats'];
                
                if (in_array($_request['other_type'], $methodsRequiringDoubleprint)) {
                    // Imprimir boleta local para métodos que la requieren
                    if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local')) {
                        $query['response_folio'] = $this->printPDF($request, $sell->id, true, true);
                    }
                    // Marcar que se imprimió un método específico
                    $printedMethods[] = $_request['other_type'];
                }
            }
        }

        if (isset($_request['ticket'])) return $query;
        else return response()->json($query, 200);
    }
  public function getSells(Request $request)
  {
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2 . '.sells')
      ->whereIn('sells.trash', [0, 1])
      ->leftJoin($database2 . '.users', 'users.id', 'sells.user')
      ->select('sells.*', 'users.fullname', 'users.avatar');
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
      $pquery->leftJoin($database2 . '.clients', 'clients.id', 'sells.client');
    }
    if (!$pquery) return response()->json('Error del servidor', 500);

    //Ordenamientos
    $orders = ['id', 'total', 'created_at', 'user'];
    // //Filtrados
    $filters = [];
    if ($request->input('todaySells')) {
      $today = new DateTime(now());
      $today->setTime(00, 00, 00);
      $pquery->where('sells.created_at', '>=', $today);
    }
    if ($request->input('startDate')) {
      $pquery->where('sells.created_at', '>=', $request->input('startDate'));
    }
    if ($request->input('endDate')) {
      $pquery->where('sells.created_at', '<=', $request->input('endDate'));
    }
    if ($request->has('orderBy_date')) {
      $pquery->orderBy('sells.created_at', $request->input('orderBy_date'));
    }
    if ($request->input('searchInSell')) {
      $name = $request->input('searchInSell');
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $sellId = DB::table($database2 . '.sells')->where('id', $name)->where('sells.trash', 0)->first();
        if (!$sellId) {
          $pquery->whereRaw("(clients.name like '%$name%' OR clients.lastname like '%$name%' OR clients.rut like '%$name%')");
        } else {
          $pquery->where('sells.id', $name);
        }
      } else {
        $pquery->where('sells.id', $name);
      }
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request, 10, '', 'sells', $orders, $filters, false, null);
    foreach ($Paginated['items'] as $sell) {
      $user = DB::table($database2 . '.users')->where('id', $sell->user_trash)->first();
      $sell->user_trash = $sell->user_trash;
      if (!empty($user)) {
        $sell->user_trash = $user->fullname;
      }
      //Añadiendo productos individuales
      $products = [];
      $productSells = DB::table($database2 . '.products_sells')
        ->where('products_sells.sell', $sell->id)
        ->leftJoin($database2 . '.products', 'products.id', 'products_sells.product')
        ->select('products_sells.id', 'products_sells.price as totalPrice', 'products_sells.quantity', 'products_sells.unitary_price', 'products.name', 'products_sells.description_sii')
        ->get();
      foreach ($productSells as $productSell) {
        // ✅ FIX MERCHISE: Usar description_sii si el nombre del producto no existe
        if (empty($productSell->name) && !empty($productSell->description_sii)) {
          $productSell->name = $productSell->description_sii;
        }
        $products[] = $productSell;
      }
      $sell->products = $products;

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $client = DB::table($database2 . '.clients')->where('id', $sell->client)->first();
        if ($client) {
          $sell->client = $client;
        }
      }

      // LOG: Verificando other_type antes de procesar
      \Log::info("=== PAYMENT METHOD DEBUG getSells ===");
      \Log::info("Sell ID: " . $sell->id);
      \Log::info("Other_type: " . ($sell->other_type ?? 'NULL'));
      \Log::info("SII Module enabled: " . (CurrentApp::ConfStr('modulos.ventas.submodulos.sii') ? 'YES' : 'NO'));

      // También escribir a un archivo específico para debug
      file_put_contents('/tmp/payment_debug.log', 
        "=== PAYMENT METHOD DEBUG getSells ===\n" .
        "Sell ID: " . $sell->id . "\n" .
        "Other_type: " . ($sell->other_type ?? 'NULL') . "\n" .
        "SII Module enabled: " . (CurrentApp::ConfStr('modulos.ventas.submodulos.sii') ? 'YES' : 'NO') . "\n",
        FILE_APPEND
      );

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
        //Definiendo si ya fue procesado como factura o boleta o guia de despacho
        $folio = Folio::where('sell_id', $sell->id)->first();

        // ✅ PRIORIDAD: Si tiene other_type (Uber, Fagotto 10%, Banco Chile 20%, etc.)
        if ($sell->other_type != null) {
          $formattedType = $this->formatPaymentMethod($sell->other_type);
          \Log::info("Formatted payment method: " . $formattedType);
          file_put_contents('/tmp/payment_debug.log', "Formatted payment method: " . $formattedType . "\n", FILE_APPEND);
          $sell->type = $formattedType;
        } else if ($folio) {
          // Solo usar folio si NO tiene other_type
          $sell->type = $folio->type;
          $sell->glosa_sii = $folio->glosa_sii;
          \Log::info("Using folio type: " . $folio->type);
          file_put_contents('/tmp/payment_debug.log', "Using folio type: " . $folio->type . "\n", FILE_APPEND);
        } else {
          $sell->type = null;
          $sell->glosa_sii = null;
          if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local')) {
            $sell->type = 'Boleta Local';
            \Log::info("Using boleta local");
            file_put_contents('/tmp/payment_debug.log', "Using boleta local\n", FILE_APPEND);
          }
        }
      } else {
        // Asignar método de pago cuando el módulo SII no está habilitado
        if ($sell->other_type != null) {
          $formattedType = $this->formatPaymentMethod($sell->other_type);
          \Log::info("SII disabled - formatted payment method: " . $formattedType);
          file_put_contents('/tmp/payment_debug.log', "SII disabled - formatted payment method: " . $formattedType . "\n", FILE_APPEND);
          $sell->type = $formattedType;
        }
      }
      
      \Log::info("Final sell->type: " . ($sell->type ?? 'NULL'));
      file_put_contents('/tmp/payment_debug.log', "Final sell->type: " . ($sell->type ?? 'NULL') . "\n=== END ===\n\n", FILE_APPEND);
      \Log::info("=== END PAYMENT METHOD DEBUG getSells ===");
    }
    return response()->json($Paginated);
  }

  public function getSellsExtended(Request $request)
  {
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2 . '.sells')
      ->whereIn('sells.trash', [0, 1])
      ->leftJoin($database2 . '.users', 'users.id', 'sells.user')
      ->select('sells.*', 'users.fullname', 'users.avatar');
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
      $pquery->leftJoin($database2 . '.clients', 'clients.id', 'sells.client');
    }
    if (!$pquery) return response()->json('Error del servidor', 500);

    //Ordenamientos
    $orders = ['id', 'total', 'created_at', 'user'];
    // //Filtrados
    $filters = [];
    if ($request->input('todaySells')) {
      $today = new DateTime(now());
      $today->setTime(00, 00, 00);
      $pquery->where('sells.created_at', '>=', $today);
    }
    if ($request->input('startDate')) {
      $pquery->where('sells.created_at', '>=', $request->input('startDate'));
    }
    if ($request->input('endDate')) {
      $pquery->where('sells.created_at', '<=', $request->input('endDate'));
    }
    if ($request->has('orderBy_date')) {
      $pquery->orderBy('sells.created_at', $request->input('orderBy_date'));
    }
    if ($request->input('searchInSell')) {
      $name = $request->input('searchInSell');
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $sellId = DB::table($database2 . '.sells')->where('id', $name)->where('sells.trash', 0)->first();
        if (!$sellId) {
          $pquery->whereRaw("(clients.name like '%$name%' OR clients.lastname like '%$name%' OR clients.rut like '%$name%')");
        } else {
          $pquery->where('sells.id', $name);
        }
      } else {
        $pquery->where('sells.id', $name);
      }
    }

    if ($request->input('paymode')) {
      $pquery->where('sells.paymode', '=', $request->input('paymode'));
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request, 20, '', 'sells', $orders, $filters, false, null);
    foreach ($Paginated['items'] as $sell) {
      $user = DB::table($database2 . '.users')->where('id', $sell->user_trash)->first();
      $sell->user_trash = $sell->user_trash;
      if (!empty($user)) {
        $sell->user_trash = $user->fullname;
      }
      //Añadiendo productos individuales
      $products = [];
      $productSells = DB::table($database2 . '.products_sells')
        ->where('products_sells.sell', $sell->id)
        ->leftJoin($database2 . '.products', 'products.id', 'products_sells.product')
        ->select('products_sells.id', 'products_sells.price as totalPrice', 'products_sells.quantity', 'products_sells.unitary_price', 'products.name', 'products_sells.description_sii')
        ->get();
      
      \Log::info('🔍 MERCHISE DEBUG - Sell ID: ' . $sell->id);
      \Log::info('🔍 MERCHISE DEBUG - ProductSells encontrados: ' . $productSells->count());
      
      foreach ($productSells as $productSell) {
        \Log::info('🔍 MERCHISE DEBUG - Producto:', [
          'id' => $productSell->id,
          'name' => $productSell->name,
          'description_sii' => $productSell->description_sii
        ]);
        
        // ✅ FIX MERCHISE: Usar description_sii si el nombre del producto no existe
        if (empty($productSell->name) && !empty($productSell->description_sii)) {
          $productSell->name = $productSell->description_sii;
          \Log::info('✅ MERCHISE DEBUG - Nombre corregido a: ' . $productSell->name);
        }
        $products[] = $productSell;
      }
      
      \Log::info('🔍 MERCHISE DEBUG - Total productos agregados: ' . count($products));
      $sell->products = $products;

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $client = DB::table($database2 . '.clients')->where('id', $sell->client)->first();
        if ($client) {
          $sell->client = $client;
        }
      }

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
        //Definiendo si ya fue procesado como factura o boleta o guia de despacho
        $folio = Folio::where('sell_id', $sell->id)->first();

        if ($sell->other_type != null) {
          $sell->type = $this->formatPaymentMethod($sell->other_type);
        } else if ($folio) {
          $sell->type = $folio->type;
          $sell->glosa_sii = $folio->glosa_sii;
        } else {
          $sell->type = null;
          $sell->glosa_sii = null;
          if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local')) {
            $sell->type = 'Boleta Local';
          }
        }
      } else {
        // Asignar método de pago cuando el módulo SII no está habilitado
        if ($sell->other_type != null) {
          $sell->type = $this->formatPaymentMethod($sell->other_type);
        }
      }
    }
    return response()->json($Paginated);
  }

  public function getDeletedSells(Request $request)
  {
    $database2 = Config::get('database.connections.mysql_local.database');
    $currentDate = Carbon::now()->format('Y-m-d');
    $pquery = DB::table($database2 . '.sells')
      ->where('sells.trash', 1)
      ->whereDate('sells.updated_at', $currentDate)
      ->leftJoin($database2 . '.users', 'users.id', 'sells.user')
      ->select('sells.*', 'users.fullname', 'users.avatar');
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
      $pquery->leftJoin($database2 . '.clients', 'clients.id', 'sells.client');
    }
    if (!$pquery) return response()->json('Error del servidor', 500);

    //Ordenamientos
    $orders = ['id', 'total', 'created_at', 'user'];
    // //Filtrados
    $filters = [];
    if ($request->input('todaySells')) {
      $today = new DateTime(now());
      $today->setTime(00, 00, 00);
      $pquery->where('sells.created_at', '>=', $today);
    }
    if ($request->input('startDate')) {
      $pquery->where('sells.created_at', '>=', $request->input('startDate'));
    }
    if ($request->input('endDate')) {
      $pquery->where('sells.created_at', '<=', $request->input('endDate'));
    }
    if ($request->has('orderBy_date')) {
      $pquery->orderBy('sells.created_at', $request->input('orderBy_date'));
    }
    if ($request->input('searchInSell')) {
      $name = $request->input('searchInSell');
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $sellId = DB::table($database2 . '.sells')->where('id', $name)->where('sells.trash', 0)->first();
        if (!$sellId) {
          $pquery->whereRaw("(clients.name like '%$name%' OR clients.lastname like '%$name%' OR clients.rut like '%$name%')");
        } else {
          $pquery->where('sells.id', $name);
        }
      } else {
        $pquery->where('sells.id', $name);
      }
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request, 10, '', 'sells', $orders, $filters, false, null);
    foreach ($Paginated['items'] as $sell) {
      //Añadiendo productos individuales
      $products = [];
      $productSells = DB::table($database2 . '.products_sells')
        ->where('products_sells.sell', $sell->id)
        ->leftJoin($database2 . '.products', 'products.id', 'products_sells.product')
        ->select('products_sells.price as totalPrice', 'products_sells.quantity', 'products_sells.unitary_price', 'products.name', 'products_sells.description_sii')
        ->get();
      foreach ($productSells as $productSell) {
        // ✅ FIX MERCHISE: Usar description_sii si el nombre del producto no existe
        if (empty($productSell->name) && !empty($productSell->description_sii)) {
          $productSell->name = $productSell->description_sii;
        }
        $products[] = $productSell;
      }
      $sell->products = $products;

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $client = DB::table($database2 . '.clients')->where('id', $sell->client)->first();
        if ($client) {
          $sell->client = $client;
        }
      }

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
        //Definiendo si ya fue procesado como factura o boleta
        $folio = Folio::where('sell_id', $sell->id)->first();

        if ($sell->other_type != null) {
          $sell->type = $this->formatPaymentMethod($sell->other_type);
        } else if ($folio) {
          $sell->type = $folio->type;
          $sell->glosa_sii = $folio->glosa_sii;
        } else {
          $sell->type = null;
          $sell->glosa_sii = null;
          if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local')) {
            $sell->type = 'Boleta Local';
          }
        }
      } else {
        // Asignar método de pago cuando el módulo SII no está habilitado
        if ($sell->other_type != null) {
          $sell->type = $this->formatPaymentMethod($sell->other_type);
        }
      }
    }
    return response()->json($Paginated);
  }

  public function getReportSells(Request $request)
  {
    // Nombre del archivo
    $filename = 'ventas_'.time().'.xlsx';

    // Guardando archivo en la storage
    $excel = Excel::store(new SellsExport($request), 'excel/'.$filename, 'public');

    // Ruta del archivo
    $path = storage_path('app/public/excel/'.$filename);

    // Enviando archivo en base64
    return $this->handlerGetFile($path);
  }

    // Transformando archivos en base64
    public function handlerGetFile($path) {
      // Verificando existencia del archivo
      if (!File::exists($path)) return response()->json('Error al crear excel',500);
  
      // Obteniendo archivo
      $file = File::get($path);
  
      // Transformandolo a base64
      $b64Doc = chunk_split(base64_encode($file));
  
      // Respuesta
      return response()->json($b64Doc, 200);
    }

  public function getSell($id, $print = false)
  {
    $database2 = Config::get('database.connections.mysql_local.database');
    $find = Sell::find($id);
    if (!$find) {
      return response()->json('Venta no encontrada 1', 404);
    }
    $pquery = DB::table($database2 . '.sells')
      ->where('sells.trash', 0)->where('sells.id', $id)
      ->leftJoin($database2 . '.users', 'users.id', 'sells.user')
      ->select('sells.*', 'users.username', 'users.avatar')->first();

    if (!$pquery) return response()->json('Error del servidor', 500);

    $products = [];
    $productSells = DB::table($database2 . '.products_sells')
      ->where('products_sells.sell', $pquery->id)
      ->leftJoin($database2 . '.products', 'products.id', 'products_sells.product')
      ->select('products_sells.id', 'products_sells.price as totalPrice', 'products_sells.quantity', 'products.name', 'products_sells.unitary_price', 'products_sells.description_sii')
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

      // ✅ FIX MERCHISE: Usar description_sii si el nombre del producto no existe
      if (empty($productSell->name) && !empty($productSell->description_sii)) {
        $productSell->name = $productSell->description_sii;
      }
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

  public function removeSell(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'trash_comment' => 'string|min:1|max:255',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }

    $sell = Sell::find($id);
    if (!$sell) {
      return response()->json('Venta no encontrada', 404);
    }
    // Obtener los productos vendidos en la venta
    $productsSells = ProductSell::where('sell', $sell->id)->get();

    // dd($productsSells);
    // Actualizar el stock de cada producto vendido
    foreach ($productsSells as $productSell) {
      $product = Product::find($productSell->product);
      $product->stock += $productSell->quantity;
      $product->save();
    }

    $sell->trash = 1;
    $sell->trash_comment = $request->input('trash_comment');
    if (!$sell->save()) {
      return response()->json('Error en la base de datos', 500);
    }

    return response()->json('Venta removida exitosamente', 200);
  }

  public function getClient($rut)
  {

    $database2 = Config::get('database.connections.mysql_local.database');
    $client = DB::table($database2 . '.clients')->where('rut', $rut)->first();

    if (!$client) {
      $response = ServicesSII::getPerson($rut);
      if ($response->ok)
        $client = $response->content;
    }


    if (!$client)
      return response()->json('Usuario no encontrado99', 404);

    //cortando caracteres menor a 40 @jesus

    if (strlen($client->giro) > 39) {

      $espacios = substr_count($client->giro, ' ');
      //Contando espacios y restandoselos.
      $GiroSinEspacios = substr($client->giro, 0, -$espacios);

      if (strlen($GiroSinEspacios) > 39) {
        $client->giro = substr($client->giro, 0, 39);
      } else {
        $client->giro = $GiroSinEspacios;
      }
    }

    return response()->json($client);
  }

  public function editClient(Request $request, $sell)
  {
    $sell = Sell::find($sell);
    if (!$sell) {
      return response()->json('Venta no encontrada', 404);
    }
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii') && $sell->siiState != 'en_espera') {
      return response()->json('Esta venta se encuentra en proceso o ya fue procesada', 400);
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
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono')) {
      $validaciones['phone'] =  'string|max:16';
    }
    $validator = Validator::make($_request, $validaciones);
    if ($validator->fails()) return response()->json($validator->errors(), 400);

    $clientNow = Client::where('rut', $_request['rut'])->first();
    if (!$clientNow) {
      $client = Client::createClient($_request);
      if (!$client) return response()->json("Error del servidor", 500);
    } else {
      $client = Client::editClient($_request, $clientNow->id);
      if (!$client) return response()->json("Error del servidor", 500);
    }
    $sell->client = $client->id;
    $sell->save();
    return response()->json('Cliente editado exitosamente', 200);
  }

  public function printPDF(Request $request, $id, $boleta = false, $self = false)
  {
    $database = Config::get('database.connections.mysql_local.database');

    $sell = $this->getSell($id, true);
    
    // Procesar información especial del pago si existe
    if (isset($sell['special_payment_info'])) {
      $specialPaymentInfo = json_decode($sell['special_payment_info'], true);
      if ($specialPaymentInfo) {
        $sell['specialPayment'] = $specialPaymentInfo;
      }
    }
    
    //$order = DB::table($database.'.orders')->where('id', $sell['order_id'])->first();
    //$waiter = DB::table($database.'.waiters')->where('id', $order['waiter_id'])->first();
    $app = CurrentApp::App();
    $sell['envs'] = json_decode($app->environment_vars);
    //$sell['waiter_name'] = $waiter['name'];
    $size = array(0, 0, 227, 600);
    if ($boleta || $request->input('boleta_local') == 1) {
      $pdf = \PDF::loadView('boleta', compact('sell'))->setPaper($size);
      $name = 'boleta_' . uniqid() . '.pdf';
    } else {
      $pdf = \PDF::loadView('factura', compact('sell'))->setPaper($size);
      $name = 'recibo_' . uniqid() . '.pdf';
    }
    Storage::put('public/pdf/' . $name, $pdf->output());
    /*$path = Storage::url('public/pdf/'.$name);
    if ($boleta && $self) {
      return $path;
    }
    return response()->json($path,200);*/
    $b64Doc = chunk_split(base64_encode($pdf->output()));
    if ($boleta && $self) {
      return $b64Doc;
    }
    return response()->json($b64Doc, 200);
  }

  /**
   * Formatear el nombre del método de pago para mostrar en la interfaz
   */
  private function formatPaymentMethod($paymentMethod)
  {
    \Log::info("=== formatPaymentMethod called ===");
    \Log::info("Input: " . $paymentMethod);
    
    file_put_contents('/tmp/payment_debug.log', "formatPaymentMethod called with: " . $paymentMethod . "\n", FILE_APPEND);
    
    $paymentMethods = [
      'banco' => 'Transbank',
      'debito' => 'Débito',
      'credito' => 'Crédito',
      'transferencia' => 'Transferencia',
      'efectivo' => 'Efectivo',
      'amipass' => 'Amipass',
      'multicaja' => 'Multicaja',
      'edenred' => 'Edenred',
      'convenio_empresa' => 'Convenio Empresa',
      'sodexo' => 'Sodexo',
      'rappi' => 'Rappi',
      'junaeb' => 'Junaeb',
      'uber' => 'Uber',
      'uber_eats' => 'Uber Eats - Boleta SII',
      'pedidos_ya' => 'Pedidos Ya',
      'pluxee' => 'Pluxee',
      'banco_chile_20' => 'Banco De Chile 20%',
      'fagotto_10' => 'Exclusivo Fagotto 10%',
      'turbus_10' => 'Turbus 10%',
      'fluxi' => 'Fluxi',
      'cheaf' => 'Cheaf',
      'cheque' => 'Cheque',
      'boleta_local' => 'Boleta Local',
      'factura' => 'Factura',
      'boleta' => 'Boleta',
      'nota_de_credito' => 'Nota de Crédito',
      'guia_despacho' => 'Guía de Despacho'
    ];

    $result = $paymentMethods[$paymentMethod] ?? ucfirst(str_replace('_', ' ', $paymentMethod));
    \Log::info("Output: " . $result);
    file_put_contents('/tmp/payment_debug.log', "formatPaymentMethod output: " . $result . "\n", FILE_APPEND);
    \Log::info("=== formatPaymentMethod end ===");
    
    return $result;
  }
}
