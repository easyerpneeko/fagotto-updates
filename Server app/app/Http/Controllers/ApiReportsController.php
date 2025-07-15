<?php

namespace App\Http\Controllers;

// Laravel
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Helpers
use App\Helpers\ConectionDB;
use App\Helpers\CurrentApp;
use Config;

// Models
use App\Aplication;
use App\models_local\ProductSell;
use App\models_local\Product;

class ApiReportsController extends Controller
{
  public function getReport(Request $request){
    $_request = $request->all();

    $validaciones = [
      'startDate' => 'required',
      'endDate' => 'required'
    ];
    // Validator
    $validator = Validator::make($_request, $validaciones);
    if($validator->fails()) return response()->json($validator->errors(), 400);

    // Conectandoce a la base de datos de la app
    $database2 = Config::get('database.connections.mysql_local.database');

    $pquery = DB::table($database2.'.sells')
                ->where('sells.trash', 0)
                ->where('sells.created_at', '>=', $_request['startDate'])
                ->where('sells.created_at', '<=', $_request['endDate'])
                ->leftJoin($database2.'.users', 'users.id','sells.user')
                ->select('sells.*','users.fullname', 'users.avatar');
                if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) $pquery = $pquery->leftJoin($database2.'.clients', 'sells.client','clients.id')->get();
                else $pquery = $pquery->get();

    if (!$pquery) return response()->json('Error del servidor',500);


    $items = [];
    // Filtrando
    foreach ($pquery as $sell) {
      if($sell->fast_sell){
        if(isset($_request['fastSell'])) {
          $items[] = $sell;
        }
      }else{
        $folio = DB::table($database2.'.folios')->where('sell_id',$sell->id)->first();
        if(!$folio){
          if(isset($_request['noSii'])) {
            $items[] = $sell;
          }
        }else{
          if(isset($_request['factura']) && $folio->type == 'factura'){
            $items[] = $sell;
          }else if(isset($_request['boleta']) && $folio->type == 'boleta'){
            $items[] = $sell;
          }
        }
      }
    }
    $pquery = $items;
    foreach ($pquery as $sell) {
      $products = [];
      $productSells = DB::table($database2.'.products_sells')->where('products_sells.sell',$sell->id)
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
    }
    return response()->json($pquery);
  }

  public function getCounters(Request $request){
    $_request = $request->all();

    $validaciones = [
      'startDate' => 'required',
      'endDate' => 'required'
    ];
    // Validator
    $validator = Validator::make($_request, $validaciones);
    if($validator->fails()) return response()->json($validator->errors(), 400);

    $database2 = Config::get('database.connections.mysql_local.database');

    $ventas = DB::table($database2.'.sells')->where('sells.trash', 0)
                                            ->where('sells.created_at', '>=', $_request['startDate'])
                                            ->where('sells.created_at', '<=', $_request['endDate'])->get();


    if (!$ventas) return response()->json('Error del servidor',500);

    $counters = [
      'balanceTotal' => 0,
      'gananciaTotal' => 0,
      'facturas' => 0,
      'boletas' => 0,
      'fastSells' => 0,
      'noSii' => 0,
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

      if($venta->fast_sell){
        if(isset($_request['fastSell'])) {
          $counters['fastSells'] += $venta->total;
        }else{
          unset($venta);
          $return = true;
        }
      }else{
        $folio = DB::table($database2.'.folios')->where('sell_id',$venta->id)->first();
        if(!$folio){
          if(isset($_request['noSii'])) {
            $counters['noSii'] += $venta->total;
          }else{
            unset($venta);
            $return = true;
          }
        }else{
          if(isset($_request['factura']) && $folio->type == 'factura'){
            $counters['facturas'] += $venta->total;
          }else if(isset($_request['boleta']) && $folio->type == 'boleta'){
            $counters['boletas'] += $venta->total;
          }else{
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
          $nP = $i;

          if (!isset($products[$nP])) {
            $products[$nP] = [
              'id' => $IDsDeProductos[$i],
              'nombre' => $producto['name'],
              'cantidad' => 0,
              'total' => 0
            ];
            if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
              $products[$nP]['ganancia'] = 0;
            }
          }

          foreach ($products_sells as $productSell) {
            if($productSell->product == $products[$nP]['id']){

              //Se suman
              $products[$nP]['cantidad'] += (float) $productSell->quantity;
              $products[$nP]['total'] += $productSell->price;
              if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
                $products[$nP]['ganancia'] += (float) $productSell->gananciaTotal;
              }
            }
          }
        }
      }
    }

    foreach ($products as $key => $value) {
      $multi_data_quantity = (float) $value['cantidad'] *100;
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales')) {
        $porcentajeQuantity = $multi_data_quantity / (float) $counters['quantityTotal'];
      }else{
        $porcentajeQuantity = $multi_data_quantity / (float) $quantityTotal;
      }


      $multi_data_balance = (float) $value['total'] *100;
      $porcentajeBalance = $multi_data_balance / (float) $counters['balanceTotal'];

      $products[$key]['procentaje_quantity'] = sprintf('%01.2f', $porcentajeQuantity);
      $products[$key]['procentaje_balance'] = sprintf('%01.2f', $porcentajeBalance);

      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $multi_data_ganancia = (float) $value['ganancia'] *100;
        if((float) $counters['gananciaTotal'] != 0) $porcentajeGanancia = $multi_data_ganancia / (float) $counters['gananciaTotal'];
        else $porcentajeGanancia = 0;

        $products[$key]['procentaje_ganancia'] = sprintf('%01.2f', $porcentajeGanancia);
      }
    }
    return response()->json(['counters' => $counters, 'products' => $products],200);
  }
}
