<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Config;

// Helpers
use App\Helpers\MPage;
use App\Helpers\ConectionDB;
use App\Helpers\CurrentApp;
use Auth;

// Models
use App\Product;
use App\Aplication;
use App\ProductChange;

// Requests
use App\Http\Requests\pedidos\StorePedidos;

class ProductController extends Controller
{
  public function store(StorePedidos $request){
    $_request = $request->all();

    if(isset($_request['app_id']) && $_request['app_id']){
      $app = Aplication::find($_request['app_id']);
      if(!$app) return response()->json('La aplicación seleccionada no existe', 404);
    }

    $product = Product::create($_request);
    return response()->json($product,200);
  }

  public function update(Request $request, $id){
    $_request = $request->all();

    $product = Product::find($id);
    if(!$product) return response()->json('Este producto no existe', 404);

    if(isset($_request['app_id']) && $_request['app_id']){
      $app = Aplication::find($_request['app_id']);
      if(!$app) return response()->json('La aplicación seleccionada no existe', 404);
    }

    $product->update($_request);
    return response()->json($product,200);
  }

  public function index(Request $request){
    $pquery = DB::table('products');

    // Filtrado y ordenadores
    $ordersAndFilters = ['created_at','id','app_id'];
    // Buscar por id de aplicacion
    if ($request->input('searchOfApp')) {
      $app_id = $request->input('searchOfApp');
      $pquery->whereRaw("admin_feeds.app_id like '%$app_id%'");
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request, 10,'','products',$ordersAndFilters,$ordersAndFilters, true, null);

    return response()->json($Paginated,200);
  }

  public function remove($id){
    $product = Product::find($id);
    if(!$product) return response()->json('Este producto no existe', 404);

    if(!$product->delete()) return response()->json('Error del servidor',500);
    return response()->json('Producto eliminadp exitosamente',200);
  }

  /**
   * Activar/Desactivar un producto (toggle active status)
   * Cuando se desactiva, también se marca como eliminado (trash = 1)
   */
  public function toggleActive($id, Request $request){
    $product = Product::find($id);
    if(!$product) return response()->json([
        'success' => false,
        'message' => 'Este producto no existe'
    ], 404);

    // Obtener el nuevo estado desde la request o invertir el actual
    $newStatus = $request->has('active') ? (int)$request->input('active') : ($product->active ? 0 : 1);
    
    $product->active = $newStatus;
    
    // Cuando se desactiva (active = 0), también marcar como eliminado (trash = 1)
    // Cuando se activa (active = 1), restaurar del trash (trash = 0)
    $product->trash = $newStatus ? 0 : 1;
    
    if(!$product->save()) {
        return response()->json([
            'success' => false,
            'message' => 'Error del servidor al actualizar el producto'
        ], 500);
    }

    $actionMessage = $newStatus ? 'Producto activado y restaurado exitosamente' : 'Producto desactivado y eliminado exitosamente';

    return response()->json([
        'success' => true,
        'message' => $actionMessage,
        'data' => [
            'id' => $product->id,
            'name' => $product->name,
            'active' => $product->active,
            'trash' => $product->trash
        ]
    ], 200);
  }

  /**
   * Mover/Quitar producto de papelera (toggle trash status)  
   */
  public function toggleTrash($id, Request $request){
    $product = Product::find($id);
    if(!$product) return response()->json([
        'success' => false,
        'message' => 'Este producto no existe'
    ], 404);

    // Obtener el nuevo estado desde la request o invertir el actual
    $newStatus = $request->has('trash') ? (int)$request->input('trash') : ($product->trash ? 0 : 1);
    
    $product->trash = $newStatus;
    
    if(!$product->save()) {
        return response()->json([
            'success' => false,
            'message' => 'Error del servidor al actualizar el producto'
        ], 500);
    }

    return response()->json([
        'success' => true,
        'message' => $newStatus ? 'Producto movido a papelera exitosamente' : 'Producto restaurado de papelera exitosamente',
        'data' => [
            'id' => $product->id,
            'name' => $product->name,
            'trash' => $product->trash
        ]
    ], 200);
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

  public function updateStock(Request $request, $id)
  {
      $request->validate([
          'stock_added' => 'required|integer', // Permite positivos y negativos
      ]);

      $product = Product::find($id);

      if (!$product) {
          return response()->json(['message' => 'Producto no encontrado'], 404);
      }

      $oldStock = $product->stock;
      $product->stock += $request->stock_added; // Sumar o restar
      
      // Evitar stock negativo
      if ($product->stock < 0) {
          return response()->json(['message' => 'El stock no puede ser negativo'], 400);
      }
      
      $product->save();

      $userId = null;
      $userName = null;

      if (Auth::check()) {
          $user = Auth::user();
          if ($user) {
              $userId = $user->id;
              $userName = $user->username;
          }
      }

      ProductChange::create([
          'user_id' => $userId,
          'user_name' => $userName,
          'field_name' => 'stock',
          'old_value' => $oldStock,
          'new_value' => $product->stock, // Nuevo stock después de la suma
      ]);

      return response()->json(['message' => 'Stock actualizado correctamente'], 200);
  }

  public function getStockHistory()
  {
      $changes = ProductChange::where('field_name', 'stock')
          ->orderBy('created_at', 'desc')
          ->limit(100)
          ->get();

      return response()->json($changes, 200);
  }

  /**
   * Obtener precios centralizados desde pedidofinal_precios
   * GET /api/local/precios-centralizados
   */
  public function getPreciosCentralizados()
  {
      try {
          // Conectar a la base de datos maestra (easyerp)
          $preciosCentralizados = DB::connection('easyerp_master')
              ->table('pedidofinal_precios')
              ->where('activo', 1)
              ->orderBy('categoria')
              ->orderBy('producto')
              ->get();

          return response()->json([
              'success' => true,
              'data' => $preciosCentralizados
          ], 200);

      } catch (\Exception $e) {
          return response()->json([
              'success' => false,
              'message' => 'Error al obtener precios centralizados: ' . $e->getMessage()
          ], 500);
      }
  }

  /**
   * Crear nuevo producto en pedidofinal_precios
   * POST /api/local/precios-centralizados
   */
  public function createPrecioCentralizado(Request $request)
  {
      return response()->json([
          'success' => false,
          'message' => 'No tienes permisos para ejecutar esta acción ❤️'
      ], 403);
  }

  /**
   * Actualizar producto existente en pedidofinal_precios
   * PUT /api/local/precios-centralizados/{id}
   */
  public function updatePrecioCentralizado(Request $request, $id)
  {
      return response()->json([
          'success' => false,
          'message' => 'No tienes permisos para ejecutar esta acción ❤️'
      ], 403);
  }

  /**
   * Eliminar producto de pedidofinal_precios
   * DELETE /api/local/precios-centralizados/{id}
   */
  public function deletePrecioCentralizado($id)
  {
      return response()->json([
          'success' => false,
          'message' => 'No tienes permisos para ejecutar esta acción ❤️'
      ], 403);
  }
}
