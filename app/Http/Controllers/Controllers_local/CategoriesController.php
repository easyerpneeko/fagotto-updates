<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\models_local\Category;
use App\models_local\Product;
use App\Helpers\ConectionDB;
use Illuminate\Http\Request;
use App\Helpers\MPage;
use Config;


class CategoriesController extends Controller
{
  protected function newCategory(Request $request){
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|min:1|max:48',
      // 'user' => 'required|exits:users,id',
    ]);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
    $data = $request->all();

    $Category = Category::newCategory($data);
    if(!$Category) return response()->json("Database Error",500);

    return response()->json(['Categoria registrada exitosamente'],200);
  }

  protected function editCategory(Request $request, $id){
    $validator = Validator::make($request->all(), [
      'name' => 'string|min:1|max:48',
    ]);

    if($validator->fails()) return response()->json($validator->errors(), 400);
    $data = $request->all();

    return Category::EditCategory($data, $id);
  }

  public function getCategories(Request $request){
    $pquery = Category::where('trash', 0)->get();
    if (!$pquery) return response()->json('Error del servidor',500);
    return response()->json($pquery);
  }
  /*
    id
    nombre
    precio
    stock

    /local/category/products/{id}
  */
  protected function getProducts($id) {
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.products')
    ->select('products.id', 'products.name', 'products.stock','products.price')
    ->where('products.trash', 0)
    ->where('category', $id)->get();



    return response()->json($pquery);
  }

  protected function trashCategory($id){
    $category = Category::find($id);
    if(!$category) return response()->json('Categoria no encontrada',404);

    if($category->trash == 1) return response()->json('La categoria ya se encuentra eliminada',400);

    $category->trash = 1;
    if(!$category->save()) return response()->json('Error del servidor',500);

    $database2 = Config::get('database.connections.mysql_local.database');
    $products = DB::table($database2.'.products')
    ->where('products.category', $id)->get();

    foreach ($products as $product) {
      $productData = Product::find($product->id);
      if ($productData) {
        $productData->category = null;
        $productData->save();
      }
    }

    return response()->json('Categoria eliminada exitosamente',200);
  }

  protected function hideCategory($id){
    //Busar categoria
    $category = Category::find($id);
    if(!$category) return response()->json('Categoria no encontrada',404);
    //verificar categoria
    if($category->status == 1) return response()->json('La categoria ya se encuentra oculta',400);
    //Establcer categoria en 1 = oculta
    $category->status = 1;
    if(!$category->save()) return response()->json('Error del servidor',500);

    return response()->json('Categoria oculta exitosamente',200);
  }

  protected function showCategory($id){
    //Busar categoria
    $category = Category::find($id);
    if(!$category) return response()->json('Categoria no encontrada',404);
    //verificar categoria
    if($category->status == 0) return response()->json('La categoria ya se encuentra visible',400);
    //Establcer categoria en 0 = visible
    $category->status = 0;
    if(!$category->save()) return response()->json('Error del servidor',500);

    return response()->json('Categoria se ha activado exitosamente',200);
  }

}
