<?php

namespace App\Http\Controllers\Controllers_local;

// Models
use App\models_local\ProductSell;
use App\models_local\Product;
use App\models_local\Sell;
use App\models_local\Order;
use App\models_local\Addition_Waiter;
use App\models_local\Workshift;
use App\models_local\Folio;
use App\models_local\Waiter;
use App\models_local\Expense;
use App\User;

// Helpers
use App\Helpers\CurrentApp;
use Config;

// Laravel
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;


define('_cantidad_total', 2);
define('_monto_total', 3);
define('_ganancia_total', 4);

define('_procentaje_quantity', 5);
define('_procentaje_balance', 6);
define('_procentaje_ganancia', 7);

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

      $salesCount = Sell::whereBetween('created_at', [$currentHour, $nextHour])
        ->count();

      $salesByHour[$currentHour->format('H:i:s')] = $salesCount;

      $currentHour = $nextHour;
    }

    return response()->json($salesByHour);
  }

  public function getCounters(Request $request, $self = false)
  {
    $_request = $request->all();
    $app = CurrentApp::App();

    $database2 = Config::get('database.connections.mysql_local.database');
    // $ventas = DB::table($database2.'.sells')->where('sells.trash', 0)
    //                                         ->where('created_at', '>=', $_request['startDate'])
    //                                         ->where('created_at', '<=', $_request['endDate'])->get();
    $ventas = Sell::where('trash', 0)
      ->where('created_at', '>=', $_request['startDate'])
      ->where('created_at', '<=', $_request['endDate'])
      ->get();

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
          if ($venta->other_type == 'factura') {
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
      } else if ($venta->fast_sell) {
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sell_fast')) {
          $counters['fastSells'] = isset($counters['fastSells']) ? $counters['fastSells'] + $venta->total : $venta->total;
        }
      } else {
        $folio = Folio::where('sell_id', $venta->id)->first();
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

      if (!$return) {
        // Calculando el total de todas las ventas
        $counters['balanceTotal'] += (float) $venta->total;

        $products_sells = ProductSell::where('sell', $venta->id)->get();
        
        $IDsDeProductos = [];
        $products = [];

        foreach ($products_sells as $productSell) {
          $productID = $productSell->product;

          // Calculando los tipos de productos comprados
          if (!in_array($productID, $IDsDeProductos)) {
            $IDsDeProductos[] = $productID;
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
              $counters['typeProducts']++; // Contador
            }
          }

          // Calculando la cantidad de productos comprados
          if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
            $counters['quantityTotal'] += (float) $productSell->quantity;
          } else {
            $quantityTotal += (float) $productSell->quantity;
          }

          // Calculando la ganancia total
          if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
            $counters['gananciaTotal'] += $productSell->gananciaTotal;
          }

          // Recorriendo tipos de productos
          for ($i = 0; $i < count($IDsDeProductos); $i++) {
            $productID = $IDsDeProductos[$i];
            $producto = Product::find($productID);
            $nP = $i;

            if (!isset($products[$nP])) {
              $products[$nP] = [$productID, $producto->name, 0, 0, 0];
            }

            if ($productSell->product == $products[$nP][0]) {
              // Se suman
              $products[$nP][_cantidad_total] += (float) $productSell->quantity;
              $products[$nP][_monto_total] += $productSell->price;
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
      $porcentajeQuantity = 0;

      if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
        $porcentajeQuantity = $multi_data_quantity / (float) $counters['quantityTotal'];
      } elseif ($quantityTotal != 0) {
        $porcentajeQuantity = $multi_data_quantity / (float) $quantityTotal;
      }

      $multi_data_balance = (float) $value[_monto_total] * 100;
      $porcentajeBalance = $multi_data_balance / (float) $counters['balanceTotal'];

      $products[$key][_procentaje_quantity] = sprintf('%01.2f', $porcentajeQuantity);
      $products[$key][_procentaje_balance] = sprintf('%01.2f', $porcentajeBalance);

      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $multi_data_ganancia = (float) $value[_ganancia_total] * 100;
        $porcentajeGanancia = 0;

        if ((float) $counters['gananciaTotal'] != 0) {
          $porcentajeGanancia = $multi_data_ganancia / (float) $counters['gananciaTotal'];
        }

        $products[$key][_procentaje_ganancia] = sprintf('%01.2f', $porcentajeGanancia);
      }
    }
    $date = Carbon::now();
    $waiters = [];

    if (CurrentApp::ConfStr('modulos.cafeteria')) {
      $orders = Order::where('state', 'procesada')
        ->whereBetween('created_at', [$_request['startDate'], $_request['endDate']])
        ->get();

      $allWaiters = Waiter::where('trash', 0)->get();

      foreach ($allWaiters as $waiter) {
        $waiterNow = [
          'waiter' => $waiter->name,
          'orders' => 0,
          'total' => 0,
          'propina' => 0
        ];

        if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.perforance_mesero_mesa_report')) {
          $waiterNow['boards_detailed'] = [];
          $waiterNow['total_products_selled'] = 0;
        }

        foreach ($orders as $order) {
          if ($waiter->id == $order->waiter_id) {
            $waiterNow['orders']++;
            $waiterNow['total'] += $order->total;
            $waiterNow['propina'] += $order->tip;

            if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.perforance_mesero_mesa_report')) {
              if ($order->board_id && $order->board) {
                $boardId = $order->board_id;
                if (!isset($waiterNow['boards_detailed'][$boardId])) {
                  $waiterNow['boards_detailed'][$boardId] = [
                    'name' => $order->board->name,
                    'total' => 0,
                    'orders' => 0,
                    'cantidad_productos' => 0
                  ];
                }
                $waiterNow['boards_detailed'][$boardId]['orders']++;
                $waiterNow['boards_detailed'][$boardId]['total'] += $order->total;
                $waiterNow['boards_detailed'][$boardId]['cantidad_productos'] += $order->cantidad_productos;
              }
              $waiterNow['total_products_selled'] += $order->cantidad_productos;
            }
          }
        }

        if (CurrentApp::ConfStr('modulos.cafeteria.submodulos.additions_waiter')) {
          $waiterJobs = Addition_Waiter::where('waiter', $waiter->id)
            ->whereBetween('created_at', [
              $date->format('Y-m-d 00:00:00'),
              $date->format('Y-m-d 23:59:59')
            ])
            ->get();

          $app = CurrentApp::App();
          $envs = json_decode($app->environment_vars);

          $waiterNow['addtions'] = [
            'quantity' => 0,
            'balanceTotal' => 0
          ];

          foreach ($waiterJobs as $job) {
            $waiterNow['addtions']['quantity'] += $job->quantity;
          }

          $waiterNow['addtions']['balanceTotal'] = (float) $waiterNow['addtions']['quantity'] * (float) $envs->default_value_addtion->value;
        }

        $waiters[] = $waiterNow;
      }
    }
    // Gastos del dia
    $expenses = false;
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.expenses_day')) {
        $counters['expenses_day'] = 0;
    
        $expenses = Expense::whereBetween('created_at', [
            $date->format('Y-m-d 00:00:00'),
            $date->format('Y-m-d 23:59:59')
        ])->get();
    
        foreach ($expenses as $expense) {
            $counters['expenses_day'] += $expense->balance;
        }
    
        $counters['totalToExpenses'] = (float) $counters['balanceTotal'] - (float) $counters['expenses_day'];
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
}
