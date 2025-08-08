<?php

namespace App\Http\Controllers\Controllers_local;

// Models
use App\models_local\ProductSell;
use App\models_local\Product;
use App\models_local\Category;
use App\models_local\Sell;
use App\models_local\Order;
use App\models_local\Addition_Waiter;
use App\models_local\Workshift;
use App\models_local\Folio;
use App\models_local\Waiter;
use App\models_local\Expense;
use App\User;
use App\Aplication;
use App\Helpers\ConectionDB;
use App\Helpers\PaymentMethodHelper;
// Helpers
use App\Helpers\CurrentApp;
use Config;

// Laravel
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

// Excel
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use App\Exports\CustomReportExport;


define('_cantidad_total', 2);
define('_monto_total', 3);
define('_ganancia_total', 4);

define('_procentaje_quantity', 5);
define('_procentaje_balance', 6);
define('_procentaje_ganancia', 7);
define('_categoria', 8);

class ReportsController extends Controller
{
  public function getSells(Request $request)
  {
    $types = [];
    $_request = $request->all();
    $database2 = Config::get('database.connections.mysql_local.database');

    $pquery = DB::table($database2 . '.sells')
      ->where('sells.trash', 0)
      ->where('sells.created_at', '>=', $_request['startDate'])
      ->where('sells.created_at', '<=', $_request['endDate'])
      ->leftJoin($database2 . '.users', 'users.id', 'sells.user')
      ->select('sells.*', 'users.fullname', 'users.avatar');
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) $pquery = $pquery->leftJoin($database2 . '.clients', 'sells.client', 'clients.id')->get();
    else $pquery = $pquery->get();

    if (!$pquery) return response()->json('Error del servidor', 500);

    $items = [];
    // Filtrando
    foreach ($pquery as $sell) {
      if ($sell->fast_sell) {
        if (isset($_request['fastSell'])) {
          $items[] = $sell;
        }
      } else {
        $folio = DB::table($database2 . '.folios')->where('sell_id', $sell->id)->first();
        if (!$folio) {
          if (isset($_request['noSii'])) {
            $items[] = $sell;
          } else if ($sell->other_type != null) {
            $items[] = $sell;
          } else if (isset($_request['amipass'])) {
            $items[] = $sell;
          }
        } else {
          if (isset($_request['factura']) && $folio->type == 'factura') {
            $items[] = $sell;
          } else if (isset($_request['boleta']) && $folio->type == 'boleta') {
            $items[] = $sell;
          } else if ($sell->other_type != null) {
            $items[] = $sell;
          }
        }
      }
    }
    $pquery = $items;
    foreach ($pquery as $sell) {
      $products = [];
      $productSells = DB::table($database2 . '.products_sells')->where('products_sells.sell', $sell->id)
        ->leftJoin($database2 . '.products', 'products.id', 'products_sells.product')
        ->select('products_sells.price as totalPrice', 'products_sells.quantity', 'products_sells.unitary_price', 'products.name')
        ->get();
      foreach ($productSells as $productSell) {
        $products[] = $productSell;
      }
      $sell->products = $products;

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $client = DB::table($database2 . '.clients')->where('id', $sell->client)->first();
        if ($client) {
          $sell->client = $client;
        }
      }
    }
    return response()->json($pquery);
  }

  public function getTopSells(Request $request)
  {
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    $topProducts = ProductSell::select('products.name   as product_name', 'products_sells.product', DB::raw('SUM(quantity) as total_quantity'))
      ->join('products', 'products_sells.product', '=', 'products.id')
      ->whereBetween(DB::raw('DATE(products_sells.created_at)'), [$startDate, $endDate])
      ->groupBy('products_sells.product', 'products.name')
      ->orderByDesc('total_quantity')
      ->limit(10)
      ->get();

    $totalSales = $topProducts->sum('total_quantity');
    $topProducts->put('totalSales', $totalSales);

    return response()->json($topProducts);
  }

  public function getSellsByHour(Request $request)
  {
    $database2 = Config::get('database.connections.mysql_local.database');

    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    // $currentDate = Carbon::now();
    $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->setHour(6)->setMinute(0)->setSecond(0);
    $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $endDate)->setHour(23)->setMinute(59)->setSecond(59);

    $salesByHour = [];

    // Iterar por cada hora desde la hora de inicio hasta la hora de fin
    $currentHour = $startDate->copy();
    $endHourObj = $endDate->copy();

    while ($currentHour <= $endHourObj) {
      $nextHour = $currentHour->copy()->addHour();

      $salesCount = DB::table($database2 . '.sells')->where('sells.trash', 0)
      ->whereBetween('created_at', [$currentHour, $nextHour])
      ->count();

      $salesByHour[$currentHour->format('H:i:s')] = $salesCount;

      $currentHour = $nextHour;
    }

    return response()->json($salesByHour);
  }

  public function getCounters(Request $request, $self = false)
  {
    $_request = $request->all();
    // $app = CurrentApp::App();
    // $database2 = Config::get('database.connections.mysql_local.database');

    //Obtenemos las  ventas
    $ventasQuery = Sell::whereBetween('created_at', [$_request['startDate'], $_request['endDate']]);
    
    // Si hay parámetros de filtrado de métodos de pago específicos, aplicar filtros
    $hasPaymentFilters = false;
    $paymentMethodsToInclude = [];
    
    // Verificar qué métodos de pago están solicitados
    $paymentMethods = [
        'fastSell', 'boleta', 'factura', 'noSii', 'amipass', 'rappi', 'uber', 'junaeb', 
        'multicaja', 'edenred', 'sodexo', 'convenio_empresa', 'debito', 'credito', 
        'transferencia', 'cheque', 'banco', 'pluxee', 'pedidos_ya', 'guia_despacho', 
        'banco_chile_20', 'nota_de_credito', 'efectivo'
    ];
    
    foreach ($paymentMethods as $method) {
        if (isset($_request[$method])) {
            $hasPaymentFilters = true;
            $paymentMethodsToInclude[] = $method;
        }
    }
    
    $ventas = $ventasQuery->get();

   // Inicializar el contador de tipos de ventas
    $counters = [
        'quantityTotal' => 0,
        'factura' => 0,
        'efectivo' => 0,
        'boleta' => 0,
        'debito' => 0,
        'nota_de_credito' => 0,
        'credito' => 0,
        'transferencia' => 0,
        'cheque' => 0,
        'banco' => 0,
        'amipass' => 0,
        'multicaja' => 0,
        'edenred' => 0,
        'convenio_empresa' => 0,
        'sodexo' => 0,
        'rappi' => 0,
        'junaeb' => 0,
        'uber' => 0,
        'pedidos_ya' => 0,
        'pluxee' => 0,
        'banco_chile_20' => 0,
        'guia_despacho' => 0,
        'fastSells' => 0,
        'noSii' => 0,
        'balanceTotal' => 0,
        'gananciaTotal' => 0,
    ];

    // Condiciones de configuración
    $ajustes = [
      'factura' => ['modulos.ventas.submodulos.sii.ajustes.factura', ['factura']],
      'efectivo' => ['modulos.ventas.submodulos.sii.ajustes.boleta_local', ['boleta_local', 'efectivo']],
      'boleta' => ['modulos.ventas.submodulos.sii.ajustes.boleta', ['boleta']],
      'debito' => ['modulos.ventas.submodulos.sii.ajustes.debito', ['debito']],
      'nota_de_credito' => ['modulos.ventas.submodulos.sii.ajustes.nota_de_credito', ['nota_de_credito']],
      'credito' => ['modulos.ventas.submodulos.sii.ajustes.credito', ['credito']],
      'transferencia' => ['modulos.ventas.submodulos.sii.ajustes.transferencia', ['transferencia']],
      'cheque' => ['modulos.ventas.submodulos.sii.ajustes.cheque', ['cheque']],
      'banco' => ['modulos.ventas.submodulos.sii.ajustes.banco', ['banco']],
      'amipass' => ['modulos.ventas.submodulos.sii.ajustes.amipass', ['amipass']],
      'multicaja' => ['modulos.ventas.submodulos.sii.ajustes.multicaja', ['multicaja']],
      'edenred' => ['modulos.ventas.submodulos.sii.ajustes.edenred', ['edenred']],
      'convenio_empresa' => ['modulos.ventas.submodulos.sii.ajustes.convenio_empresa', ['convenio_empresa']],
      'sodexo' => ['modulos.ventas.submodulos.sii.ajustes.sodexo', ['sodexo']],
      'rappi' => ['modulos.ventas.submodulos.sii.ajustes.rappi', ['rappi']],
      'junaeb' => ['modulos.ventas.submodulos.sii.ajustes.junaeb', ['junaeb']],
      'uber' => ['modulos.ventas.submodulos.sii.ajustes.uber', ['uber']],
      'pedidos_ya' => ['modulos.ventas.submodulos.sii.ajustes.pedidos_ya', ['pedidos_ya']],
      'pluxee' => ['modulos.ventas.submodulos.sii.ajustes.pluxee', ['pluxee']],
      'guia_despacho' => ['modulos.ventas.submodulos.sii.ajustes.guia_despacho', ['guia_despacho']],
      'banco_chile_20' => ['modulos.ventas.submodulos.sii.ajustes.banco_chile_20', ['banco_chile_20']]
    ];

    if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
      $counters['orders'] = count($ventas);
    }
    
    //datos_opcionales
    foreach ($ventas as $venta) {
      $shouldIncludeThisSale = false;
      
      // Si no hay filtros específicos, incluir todas las ventas (comportamiento original)
      if (!$hasPaymentFilters) {
          $shouldIncludeThisSale = true;
      } else {
          // Verificar si esta venta coincide con algún filtro solicitado
          
          // Verificar fast sells
          if (in_array('fastSell', $paymentMethodsToInclude) && $venta->fast_sell) {
              $shouldIncludeThisSale = true;
          }
          
          // Verificar ventas sin SII
          if (in_array('noSii', $paymentMethodsToInclude) && !$venta->sell_folio) {
              $shouldIncludeThisSale = true;
          }
          
          // Verificar ventas con folio SII
          if ($venta->sell_folio) {
              $folio = Folio::where('sell_id', $venta->id)->first();
              if ($folio) {
                  if (in_array('factura', $paymentMethodsToInclude) && $folio->type == 'factura') {
                      $shouldIncludeThisSale = true;
                  }
                  if (in_array('boleta', $paymentMethodsToInclude) && $folio->type == 'boleta') {
                      $shouldIncludeThisSale = true;
                  }
              }
          }
          
          // Verificar otros tipos de pago
          foreach ($ajustes as $key => $config) {
              if (in_array($key, $paymentMethodsToInclude)) {
                  if (in_array($venta->other_type, $config[1]) || in_array($venta->paymode, $config[1]) || in_array($venta->type_sell, $config[1])) {
                      $shouldIncludeThisSale = true;
                      break;
                  }
              }
          }
      }
      
      // Solo procesar la venta si debe incluirse según los filtros
      if (!$shouldIncludeThisSale) {
          continue;
      }
      
      // calculado el total de todas las ventas
      $counters['balanceTotal']  += (float) $venta->total;
      // calculando la ganancia total
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $counters['gananciaTotal'] += $venta->gananciaTotal;
      }

      $counted = false;
      foreach ($ajustes as $key => $config) {
          if (CurrentApp::ConfStr($config[0])) {
              if (in_array($venta->other_type, $config[1]) || in_array($venta->paymode, $config[1]) || in_array($venta->type_sell, $config[1])) {
                  $counters[$key] += $venta->total;
                  $counted = true;
                  break; // Evitar duplicidad
              }
          }
      }

      // Procesar fast sells
      if (!$counted && $venta->fast_sell && CurrentApp::ConfStr('modulos.ventas.submodulos.sell_fast')) {
          $counters['fastSells'] += $venta->total;
          $counted = true;
      }

      // Procesar ventas sin SII
      if (!$counted && !$venta->sell_folio && isset($_request['noSii'])) {
          $counters['noSii'] += $venta->total;
      } elseif (!$counted && $venta->sell_folio) {
          $folio = Folio::where('sell_id', $venta->id)->first();
          if ($folio) {
              if (isset($_request['factura']) && $folio->type == 'factura') {
                  $counters['factura'] += $venta->total;
              } elseif (isset($_request['boleta']) && $folio->type == 'boleta') {
                  $counters['boleta'] += $venta->total;
              }
          } else {
              unset($venta);
          }
      }

    }
    
    // Después de procesar todas las ventas, asegurar que los métodos de pago configurados aparezcan
    // aunque no tengan ventas (con valor 0)
    $activePaymentMethods = PaymentMethodHelper::getActivePaymentMethods();
    
    foreach ($activePaymentMethods as $method) {
        // Asegurar que todos los métodos configurados aparezcan en los contadores
        if (!isset($counters[$method])) {
            $counters[$method] = 0;
        }
    }
    
    // También verificar los métodos especiales
    if (isset($_request['noSii'])) {
        if (!isset($counters['noSii'])) {
            $counters['noSii'] = 0;
        }
    }

    // Solo procesar productos si hay ventas que cumplan con los filtros
    $products_sells = ProductSell::whereBetween('created_at', [$_request['startDate'], $_request['endDate']]);
    
    // Si hay filtros de métodos de pago, también filtrar los productos vendidos
    if ($hasPaymentFilters) {
        $validSellIds = [];
        foreach ($ventas as $venta) {
            $shouldIncludeThisSale = false;
            
            // Repetir la misma lógica de filtrado para obtener IDs válidos
            if (in_array('fastSell', $paymentMethodsToInclude) && $venta->fast_sell) {
                $shouldIncludeThisSale = true;
            }
            
            if (in_array('noSii', $paymentMethodsToInclude) && !$venta->sell_folio) {
                $shouldIncludeThisSale = true;
            }
            
            if ($venta->sell_folio) {
                $folio = Folio::where('sell_id', $venta->id)->first();
                if ($folio) {
                    if (in_array('factura', $paymentMethodsToInclude) && $folio->type == 'factura') {
                        $shouldIncludeThisSale = true;
                    }
                    if (in_array('boleta', $paymentMethodsToInclude) && $folio->type == 'boleta') {
                        $shouldIncludeThisSale = true;
                    }
                }
            }
            
            foreach ($ajustes as $key => $config) {
                if (in_array($key, $paymentMethodsToInclude)) {
                    if (in_array($venta->other_type, $config[1]) || in_array($venta->paymode, $config[1]) || in_array($venta->type_sell, $config[1])) {
                        $shouldIncludeThisSale = true;
                        break;
                    }
                }
            }
            
            if ($shouldIncludeThisSale) {
                $validSellIds[] = $venta->id;
            }
        }
        
        if (!empty($validSellIds)) {
            $products_sells = $products_sells->whereIn('sell', $validSellIds);
        } else {
            // Si no hay ventas válidas, no hay productos vendidos
            $products_sells = collect([]);
            $counters['quantityTotal'] = 0;
            $counters['typeProducts'] = 0;
            return response()->json($counters);
        }
    }
    
    $products_sells = $products_sells->get();
    $uniqueProducts = [];

    // Array para guardar el reporte de productos
    $products = [];

    foreach ($products_sells as $product_sell) {

        //Cantidad de productos vendidos
        $counters['quantityTotal'] += $product_sell->quantity;

        $product = Product::with('productCategory')->find($product_sell->product);
        if ($product) {
            $productId = $product->id;
            $sellPrice = $product_sell->price;

            $categoryName = 'Sin categoría'; // Valor predeterminado
            // Comprobación explícita de null
            if ($product->productCategory !== null) {
                $categoryName = $product->productCategory->name;
            }

            $uniqueKey = $productId . '_' . $sellPrice; // Crear una clave única combinando ID y precio

            if (!isset($uniqueProducts[$uniqueKey])) {
                $uniqueProducts[$uniqueKey] = [
                    'name' => $product->name,
                    'quantity' => 0,
                    'price' => $sellPrice,
                    'totalProfit' => 0,
                    'category' => $categoryName,
                    'product_id' => $productId // Opcional: guardar el ID del producto para referencia
                ];
            }

            $uniqueProducts[$uniqueKey]['quantity'] += $product_sell->quantity;
            $uniqueProducts[$uniqueKey]['totalProfit'] += $product_sell->gananciaTotal;
        }
    }

    // Convertir a array de productos
    foreach ($uniqueProducts as $productData) {
        $products[] = $productData;
    }

    // Contar la cantidad de productos únicos
    $counters['typeProducts'] = count($uniqueProducts);    

    // Gastos del dia
    $expenses = false;
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.expenses_day')) {
      $counters['expenses_day'] = 0;
      $expenses = Expense::whereBetween('created_at', [$_request['startDate'], $_request['endDate']])->get();

      foreach ($expenses as $expense) {
        $counters['expenses_day'] += $expense->balance;
      }

      $counters['totalToExpenses'] = (float) $counters['balanceTotal'] - (float) $counters['expenses_day'];
    }

    //waiters NO OPTIMIZADO ESTA PARTE DE LOS MESEROS
    $date = Carbon::now();
    // Cantidad de mesas atendidas por mesaras y totales
    $waiters = array();
    if (CurrentApp::ConfStr('modulos.cafeteria')) {

      $orders = Order::where('state', 'procesada')
        ->where('created_at', '>=', $_request['startDate'])
        ->where('created_at', '<=', $_request['endDate']);
      // Obtenerlas
      $orders = $orders->get();


      $allWaiters = Waiter::where('trash', 0)->get();
      foreach ($allWaiters as $waiter) {

        $waiterNow = [
          'waiter'  => $waiter->name,
          'orders'  => 0,
          'total'   => 0,
          'propina' => 0
        ];

        if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.perforance_mesero_mesa_report')) {
          // Submodulo de mesas de cada waiter
          $waiterNow['boards_detailed'] = [];
          $waiterNow['total_products_selled'] = 0;
        }

        // Ordenes de este waiter (facepalm)
        foreach ($orders as $order) {
          if ($waiter->id == $order->waiter_id) {

            $waiterNow['orders']++;
            $waiterNow['total'] += $order->total;
            $waiterNow['propina'] += $order->tip;

            if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.perforance_mesero_mesa_report')) {
              // Submodulo de mesas de cada waiter
              if ($order->board_id && $order->board) {
                if (!isset($waiterNow['boards_detailed'][$order->board_id])) {
                  $waiterNow['boards_detailed'][$order->board_id] = ['name' => '', 'total' => 0, 'orders' => 0, 'cantidad_productos' => 0];
                  $waiterNow['boards_detailed'][$order->board_id]['name']  = $order->board->name;
                }
                $waiterNow['boards_detailed'][$order->board_id]['orders']++;
                $waiterNow['boards_detailed'][$order->board_id]['total'] += $order->total;
                $waiterNow['boards_detailed'][$order->board_id]['cantidad_productos'] += $order->cantidad_productos;
              }
              $waiterNow['total_products_selled'] += $order->cantidad_productos;
            }
          }
        }

        // Submodulo de adiciones
        if (CurrentApp::ConfStr('modulos.cafeteria.submodulos.additions_waiter')) {
          $waiterJobs = Addition_Waiter::where('waiter', $waiter->id)->where('created_at', '>=', $date->format('Y-m-d 00:00:00'))
            ->where('created_at', '<=', $date->format('Y-m-d 23:59:59'))->get();
          $app = CurrentApp::App();
          $envs = json_decode($app->environment_vars);
          $waiterNow['addtions'] = [
            'quantity'     => 0,
            'balanceTotal' => 0
          ];
          foreach ($waiterJobs as $job) $waiterNow['addtions']['quantity'] += $job->quantity;
          $waiterNow['addtions']['balanceTotal'] = (float) $waiterNow['addtions']['quantity'] * (float) $envs->default_value_addtion->value;
        }



        $waiters[] = $waiterNow;
      }
    }

      // Turnos del dia
      $currentDate = Carbon::now()->toDateString();
  
      $workshifts = Workshift::with('user')
        ->whereDate('created_at', $currentDate)
        ->get();
        
    if ($self) {
      return ['counters' => $counters, 'products' => $products, 'waiters' => $waiters, 'expenses' => $expenses, 'workshifts' => $workshifts];
    }
    return response()->json(['counters' => $counters, 'products' => $products, 'waiters' => $waiters, 'expenses' => $expenses, 'workshifts' => $workshifts], 200);
  }

  public function getCountersOLD(Request $request, $self = false)
  {
    $_request = $request->all();
    $app = CurrentApp::App();

    $database2 = Config::get('database.connections.mysql_local.database');
    $ventas = DB::table($database2 . '.sells')->where('sells.trash', 0)
      ->where('created_at', '>=', $_request['startDate'])
      ->where('created_at', '<=', $_request['endDate'])->get();
      

    // return response()->json($ventas,222);
    if (!$ventas) return response()->json('Error del servidor', 500);

    $counters = [
      'init_money' => $app['init_money'],
      'balanceTotal' => 0,
      'gananciaTotal' => 0,
      'factura' => 0,
      'boleta' => 0,
      'fastSells' => 0,
      'noSii' => 0,
      // 'debito' => 0,
      // 'credito' => 0,
      // 'transferencia' => 0,
      // 'cheque' => 0,
      // 'banco' => 0,
      // 'amipass' => 0,
      // 'rappi' => 0,
      // 'junaeb' => 0,
      // 'multicaja' => 0,
      // 'convenio_empresa' => 0,
      // 'edenred' => 0,
      // 'sodexo' => 0

    ];
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
      $counters['orders'] = count($ventas);
      $counters['typeProducts'] = 0;
      $counters['quantityTotal'] = 0;
    }

    $products = [];
    $IDsDeProductos = [];

    $NV = 0;
    $quantityTotal = 0;
    foreach ($ventas as $venta) {
      $NV++;
      $return = false;

      if ($venta->other_type != null || $venta->typeSell != null || $venta->paymode != null) {
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura')) {
          if ($venta->other_type == 'factura' || $venta->paymode == 'factura') {
            $counters['factura'] = isset($counters['factura']) ? $counters['factura'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local')) {
          if ($venta->other_type == 'boleta_local' || $venta->other_type == 'efectivo' || $venta->paymode == 'boleta_local') {
            $counters['efectivo'] = isset($counters['efectivo']) ? $counters['efectivo'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta')) {
          if ($venta->other_type == 'boleta' || $venta->paymode == 'boleta' || $venta->typeSell == 'boleta') {
            $counters['boleta'] = isset($counters['boleta']) ? $counters['boleta'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.debito')) {
          if ($venta->other_type == 'debito' || $venta->paymode == 'debito') {
            $counters['debito'] = isset($counters['debito']) ? $counters['debito'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.nota_de_credito')) {
          if ($venta->other_type == 'nota_de_credito' || $venta->paymode == 'nota_de_credito') {
            $counters['nota_de_credito'] = isset($counters['nota_de_credito']) ? $counters['nota_de_credito'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.credito')) {
          if ($venta->other_type == 'credito' || $venta->paymode == 'credito') {
            $counters['credito'] = isset($counters['credito']) ? $counters['credito'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.transferencia')) {
          if ($venta->other_type == 'transferencia' || $venta->paymode == 'transferencia') {
            $counters['transferencia'] = isset($counters['transferencia']) ? $counters['transferencia'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.cheque')) {
          if ($venta->other_type == 'cheque' || $venta->paymode == 'cheque') {
            $counters['cheque'] = isset($counters['cheque']) ? $counters['cheque'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.banco')) {
          if ($venta->other_type == 'banco' || $venta->paymode == 'banco') {
            $counters['banco'] = isset($counters['banco']) ? $counters['banco'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.amipass')) {
          if ($venta->other_type == 'amipass' || $venta->paymode == 'amipass') {
            $counters['amipass'] = isset($counters['amipass']) ? $counters['amipass'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.multicaja')) {
          if ($venta->other_type == 'multicaja' || $venta->paymode == 'multicaja') {
            $counters['multicaja'] = isset($counters['multicaja']) ? $counters['multicaja'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.edenred')) {
          if ($venta->other_type == 'edenred' || $venta->paymode == 'edenred') {
            $counters['edenred'] = isset($counters['edenred']) ? $counters['edenred'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.convenio_empresa')) {
          if ($venta->other_type == 'convenio_empresa' || $venta->paymode == 'convenio_empresa') {
            $counters['convenio_empresa'] = isset($counters['convenio_empresa']) ? $counters['convenio_empresa'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.sodexo')) {
          if ($venta->other_type == 'sodexo' || $venta->paymode == 'sodexo') {
            $counters['sodexo'] = isset($counters['sodexo']) ? $counters['sodexo'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.rappi')) {
          if ($venta->other_type == 'rappi' || $venta->paymode == 'rappi') {
            $counters['rappi'] = isset($counters['rappi']) ? $counters['rappi'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.junaeb')) {
          if ($venta->other_type == 'junaeb' || $venta->paymode == 'junaeb') {
            $counters['junaeb'] = isset($counters['junaeb']) ? $counters['junaeb'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.uber')) {
          if ($venta->other_type == 'uber' || $venta->paymode == 'uber') {
            $counters['uber'] = isset($counters['uber']) ? $counters['uber'] + $venta->total : $venta->total;
          }
        }
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.guia_despacho')) {
          if ($venta->other_type == 'guia_despacho' || $venta->paymode == 'guia_despacho') {
            $counters['guia_despacho'] = isset($counters['guia_despacho']) ? $counters['guia_despacho'] + $venta->total : $venta->total;
          }
        }
      } else if ($venta->fast_sell) {
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sell_fast')) {
          $counters['fastSells'] = isset($counters['fastSells']) ? $counters['fastSells'] + $venta->total : $venta->total;
        }
      } else {
        // $folio = Folio::where('sell_id', $venta->id)->first();
        $folio = DB::table($database2 . '.folios')->where('sell_id', $venta->id)->first();
        if (!$folio) {
          if (isset($_request['noSii'])) {
            $counters['noSii'] += $venta->total;
          } else {
            unset($venta);
            $return = true;
          }
        } else {
          if (isset($_request['factura']) && $folio->type == 'factura') {
            $counters['factura'] += $venta->total;
          } else if (isset($_request['boleta']) && $folio->type == 'boleta') {
            $counters['boleta'] += $venta->total;
          } else {
            unset($venta);
            $return = true;
          }
        }
      }

      if(!$return){
        // calculado el total de todas las ventas
        $counters['balanceTotal'] = $counters['balanceTotal'] + (float) $venta->total;

        $products_sells = ProductSell::where('sell', $venta->id)->get();

        foreach ($products_sells as $productSell) {
          // calculado los tipos de productos comprados
          $data = array_search($productSell->product, $IDsDeProductos);
          if($data === false) {
            $IDsDeProductos[] = $productSell->product;
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
              $counters['typeProducts'] = $counters['typeProducts'] + 1;//Contador
            }
          }

          // calculando la cantidad de prodcutos comprados
          if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
            $counters['quantityTotal'] = $counters['quantityTotal'] + (float) $productSell->quantity;
          }else{
            $quantityTotal =  $quantityTotal + (float) $productSell->quantity;
          }

          // calculando la ganancia total
          if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
            $counters['gananciaTotal'] += $productSell->gananciaTotal;
          }
        }

        // Recorriendo tipos de productos
        for ($i=0; $i < count($IDsDeProductos); $i++) {

          $producto = Product::find($IDsDeProductos[$i]);
          $categoria = Category::find($producto['category']);
          
          $nP = $i;

          if (!isset($products[$nP])) {
            $products[$nP] = [$IDsDeProductos[$i], $producto['name'], 0, 0, 0];
            // if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
            //   $products[$nP][_ganancia_total] = 0;
            // }
          }

          foreach ($products_sells as $productSell) {
            if($productSell->product == $products[$nP][0]){

              /*//Si no estan establecidos, definelos
              if (!isset($products[$nP][_monto_total])) $products[$nP][_monto_total] = 0;
              if (!isset($products[$nP][_cantidad_total])) $products[$nP][_cantidad_total] = 0;
              if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
              if (!isset($products[$nP][_ganancia_total])) $products[$nP][_ganancia_total] = 0;
            }*/

            //Se suman
            $products[$nP][_cantidad_total] += (float) $productSell->quantity;
            $products[$nP][_monto_total] += (float) $productSell->price * $productSell->quantity;
            $products[$nP][_categoria] = $categoria['name'];
            if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
              $products[$nP][_ganancia_total] += (float) $productSell->gananciaTotal;
            }
          }
        }
        }
      }
    }

    foreach ($products as $key => $value) {
      $multi_data_quantity = (float) $value[_cantidad_total] * 100;
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
        $porcentajeQuantity = $multi_data_quantity / (float) $counters['quantityTotal'];
      } else {
        $porcentajeQuantity = $multi_data_quantity / (float) $quantityTotal;
      }


      $multi_data_balance = (float) $value[_monto_total] * 100;
      $porcentajeBalance = $multi_data_balance / (float) $counters['balanceTotal'];

      $products[$key][_procentaje_quantity] = sprintf('%01.2f', $porcentajeQuantity);
      $products[$key][_procentaje_balance] = sprintf('%01.2f', $porcentajeBalance);

      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $multi_data_ganancia = (float) $value[_ganancia_total] * 100;
        if ((float) $counters['gananciaTotal'] != 0) $porcentajeGanancia = $multi_data_ganancia / (float) $counters['gananciaTotal'];
        else $porcentajeGanancia = 0;

        $products[$key][_procentaje_ganancia] = sprintf('%01.2f', $porcentajeGanancia);
      }
    }
    $date = Carbon::now();
    // Cantidad de mesas atendidas por mesaras y totales
    $waiters = array();
    if (CurrentApp::ConfStr('modulos.cafeteria')) {

      // Query
      // $orders = DB::table($database2 . '.orders')->where('state','procesada')
      // ->where('created_at', '>=', $_request['startDate'])
      // ->where('created_at', '<=', $_request['endDate']);

      $orders = Order::/*table($database2.'.orders')*/where('state', 'procesada')
        ->where('created_at', '>=', $_request['startDate'])
        ->where('created_at', '<=', $_request['endDate']);
      // Obtenerlas
      $orders = $orders->get();


      $allWaiters = DB::table($database2.'.waiters')->where('trash', 0)->get();
      // $allWaiters = Waiter::where('trash', 0)->get();
      foreach ($allWaiters as $waiter) {

        $waiterNow = [
          'waiter'  => $waiter->name,
          'orders'  => 0,
          'total'   => 0,
          'propina' => 0
        ];

        if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.perforance_mesero_mesa_report')) {
          // Submodulo de mesas de cada waiter
          $waiterNow['boards_detailed'] = [];
          $waiterNow['total_products_selled'] = 0;
        }

        // Ordenes de este waiter (facepalm)
        foreach ($orders as $order) {
          if ($waiter->id == $order->waiter_id) {

            $waiterNow['orders']++;
            $waiterNow['total'] += $order->total;
            $waiterNow['propina'] += $order->tip;

            if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.perforance_mesero_mesa_report')) {
              // Submodulo de mesas de cada waiter
              if ($order->board_id && $order->board) {
                if (!isset($waiterNow['boards_detailed'][$order->board_id])) {
                  $waiterNow['boards_detailed'][$order->board_id] = ['name' => '', 'total' => 0, 'orders' => 0, 'cantidad_productos' => 0];
                  $waiterNow['boards_detailed'][$order->board_id]['name']  = $order->board->name;
                }
                $waiterNow['boards_detailed'][$order->board_id]['orders']++;
                $waiterNow['boards_detailed'][$order->board_id]['total'] += $order->total;
                $waiterNow['boards_detailed'][$order->board_id]['cantidad_productos'] += $order->cantidad_productos;
              }
              $waiterNow['total_products_selled'] += $order->cantidad_productos;
            }
          }
        }

        // Submodulo de adiciones
        if (CurrentApp::ConfStr('modulos.cafeteria.submodulos.additions_waiter')) {
          $waiterJobs = Addition_Waiter::where('waiter', $waiter->id)->where('created_at', '>=', $date->format('Y-m-d 00:00:00'))
            ->where('created_at', '<=', $date->format('Y-m-d 23:59:59'))->get();
          $app = CurrentApp::App();
          $envs = json_decode($app->environment_vars);
          $waiterNow['addtions'] = [
            'quantity'     => 0,
            'balanceTotal' => 0
          ];
          foreach ($waiterJobs as $job) $waiterNow['addtions']['quantity'] += $job->quantity;
          $waiterNow['addtions']['balanceTotal'] = (float) $waiterNow['addtions']['quantity'] * (float) $envs->default_value_addtion->value;
        }



        $waiters[] = $waiterNow;
      }
    }
    // Gastos del dia
    $expenses = false;
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.expenses_day')) {
      $counters['expenses_day'] = 0;
      $expenses = DB::table($database2.'.expenses')->where('created_at', '>=', $_request['startDate'])
                                                   ->where('created_at', '<=', $_request['endDate'])->get();
      // $expenses = Expense::where('created_at', '>=', $_request['startDate'])
      //   ->where('created_at', '<=', $_request['endDate'])
      //   ->get();

      foreach ($expenses as $expense) {
        $counters['expenses_day'] += $expense->balance;
      }

      $counters['totalToExpenses'] = (float) $counters['balanceTotal'] - (float) $counters['expenses_day'];
    }

    // Turnos del dia
    $currentDate = Carbon::now()->toDateString();

    // $workshifts = DB::table($database2 . '.workshifts')
    // ->join('users', 'workshifts.user_id', '=', 'users.id')  // Assuming a join
    // ->select('workshifts.*', 'users.*')                // Example select
    // ->whereDate('workshifts.created_at', $currentDate)
    // ->get();

    $workshifts = Workshift::with('user')
      ->whereDate('created_at', $currentDate)
      ->get();

    if ($self) {
      return ['counters' => $counters, 'products' => $products, 'waiters' => $waiters, 'expenses' => $expenses, 'workshifts' => $workshifts];
    }
    return response()->json(['counters' => $counters, 'products' => $products, 'waiters' => $waiters, 'expenses' => $expenses, 'workshifts' => $workshifts], 200);
  }

  public function getOneWaiter(Request $request)
  {
    // if (CurrentApp::ConfStr('modulos.cafeteria')) {
    $database = Config::get('database.connections.mysql_local.database');

    $waiter = DB::table($database . '.waiters')->where('id', $request['waiter_id'])->get();

    $ordenes = DB::table($database . '.orders')->where('state', 'procesada')
      ->where('waiter_id', $request['waiter_id'])
      ->where('created_at', '>=', $request['startDate'])
      ->where('created_at', '<=', $request['endDate'])->get();

    /*
      [
        item:[
          board_name,
          products: [
            nombre,
            precio,
            cantidad
          ],
          tip,
          discount,
          total,
        ]
      ]
    */

    $respuesta = [
      'waiter_id' => $request['waiter_id'],
      // 'name' => $waiter[0]->name,
      'database' => $database,
      'startDate' => $request['startDate'],
      'endDate' => $request['endDate'],
      'report' => [],
      'report_count' => count($ordenes)
    ];


    foreach ($ordenes as $orden) {
      $item = [];
      $board = DB::table($database . '.boards')->where('id', $orden->board_id)->get();
      $item['board_name'] = $board[0]->name;
      $item['products'] = $orden->products;
      //subtotal se calcula en el cliente
      $item['total'] = $orden->total;
      $item['tip'] = $orden->tip;
      $item['discount'] = $orden->discount;
      $respuesta['report'][] = $item;
    }






    return response()->json($respuesta, 200);
    // }else{
    // return response()->json('Modulo cafeteria desactivado',200);
    // }
  }

  public function printReport(Request $request)
  {
    $dates = $request->all();
    $data = $this->getCounters($request, true);
    $app = CurrentApp::App();
    $data['dates'] = [
      'startDate' => $dates['startDate'],
      'endDate' => $dates['endDate']
    ];
    $data['envs'] = json_decode($app->environment_vars);
    $size = array(0, 0, 227, 600);

    $pdf = \PDF::loadView($dates['params'], compact('data'))->setPaper($size);
    $name = 'reporte_' . uniqid() . '.pdf';
    Storage::put('public/pdf/' . $name, $pdf->output());

    /*
    $path = Storage::url('public/pdf/'.$name);
    return response()->json($path,200);*/
    $b64Doc = chunk_split(base64_encode($pdf->output()));
    return response()->json($b64Doc, 200);
  }

  public function getFacturacion(Request $request)
  {
      $_request = $request->all();
      //Directamente la base de datos que se usa para facturar****
      $folios = DB::table('erd_app_facturacion_fafotto_66420ee700c12' . '.folios')
                ->where('type', 'factura')
                ->where('updated_at', '>=', $_request['startDate'])
                ->where('updated_at', '<=', $_request['endDate'])
                ->get();
  
      $result = [];
      foreach ($folios as $folio) {
          // Verificar si xml_string no es nula o vacía
          if (empty($folio->xml_string)) {
              continue;
          }
  
          // Reemplazar solo los caracteres problemáticos en el XML
          $xmlStringSanitized = str_replace('&', '&amp;', $folio->xml_string);
  
          // Intentar cargar el XML y manejar errores
          libxml_use_internal_errors(true);
          $xml = simplexml_load_string($xmlStringSanitized);
  
          if ($xml === false) {
              // Añadir un mensaje de error específico al resultado
              $result[] = [
                  'FolioID' => $folio->id,
                  'error' => 'Error al cargar XML: ' . implode(", ", array_map(function($error) {
                      return $error->message;
                  }, libxml_get_errors()))
              ];
              libxml_clear_errors();
              continue;
          }
  

          // Extraer los datos requeridos
          $rznSocRecep = (string)$xml->Documento->Encabezado->Receptor->RznSocRecep;
          $rutRecep = (string)$xml->Documento->Encabezado->Receptor->RUTRecep;
          $mntTotal = (string)$xml->Documento->Encabezado->Totales->MntTotal;
          $pdf = (string)$folio->pdf_url;
          // Añadir los datos extraídos al resultado
          $result[] = [
              'FolioID' => $folio->id,
              'RznSocRecep' => $rznSocRecep,
              'RUTRecep' => $rutRecep,
              'MntTotal' => $mntTotal,
              'pdf' => $pdf
          ];
      }
  
      return response()->json($result);
  }
  
  /**
   * Exportar Excel personalizado con todos los datos de reportes
   */
  public function exportCustomExcel(Request $request)
  {
      try {
          $startDate = $request->input('startDate', date('Y-m-d'));
          $endDate = $request->input('endDate', date('Y-m-d') . ' 23:59:59');
          $id = $request->input('id');
          
          // Debug: Log de parámetros recibidos
          \Log::info('Excel Export - Parámetros recibidos:', [
              'startDate' => $startDate,
              'endDate' => $endDate,
              'id' => $id,
              'all_params' => $request->all()
          ]);
          
          // MÉTODO DIRECTO: Obtener ventas directamente de la base de datos sin filtros complejos
          $database2 = Config::get('database.connections.mysql_local.database');
          
          $sellsData = DB::table($database2 . '.sells')
              ->where('sells.trash', 0)
              ->where('sells.created_at', '>=', $startDate)
              ->where('sells.created_at', '<=', $endDate)
              ->leftJoin($database2 . '.users', 'users.id', 'sells.user')
              ->select(
                  'sells.id',
                  'sells.total',
                  'sells.created_at',
                  'sells.updated_at',
                  'sells.type_sell',
                  'sells.paymode',
                  'sells.other_type',
                  'users.fullname as user_name'
              )
              ->orderBy('sells.created_at', 'desc')
              ->get();
          
          // Convertir a array para facilitar el manejo
          $sellsArray = $sellsData->toArray();
          
          // Debug: Log de cantidad de ventas encontradas
          \Log::info('Excel Export - Ventas encontradas (método directo):', [
              'count' => count($sellsArray),
              'first_sell' => !empty($sellsArray) ? $sellsArray[0] : 'no data',
              'date_range' => "FROM {$startDate} TO {$endDate}",
              'database' => $database2
          ]);
          
          // Obtener contadores usando el método existente
          $countersRequest = new Request([
              'startDate' => $startDate,
              'endDate' => $endDate,
              'id' => $id
          ]);
          $countersData = $this->getCounters($countersRequest, true);
          
          // Crear directorio si no existe
          $excelDir = storage_path('app/public/excel');
          if (!File::exists($excelDir)) {
              File::makeDirectory($excelDir, 0755, true);
          }
          
          // Crear el Excel
          $filename = 'reporte_completo_' . time() . '.xlsx';
          $fullPath = $excelDir . '/' . $filename;
          
          Excel::store(new CustomReportExport($countersData, $sellsArray, $startDate, $endDate), 'excel/' . $filename, 'public');
          
          // Verificar que el archivo se creó correctamente
          if (!File::exists($fullPath)) {
              throw new \Exception('No se pudo crear el archivo Excel');
          }
          
          \Log::info('Excel Export - Archivo creado exitosamente:', [
              'path' => $fullPath,
              'size' => File::size($fullPath)
          ]);
          
          return $this->handlerGetFile($fullPath);
          
      } catch (\Exception $e) {
          \Log::error('Excel Export Error:', [
              'error' => $e->getMessage(),
              'trace' => $e->getTraceAsString()
          ]);
          return response()->json('Error al crear excel: ' . $e->getMessage(), 500);
      }
  }
  
  /**
   * Transformar archivo a base64
   */
  public function handlerGetFile($path) 
  {
      try {
          if (!File::exists($path)) {
              \Log::error('Excel Handler - Archivo no encontrado:', ['path' => $path]);
              return response()->json('Error al crear excel - archivo no encontrado', 500);
          }
          
          $fileSize = File::size($path);
          \Log::info('Excel Handler - Procesando archivo:', [
              'path' => $path,
              'size' => $fileSize
          ]);
          
          if ($fileSize === 0) {
              \Log::error('Excel Handler - Archivo vacío:', ['path' => $path]);
              return response()->json('Error al crear excel - archivo vacío', 500);
          }
          
          $file = File::get($path);
          $b64Doc = chunk_split(base64_encode($file));
          
          // Eliminar archivo temporal
          File::delete($path);
          
          \Log::info('Excel Handler - Archivo procesado exitosamente:', [
              'original_size' => $fileSize,
              'base64_length' => strlen($b64Doc)
          ]);
          
          return response()->json($b64Doc, 200);
          
      } catch (\Exception $e) {
          \Log::error('Excel Handler Error:', [
              'error' => $e->getMessage(),
              'path' => $path
          ]);
          return response()->json('Error al procesar excel: ' . $e->getMessage(), 500);
      }
  }
  
}
