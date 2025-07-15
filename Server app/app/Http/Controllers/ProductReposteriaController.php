<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Config;

// Helpers
use App\Helpers\MPage;
use App\Helpers\ConectionDB;
use App\Helpers\CurrentApp;

// Models
use App\ProductReposteria;
use App\Aplication;

// Requests
use App\Http\Requests\pedidos\StorePedidos;

class ProductReposteriaController extends Controller
{
  public function store(StorePedidos $request){
    $_request = $request->all();

    if(isset($_request['app_id']) && $_request['app_id']){
      $app = Aplication::find($_request['app_id']);
      if(!$app) return response()->json('La aplicación seleccionada no existe', 404);
    }

    $product = ProductReposteria::create($_request);
    return response()->json($product,200);
  }

  public function update(Request $request, $id){
    $_request = $request->all();

    $product = ProductReposteria::find($id);
    if(!$product) return response()->json('Este producto no existe', 404);

    if(isset($_request['app_id']) && $_request['app_id']){
      $app = Aplication::find($_request['app_id']);
      if(!$app) return response()->json('La aplicación seleccionada no existe', 404);
    }

    $product->update($_request);
    return response()->json($product,200);
  }

  public function index(Request $request){
    $pquery = DB::table('products_reposteria');

    // Filtrado y ordenadores
    $ordersAndFilters = ['created_at','id','app_id'];
    // Buscar por id de aplicacion
    if ($request->input('searchOfApp')) {
      $app_id = $request->input('searchOfApp');
      $pquery->whereRaw("admin_feeds.app_id like '%$app_id%'");
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request, 10,'','products_reposteria',$ordersAndFilters,$ordersAndFilters, true, null);

    return response()->json($Paginated,200);
  }

  public function remove($id){
    $product = ProductReposteria::find($id);
    if(!$product) return response()->json('Este producto no existe', 404);

    if(!$product->delete()) return response()->json('Error del servidor',500);
    return response()->json('Producto eliminadp exitosamente',200);
  }

  public function getMyFeeds(Request $request){
    $myApp = CurrentApp::App();
    $pquery = DB::table('admin_feeds')->where('app_id', $myApp->id)->orWhereNull('app_id')->orderBy('id', 'DESC')->get();
    return response()->json($pquery,200);
  }

  protected function getProductsOfSell(){
    $database2 = Config::get('database.connections.mysql.database');
    $pquery = DB::table($database2.'.products')
    ->where('products.trash', 0)->get();

    if (!$pquery) return response()->json('Error del servidor',500);
    foreach ($pquery as $key) {
      if ($key->prices) {
        $key->prices = json_decode($key->prices);
      }
    }
    return $pquery;
  }
}
