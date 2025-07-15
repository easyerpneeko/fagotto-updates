<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CurrentApp;

class Product extends Model
{
  protected $connection = 'mysql_local';

  protected $fillable = [
    'image',
    'name',
    'barcode',
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
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_precio_variante')) {
      $keysAllow[] = 'prices';
    }
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'ganancia';
    }
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'ganancia_mayor';
    }

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
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }
    // Verificar si promo_price es null y asignarle 0.00 si es necesario 
    $itemToSave['promo_price'] = $itemToSave['promo_price'] ?? 0.00;
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
    if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_precio_variante')) {
      $keysAllow[] = 'prices';
    }
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'ganancia';
    }
    if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
      $keysAllow[] = 'ganancia_mayor';
    }


    foreach ($keysAllow as $key){
      if (isset($request[$key])){
        $Product->{$key} = $request[$key];
      }
    }

    if(!$Product->save()) return response()->json('Database error',500);

    return response()->json('Producto editado exitosamente',200);

  }

  public function devolution()
  {
    return $this->hasMany(Devolution::class);
  }

  public function productCategory() {
    return $this->belongsTo(Category::class, 'category'); // Especifica el nombre de la columna
  }

  public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients', 'product_id', 'ingredient_id');
    }
}
