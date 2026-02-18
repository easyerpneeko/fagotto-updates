<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Helpers\ConectionDB;
use Illuminate\Http\Request;
use App\Helpers\CurrentApp;
use App\Helpers\MPage;

use App\models_local\Product;
use App\models_local\HistoryProduct;
use App\models_local\ComboStep;

use Dompdf\Dompdf;
use Response;
use Config;
use File;


class ProductsController extends Controller
{
  //JC
  protected function newProduct(Request $request){
    $data = $request->all();
    
    // Convertir 'null' a null real 
    foreach ($data as $key => $value) { 
      if ($value === 'null') { 
        $data[$key] = null; 
      }
    }

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
          if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia') && $prices[0]->ganancia){
            $data['ganancia'] = $prices[0]->ganancia;
          }
        }
      }
    }else {
      $validaciones['price'] = 'required|max:15';
      $validaciones['mayor'] = 'max:15';
      $validator = Validator::make($data, $validaciones);
      if($validator->fails()) return response()->json($validator->errors(), 400);
    }

    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $validaciones['ganancia'] = 'max:15';
    }
        
    $validator = Validator::make($data, $validaciones);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);

    // dd($data);
    $products = Product::newProduct($data);
    if(!$products) return response()->json("Database Error",500);
    
    // Guardar pasos del combo si existe
    if (isset($data['is_combo']) && $data['is_combo'] == 1 && isset($data['combo_steps'])) {
      $comboSteps = json_decode($data['combo_steps'], true);
      if (is_array($comboSteps)) {
        ComboStep::saveSteps($products->id, $comboSteps);
      }
    }
    
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
      $validaciones['mayor'] = 'max:15';
      $validator = Validator::make($data, $validaciones);
      if($validator->fails()) return response()->json($validator->errors(), 400);
    }

    $product = Product::find($id);

    if (!is_string($data['image'])) {
      if ($data['image'] != $product->image){
        $validator = Validator::make($request->all(), [
          'image' => 'image'
        ]);
        if($validator->fails()) return response()->json($validator->errors(), 400);
      }
    }

    $this->transactionHistory($data,$product,'updated');
    
    // Log para debug
    \Log::info('🎯 editProduct - Datos recibidos:', [
      'is_combo' => $data['is_combo'] ?? 'no enviado',
      'combo_steps' => isset($data['combo_steps']) ? 'sí enviado' : 'no enviado'
    ]);
    
    $result = Product::EditProduct($data, $id);
    
    // Guardar pasos del combo si existe
    if (isset($data['is_combo']) && $data['is_combo'] == 1 && isset($data['combo_steps'])) {
      \Log::info('✅ Guardando pasos del combo para producto ' . $id);
      $comboSteps = json_decode($data['combo_steps'], true);
      if (is_array($comboSteps)) {
        ComboStep::saveSteps($id, $comboSteps);
        \Log::info('✅ Pasos guardados: ' . count($comboSteps));
      }
    } else if (isset($data['is_combo']) && $data['is_combo'] == 0) {
      \Log::info('❌ Eliminando pasos del combo para producto ' . $id);
      // Si desactivan el combo, eliminar los pasos
      ComboStep::where('product_id', $id)->delete();
    }
    
    return $result;
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
      
      // Agregar pasos del combo si es un combo
      if ($key->is_combo == 1) {
        $key->combo_steps = ComboStep::getStepsByProduct($key->id);
      }
    }
    return $pquery;
  }
  
  protected function getProductsOfSell2()
    {
        $database2 = Config::get('database.connections.mysql.database');
       // $pquery = DB::table($database2 . '.products')
       //     ->where('products.trash', 0)->get();
        $pquery = Product::with('ingredients')
            ->where('trash', 0)
            ->get();

        if (!$pquery) {
            return response()->json('Error del servidor', 500);
        }

        $products = [];
        foreach ($pquery as $product) {
           // $productModel = Product::find($product->id); // Obtener el modelo del producto para acceder a las relaciones
            $productArray = $product->toArray(); // Convertir el objeto stdClass a array

            $tieneStock = true;
            // Verificar el stock de cada ingrediente del producto
            if ( $product && is_iterable($product->ingredients)) { // Agregamos esta condición
                foreach ($product->ingredients as $ingredient) {
                    // Obtener la cantidad necesaria del ingrediente para este producto GLOBAL
                    $productIngredient = DB::table($database2 . '.global_ingredients')
                        ->where('ingredient_id', $ingredient->id)
                        ->first();

                    if ($productIngredient) {
                        $cantidadNecesaria = $productIngredient->quantity_grams;
                        // Si el stock del ingrediente es menor que la cantidad necesaria, el producto no tiene stock
                        if ($ingredient->stock_quantity * 1000 < $cantidadNecesaria) {
                            $tieneStock = false;
                            break; // No es necesario seguir verificando ingredientes
                        }
                    }
                    // Verificar si el ingrediente tiene stock 0 y la cantidad necesaria es mayor que 0
                    if ($ingredient->stock_quantity == 0 && $productIngredient && $productIngredient->quantity_grams > 0) {
                        $tieneStock = false;
                        break;
                    }
                }
            }

            $productArray['tiene_stock'] = $tieneStock;

            if ($product->prices) {
                $productArray['prices'] = json_decode($product->prices);
            }
            
            // 🎯 Agregar pasos del combo si es un combo guiado
            if (isset($product->is_combo) && $product->is_combo == 1) {
                \Log::info("🎯 [getProductsOfSell2] Product {$product->id} ({$product->name}) is_combo = {$product->is_combo}, loading steps...");
                $productArray['combo_steps'] = ComboStep::getStepsByProduct($product->id);
                \Log::info("✅ [getProductsOfSell2] Loaded " . count($productArray['combo_steps']) . " steps for product {$product->id}");
            }
            
            $products[] = $productArray;
        }
        return response()->json($products, 222);
    }

  
    protected function getProductsOfFagotto(){
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.products_fagotto')
    ->get();

    if (!$pquery) return response()->json('Error del servidor',500);
    
    return $pquery;
  }

  public function getProducts(Request $request){
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
      $pquery->select('products.*', 'categories.name as category_name', 'categories.status as category_status');
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,12,'','products',$orders,$filters, false, null);
    foreach ($Paginated['items'] as $key) {
      if ($key->prices) {
        $key->prices = json_decode($key->prices);
      }
      
      // Agregar pasos del combo si es un combo
      if (isset($key->is_combo) && $key->is_combo == 1) {
        \Log::info("🎯 Product {$key->id} ({$key->name}) is_combo = {$key->is_combo}, loading steps...");
        $key->combo_steps = ComboStep::getStepsByProduct($key->id);
        \Log::info("✅ Loaded " . count($key->combo_steps) . " steps for product {$key->id}");
      }
    }
    return response()->json($Paginated);
  }

  public function getProductsExtended(Request $request){
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
      $pquery->select('products.*', 'categories.name as category_name', 'categories.status as category_status');
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,20,'','products',$orders,$filters, false, null);
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
    $product->barcode = null;
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

  public function transactionHistory($ProdNew = null, $ProdOld = null, $type = null) {
    $user = auth()->user();
    $transaction = "";
    $field_afected = "";
    $old_value = "";
    $new_value = "";

    if($type == 'created') {
      $transaction .= "Usuario {$user->fullname} el dia {$ProdNew->created_at} registro un producto {$ProdNew->name} su precio es {$ProdNew->price}, ";
      $transaction .= "el precio de ganacia {$ProdNew->ganancia}, ";
      $transaction .= "el precio de compra {$ProdNew->compra}, ";
      $transaction .= "el stock es {$ProdNew->stock}. ";
      $transaction .= "el categoria es {$ProdNew->category}. ";
    }

    if($type == 'updated') {
      $ProdNew['price'] = $ProdNew['price'] ?? $ProdOld->price ?? 0;
      $ProdNew['ganancia'] = $ProdNew['ganancia'] ?? $ProdOld->ganancia ?? 0;
      $ProdNew['compra'] = $ProdNew['compra'] ?? $ProdOld->compra ?? 0;
      $ProdNew['stock'] = $ProdNew['stock'] ?? $ProdOld->stock ?? 0;
      $ProdNew['name'] = $ProdNew['name'] ?? $ProdOld->name ?? null;
      $transaction .= "Usuario {$user->fullname} el dia {$ProdOld->updated_at} modifico el producto ({$ProdOld->name}) nombre anterior {$ProdOld->name} por, el nuevo nombre es {$ProdNew['name']} ";
      $transaction .= "el precio anterior {$ProdOld->price} ahora nuevo valor que se edito es {$ProdNew['price']}, ";
      $transaction .= "el precio de ganancia anterior {$ProdOld->ganancia} ahora nuevo valor que se edito es {$ProdNew['ganancia']}, ";
      $transaction .= "el precio de compra anterior {$ProdOld->compra} ahora nuevo valor que se edito es {$ProdNew['compra']}, ";
      $transaction .= "el stock de producto anterior {$ProdOld->stock} ahora nuevo valor que se edito es {$ProdNew['stock']}. ";
      $transaction .= "categoryNuevo {$ProdNew['category']}";
    }
    
    if($type == 'updated'){
      if($ProdNew['price'] != $ProdOld->price){  
        $field_afected = 'price';
        $old_value = $ProdOld->price;
        $new_value = $ProdNew['price'];

      }else if($ProdNew['compra'] != $ProdOld->compra){
        $field_afected = 'compra';
        $old_value = $ProdOld->compra;
        $new_value = $ProdNew['compra'];
        
      }else if($ProdNew['name'] != $ProdOld->name){
        $field_afected = 'name';
        $old_value = $ProdOld->name;
        $new_value = $ProdNew['name'];

      }else if($ProdNew['ganancia'] != $ProdOld->ganancia){
        $field_afected = 'ganancia';
        $old_value = $ProdOld->ganancia;
        $new_value = $ProdNew['ganancia'];
        
      }else if($ProdNew['stock'] != $ProdOld->stock){
        $field_afected = 'stock';
        $old_value = $ProdOld->stock;
        $new_value = $ProdNew['stock'];

        $type = 'stock';
      }
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
        'transaction'           => $transaction,
        'type'                  => $type,
        'product_id'            => $ProdOld->id ?? $ProdNew->id,
        'user_id'               => $user->id,
        'category_id'           => $ProdNew->category ?? $ProdOld->category
      ];

      //or $type == 'stock'
      if($type == 'updated' ){

          $data['price_venta_old']=$ProdOld->price;
          $data['price_venta_new']=$ProdNew['price'];
          $data['price_compra_old']=$ProdOld->compra;
          $data['price_compra_new']=$ProdNew['compra'];
          $data['ganancia_old']=$ProdOld->ganancia;
          $data['ganancia_new']=$ProdNew['ganancia'];
          $data['stock_old']=$ProdOld->stock;
          $data['stock_new']=$ProdNew['stock'];
          $data['name_old']=$ProdOld->name;
          $data['name_new']=$ProdNew['name'];
          
          $data['field_afected'] = $field_afected;
          $data['old_value'] = $old_value;
          $data['new_value'] = $new_value;
      }


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
    
    //añadiendo join con tabla users y categories @jesus
    $pquery->join($database2.'.users', 'users.id', 'history_products.user_id');
    $pquery->join($database2.'.categories', 'categories.id', 'history_products.category_id');

    $pquery->select('products.name','users.fullname','categories.name as namecategory','history_products.*');
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
      ->select('products_sells.price as totalPrice','products_sells.quantity','products_sells.unitary_price','products.name','products_sells.description_sii')
      ->get();
      foreach ($productSells as $productSell) {
        // ✅ FIX MERCHISE/CHEAF/COLACIÓN: Usar description_sii si el nombre del producto no existe
        if (empty($productSell->name) && !empty($productSell->description_sii)) {
          $productSell->name = $productSell->description_sii;
        }
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

  public function getProductIngredients($productId) // Recibimos el ID directamente
  {
        try {
            // Buscar el producto manualmente
            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado.'
                ], 404);
            }

            $ingredients = $product->ingredients()->get();

            // Mapear los resultados para darles la estructura que espera el componente Vue
            $formattedIngredients = $ingredients->map(function ($ingredient) {
                return [
                    'ingredient_id' => $ingredient->id,
                    'name' => $ingredient->name, // Nombre del ingrediente
                    // Puedes añadir más campos del ingrediente si los necesitas en el frontend
                    'unit_of_measurement' => $ingredient->unit_of_measurement ?? null,
                    'stock_quantity' => $ingredient->stock_quantity ?? 0,
                    'category_id' => $ingredient->category_id ?? null,
                    // Datos de la tabla pivote
                    'pivot' => [
                        'product_id' => $ingredient->pivot->product_id,
                        'ingredient_id' => $ingredient->pivot->ingredient_id,
                        // 'quantity_grams' => (float) $ingredient->pivot->quantity_grams, // Asegurar que sea float
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedIngredients
            ], 200);

        } catch (\Exception $e) {
            // Es buena práctica loggear el error para depuración
            // Log::error("Error al obtener los ingredientes del producto con ID {$productId}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al obtener los ingredientes del producto.',
                'error' => $e->getMessage() // Útil para depuración, pero evita mostrarlo en producción
            ], 500);
        }
  }

  public function assignIngredientsToProduct(Request $request, $productId)
  {
      // dd($request);

      try {
          // Decodificar la cadena JSON de ingredientes que viene en el FormData
          // Asegúrate de que el frontend envía esto como una cadena JSON en el campo 'ingredients'
          $ingredientsData = json_decode($request->input('ingredients'), true);

          // Si la decodificación falla o no es un array, manejar el error
          if (!is_array($ingredientsData)) {
              return response()->json([
                  'success' => false,
                  'message' => 'Formato de ingredientes inválido. Se esperaba un array JSON.',
              ], 400);
          }

          // Preparar los IDs de ingredientes para la validación y sincronización.
          // Para la sincronización, solo necesitamos los IDs de los ingredientes.
          $ingredientIds = collect($ingredientsData)->pluck('ingredient_id')->all();
          $product_id = $request->input('productId');
          // Validar la solicitud
          $validator = Validator::make([
              'productId' => $product_id, // Usar el ID de la URL
              'ingredient_ids' => $ingredientIds,
          ], [
              // Validar que el producto existe en la base de datos local
              'productId' => 'required|exists:mysql_local.products,id',
              // Validar que los IDs de ingredientes existen en la tabla de ingredientes local
              'ingredient_ids.*' => 'required|integer|exists:mysql_local.ingredients,id',
          ]);

          if ($validator->fails()) {
              return response()->json([
                  'success' => false,
                  'message' => 'Errores de validación.',
                  'errors' => $validator->errors()
              ], 400);
          }

          // Buscar el producto local
          $product = Product::find($product_id);

          // Sincronizar los ingredientes.
          // Dado que la tabla pivote 'product_ingredients' ya no tiene 'quantity_grams',
          // solo sincronizamos los IDs de los ingredientes.
          $product->ingredients()->sync($ingredientIds);

          return response()->json([
              'success' => true,
              'message' => 'Asignaciones de ingredientes guardadas correctamente para el producto local.',
          ], 200);

      } catch (\Exception $e) {
          // Puedes usar Log::error($e->getMessage()); para registrar el error
          return response()->json([
              'success' => false,
              'message' => 'Error interno del servidor al guardar las asignaciones.',
              'error' => $e->getMessage()
          ], 500);
      }
  }
}
