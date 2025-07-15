<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\models_local\Product;
use App\Helpers\ConectionDB;
use Illuminate\Http\Request;
use App\Helpers\CurrentApp;
use App\Helpers\MPage;
use App\models_local\HistoryProduct;
use Dompdf\Dompdf;
use Response;
use Config;
use File;


class ProductsController extends Controller
{
  //JC
  protected function newProduct(Request $request){
    $data = $request->all();

    $validaciones = [
      'name' => 'required|string|min:3|max:48',
    ];

    //return response()->json(["message"=>"Database Error","request"=>$data],500); //pruba de envio de parametros (JC)

    if (isset($data['image'])) {
      $validaciones['image'] = 'image';
    }
    //Submodulos
    if (CurrentApp::ConfStr('modulos.productos.submodulos.categorias')) {
      $validaciones['category'] = 'exists:mysql_local.categories,id';
    }

    //Ajustes
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_barcode')) {
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cecina') && (isset($data['cecina']) && $data['cecina'])){
        $validaciones['barcode'] = 'nullable|unique:mysql_local.products,barcode';
      }else {
        $validaciones['barcode'] = 'required|unique:mysql_local.products,barcode';
      }
    }

    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock') && CurrentApp::HavePermission('productos_modificar_stock')) {
      $validaciones['stock'] = 'required|integer';
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.cantidades_float')) {
        $validaciones['stock'] = 'required|numeric';
      }
    }

    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')) {
      $validaciones['min_quantity'] = 'integer';
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.cantidades_float')) {
        $validaciones['min_quantity'] = 'numeric';
      }
    }

    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_precio_variante')){
      $validaciones['prices'] = 'json';
      $validator = Validator::make($data, $validaciones);
      if($validator->fails()) return response()->json($validator->errors(), 400);
      if (isset($data['prices'])){
        $prices = json_decode($data['prices']);
        if (count($prices)) {
          $data['price'] = $prices[0]->precio;
          if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia') && $prices[0]->ganancia){
            $data['ganancia'] = $prices[0]->ganancia;
          }
        }
      }
    }else {
      $validaciones['price'] = 'required|max:15';
      $validator = Validator::make($data, $validaciones);
      if($validator->fails()) return response()->json($validator->errors(), 400);
    }

    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $validaciones['ganancia'] = 'required|max:15';
    }

    $validator = Validator::make($data, $validaciones);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);

    // if (!CurrentApp::ConfStr('productos_modificar_stock')){
    //   unset($data['stock']);
    // }
    $products = Product::newProduct($data);
    if(!$products) return response()->json("Database Error",500);
    $this->transactionHistory($products,null, 'created');

    return response()->json(['Producto registrado exitosamente'],200);
  }

  protected function editProduct(Request $request, $id){
    $data = $request->all();

    $validaciones = [
      'name' => 'string|min:3|max:48',
    ];

    //Submodulos
    if (CurrentApp::ConfStr('modulos.productos.submodulos.categorias')) {
      $validaciones['category'] = 'exists:mysql_local.categories,id';
    }

    //Ajustes
    // if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_barcode')) {
    //   $validaciones['barcode'] = 'unique:mysql_local.products';
    // }
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')) {
      $validaciones['stock'] = 'integer';
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.cantidades_float')) {
        $validaciones['stock'] = 'numeric';
      }
    }
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')) {
      $validaciones['min_quantity'] = 'integer';
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.cantidades_float')) {
        $validaciones['min_quantity'] = 'numeric';
      }
    }
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_precio_variante')){
      $validaciones['prices'] = 'json';
      $validator = Validator::make($data, $validaciones);
      if($validator->fails()) return response()->json($validator->errors(), 400);
      if (isset($data['prices'])){
        $prices = json_decode($data['prices']);
        if (count($prices)) {
          $data['price'] = $prices[0]->precio;
        }
      }
    }else {
      $validaciones['price'] = 'max:15';
      $validator = Validator::make($data, $validaciones);
      if($validator->fails()) return response()->json($validator->errors(), 400);
    }

    if (!is_string($data['image'])) {
      $productImage = Product::find($id);
      if ($data['image'] != $productImage->image){
        $validator = Validator::make($request->all(), [
          'image' => 'image'
        ]);
        if($validator->fails()) return response()->json($validator->errors(), 400);
      }
    }
    $this->transactionHistory($data,Product::find($id),'updated');
    return Product::EditProduct($data, $id);
  }

  protected function getProductsOfSell(){
    $database2 = Config::get('database.connections.mysql_local.database');
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

  protected function getProducts(Request $request){
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.products')
    ->where('products.trash', 0);
    if (!$pquery) return response()->json('Error del servidor',500);

    //Ordenamientos
    $orders = ['id','name'];
    //Filtrados
    $filters = ['name', 'category'];

    if (CurrentApp::ConfStr('modulos.productos.submodulos.categorias')){
      if ($request->input('categoryOfProduct')) {
        $pquery->where("products.category", $request->input('categoryOfProduct'));
      }

    }
    if ($request->has('orderBy_name')) {
      $pquery->orderBy('products.name', $request->input('orderBy_name'));
    }
    
    if ($request->input('nameOfProduct')) {

      $name = $request->input('nameOfProduct');

      $opcionales = '';
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_barcode')) {
        $opcionales .= " OR products.barcode like '%$name%'";
      }

      $pquery->whereRaw("products.name like '%$name%' ".$opcionales);
    }

    if (CurrentApp::ConfStr('modulos.productos.submodulos.categorias')) {

      $pquery->leftJoin($database2.'.categories', 'categories.id','products.category');
      $pquery->select('products.*','categories.name as category_name');
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,12,'','products',$orders,$filters, false, null);
    foreach ($Paginated['items'] as $key) {
      if ($key->prices) {
        $key->prices = json_decode($key->prices);
      }
    }
    return response()->json($Paginated);
  }

  protected function trashProduct($id){
    $product = Product::find($id);
    if(!$product) return response()->json('Producto no encontrado',404);

    if($product->trash == 1) return response()->json('El producto ya se encuentra eliminado',400);

    $product->trash = 1;
    if(!$product->save()) return response()->json('Error del servidor',500);
    $this->transactionHistory(null,$product, 'deleted');
    return response()->json('Producto eliminado exitosamente',200);
  }

  // Import Products
  public function import(Request $request) {
    Excel::import(new ProductsImport(), $request->file('products'));

    return response()->json('Productos procesados exitosamente', 200);
  }

  // Export Products
  public function export(){
    // Nombre del archivo
    $filename = 'inventario_'.time().'.xlsx';

    // Guardando archivo en la storage
    $excel = Excel::store(new ProductsExport(), 'excel/'.$filename, 'public');

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

  protected function changeStockProduct(Request $request){
    $product = Product::find($request->id);
    if(!$product) return response()->json('Producto no encontrado',404);

    if($product->trash == 1) return response()->json('El producto ya se encuentra eliminado',400);
    $data = ['stock' => $request->stock];
    return $this->transactionHistory($data, $product, 'stock');
  }

  public function transactionHistory($ProdNew = null, $ProdOld = null, $type) {
    $user = auth()->user();
    $transaction = "";
    if($type == 'created') {
      $transaction .= "Usuario {$user->fullname} el dia {$ProdNew->created_at} registro un producto {$ProdNew->name} su precio es {$ProdNew->price}, ";
      $transaction .= "el precio de ganacia {$ProdNew->ganancia}, ";
      $transaction .= "el precio de compra {$ProdNew->compra}, ";
      $transaction .= "el stock es {$ProdNew->stock}. ";
    }

    if($type == 'updated') {
      $ProdNew['price'] = $ProdNew['price'] ?? $ProdOld->price ?? 0;
      $ProdNew['ganancia'] = $ProdNew['ganancia'] ?? $ProdOld->ganancia ?? 0;
      $ProdNew['compra'] = $ProdNew['compra'] ?? $ProdOld->compra ?? 0;
      $ProdNew['stock'] = $ProdNew['stock'] ?? $ProdOld->stock ?? 0;
      $ProdNew['name'] = $ProdNew['name'] ?? $ProdOld->name ?? null;
      $transaction .= "Usuario {$user->fullname} el dia {$ProdOld->updated_at} modifico el producto ({$ProdOld->name}) precio anterior {$ProdOld->price} por ({$ProdNew['name']}), el nuevo precio es {$ProdNew['price']} ";
      $transaction .= "el precio de ganancia anterior {$ProdOld->ganancia} ahora nuevo valor que se edito es {$ProdNew['ganancia']}, ";
      $transaction .= "el precio de compra anterior {$ProdOld->compra} ahora nuevo valor que se edito es {$ProdNew['compra']}, ";
      $transaction .= "el stock de producto anterior {$ProdOld->stock} ahora nuevo valor que se edito es {$ProdNew['stock']}. ";
    }

    if($type == 'deleted') {
      $transaction .= "Usuario {$user->fullname} el dia ".now()." elimino un producto {$ProdOld->name} su precio es {$ProdOld->price}, ";
      $transaction .= "el precio de ganacia {$ProdOld->ganancia}, ";
      $transaction .= "el precio de compra {$ProdOld->compra}, ";
      $transaction .= "el stock es {$ProdOld->stock}. ";
    }

    if($type == 'stock') {
      $ProdNew['stock'] = $ProdNew['stock'] ?? 0;
      $transaction .= "Usuario {$user->fullname} el dia ".now()." actualizo el stock {$ProdOld->stock} para agregar una nueva cantidad ({$ProdNew['stock']}) al producto {$ProdOld->name} su precio es {$ProdOld->price}, ";
      $transaction .= "el precio de ganacia {$ProdOld->ganancia}, ";
      $transaction .= "el precio de compra {$ProdOld->compra}, ";
    }

    if($type == 'stock' or $type == 'deleted' or $type == 'updated' or $type == 'created') {
      $data = [
        'transaction'   => $transaction,
        'type'          => $type,
        'product_id'    => $ProdOld->id ?? $ProdNew->id,
        'user_id'       => $user->id,
      ];

      if ($type == 'stock' ) {
        $product = Product::find($ProdOld->id);
        $product->stock = ($product->stock > 0) ? $product->stock+$ProdNew['stock'] : $ProdNew['stock'];
        if(!$product->save()) return response()->json('Error del servidor',500);
        HistoryProduct::create($data);
        return response()->json('Stock de producto actualizado exitosamente',200);
      }
      HistoryProduct::create($data);
      return true;
    }
    return false;
  }

  protected function getHistoryProducts(Request $request) {
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.history_products');
    if (!$pquery) return response()->json('Error del servidor',500);
    //Ordenamientos
    $orders = ['id'];
    //Filtrados
    $filters = [];

    if ($request->has('product_id')) {
      $pquery->where('history_products.product_id', $request->input('product_id'));
    }
    if ($request->has('startDate') && $request->has('endDate')) {
      $pquery->where('history_products.created_at', '>=', $request->startDate);
      $pquery->where('history_products.created_at', '<=', $request->endDate);
    }
    
    $pquery->join($database2.'.products', 'products.id','history_products.product_id');
    $pquery->select('products.name','history_products.*');
    $pquery->orderBy('history_products.product_id', 'desc');
    $pquery->orderBy('history_products.id', 'desc');

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,10,'','history_products',$orders,$filters, false, null);
    return response()->json($Paginated);
  }

  public function getSellFollows(Request $request) {
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.sells');
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')){
      $pquery->select('sells.id as sell_id', 'sells.total', 'sells.created_at as fecha', 'clients.*');
    } else {
      $pquery->select('sells.id as sell_id', 'sells.total', 'sells.created_at as fecha');
    }
    $pquery->where('sells.trash', 0);
    $pquery->where('sells.typeSell', 'factura');
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')){
      $pquery->leftJoin($database2.'.clients', 'clients.id','sells.client');
      if ($request->input('searchOfClient')) {
        $rut = $request->input('searchOfClient');
        $pquery->whereRaw("clients.rut like '%$rut%'");
      }
    }
    if (!$pquery) return response()->json('Error del servidor',500);

    //Ordenamientos
    $orders = ['id','total','created_at'];
    // //Filtrados
    $filters = [];

    

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,10,'','sells',$orders,$filters, false, null);
    foreach ($Paginated['items'] as $sell) {
      
      //Añadiendo productos individuales
      $products = [];
      $productSells = DB::table($database2.'.products_sells')
      ->where('products_sells.sell',$sell->sell_id)
      ->leftJoin($database2.'.products','products.id','products_sells.product')
      ->select('products_sells.price as totalPrice','products_sells.quantity','products_sells.unitary_price','products.name')
      ->get();
      foreach ($productSells as $productSell) {
        $products[] = $productSell;
      }
      $sell->products = $products;
    }
    return response()->json($Paginated);
  }

  // Export Products
  // public function export_old() {
  //   return Excel::download(new ProductsExport(), 'inventario.xlsx');
  // }
}
