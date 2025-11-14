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
use App\AdminFeed;
use App\Aplication;

// Requests
use App\Http\Requests\feeds\StoreRequest;

class FeedController extends Controller
{
  public function store(StoreRequest $request){
    $_request = $request->all();

    if(isset($_request['app_id']) && $_request['app_id']){
      $app = Aplication::find($_request['app_id']);
      if(!$app) return response()->json('La aplicación seleccionada no existe', 404);
    }

    $feed = AdminFeed::create($_request);
    return response()->json($feed,200);
  }

  public function update(Request $request, $id){
    $_request = $request->all();

    $feed = AdminFeed::find($id);
    if(!$feed) return response()->json('Esta noticia no existe', 404);

    if(isset($_request['app_id']) && $_request['app_id']){
      $app = Aplication::find($_request['app_id']);
      if(!$app) return response()->json('La aplicación seleccionada no existe', 404);
    }

    $feed->update($_request);
    return response()->json($feed,200);
  }

  public function index(Request $request){
    $pquery = DB::table('admin_feeds');

    // Filtrado y ordenadores
    $ordersAndFilters = ['created_at','id','app_id'];
    // Buscar por id de aplicacion
    if ($request->input('searchOfApp')) {
      $app_id = $request->input('searchOfApp');
      $pquery->whereRaw("admin_feeds.app_id like '%$app_id%'");
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request, 10,'','admin_feeds',$ordersAndFilters,$ordersAndFilters, true, null);

    return response()->json($Paginated,200);
  }

  public function remove($id){
    $feed = AdminFeed::find($id);
    if(!$feed) return response()->json('Esta noticia no existe', 404);

    if(!$feed->delete()) return response()->json('Error del servidor',500);
    return response()->json('Noticia eliminada exitosamente',200);
  }

  public function getMyFeeds(Request $request){
    $myApp = CurrentApp::App();
    $pquery = DB::table('admin_feeds')->where('app_id', $myApp->id)->orWhereNull('app_id')->orderBy('id', 'DESC')->get();
    return response()->json($pquery,200);
  }
}
