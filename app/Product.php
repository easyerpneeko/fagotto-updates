<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CurrentApp;

class Product extends Model
{
  // protected $connection = 'mysql_local';

  protected $fillable = [
    'image',
    'name',
    'barcode',
    'min_stock',
    'stock',
    'min_quantity',
    'price',
    'mayor',
    'active',
    'trash',
    'user',
    'cecina',
    'prices',
    'category',
    'ganancia',
    'ganancia_mayor',
    'compra',
    'product_variable_category',
    'promo_active',
    'promo_price',
    'promo_combo_price',
    'is_combo',
    'discount_percentage',
  ];

  public static function newProduct($request){
    $keysAllow = [
      'name',
      'price',
      'mayor',
      "compra",
      'image'
    ];

    if (CurrentApp::ConfStr('modulos.productos.submodulos.categorias')) {
      $keysAllow[] = 'category';
    }

    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_barcode')) {
      $keysAllow[] = 'barcode';
    }
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')) {
      $keysAllow[] = 'stock';
    }
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')) {
      $keysAllow[] = 'min_quantity';
    }
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cecina')) {
      $keysAllow[] = 'cecina';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'promo_active';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'promo_price';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'product_variable_category';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'promo_combo_price';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'is_combo';
    }
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_precio_variante')) {
      $keysAllow[] = 'prices';
    }
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'ganancia';
    }
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'ganancia_mayor';
    }
    
    // Siempre permitir discount_percentage (descuento para pedidos)
    $keysAllow[] = 'discount_percentage';

    $itemToSave = [];
    if (isset($request['image'])) {
      $uploadedFile = $request['image'];
      $filename = time().$uploadedFile->getClientOriginalName();

      $result = Storage::disk('local')->putFileAs(
        'uploads/products',
        $uploadedFile,
        $filename
      );
      // asignando la ruta de la imagen que se guardara en la base de datos
      $request['image'] = $result;
    }else {
      $request['image'] = 'productDefault';
    }
    foreach ($keysAllow as $key){
      if (isset($request[$key])) {
        // Convertir is_combo a entero explícitamente
        if ($key === 'is_combo') {
          $itemToSave[$key] = (int) $request[$key];
        } else {
          $itemToSave[$key] = $request[$key];
        }
      } else {
        $itemToSave[$key] = null;
      }
    }
    return Product::create($itemToSave);
  }

  public static function EditProduct($request, $id){

    $Product = Product::find($id);
    if(!$Product) return response()->json('Producto no encontrado',404);

    if ($request['image'] && !is_string($request['image'])) {
      $uploadedFile = $request['image'];
      $filename = time().$uploadedFile->getClientOriginalName();

      $result = Storage::disk('local')->putFileAs(
        'uploads/products',
        $uploadedFile,
        $filename
      );

      // asignando la ruta de la imagen que se guardara en la base de datos
      $request['image'] = $result;
    }

    $keysAllow = [
      'image',
      'name',
      'compra',
      'price',
      'mayor'
    ];

    if(CurrentApp::ConfStr('modulos.productos.submodulos.categorias')){
      $keysAllow[] = 'category';
    }
    if(CurrentApp::ConfStr('modulos.productos.ajustes.permitir_barcode')){
      $keysAllow[] = 'barcode';
    }
    if(CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')){
      $keysAllow[] = 'stock';
    }
    if(CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')){
      $keysAllow[] = 'min_quantity';
    }
    if(CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cecina')){
      $keysAllow[] = 'cecina';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'promo_active';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'promo_price';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'product_variable_category';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'promo_combo_price';
    }
    if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
      $keysAllow[] = 'is_combo';
    }
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_precio_variante')) {
      $keysAllow[] = 'prices';
    }
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'ganancia';
    }
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'ganancia_mayor';
    }

    // Siempre permitir discount_percentage (descuento para pedidos)
    $keysAllow[] = 'discount_percentage';

    foreach ($keysAllow as $key){
      if (isset($request[$key])){
        // Convertir is_combo a entero explícitamente
        if ($key === 'is_combo') {
          $Product->{$key} = (int) $request[$key];
          \Log::info('🔧 Setting is_combo to: ' . $Product->{$key});
        } else {
          $Product->{$key} = $request[$key];
        }
      }
    }

    \Log::info('💾 Antes de guardar producto:', [
      'id' => $Product->id,
      'name' => $Product->name,
      'is_combo' => $Product->is_combo
    ]);

    if(!$Product->save()) return response()->json('Database error',500);

    \Log::info('✅ Producto guardado exitosamente:', [
      'id' => $Product->id,
      'is_combo' => $Product->is_combo
    ]);

    return response()->json('Producto editado exitosamente',200);

  }



}