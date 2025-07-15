<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use Auth;
class Category extends Model
{
  protected $connection = 'mysql_local';

  protected $fillable = [
    'name',
    'user',
    'trash'
  ];
  public static function newCategory($request){
    $request['user'] = Auth::user()->id;
    $keysAllow = [
      'name',
      'user'
    ];
    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }
    return Category::create($itemToSave);
  }

  public static function EditCategory($request, $id){
    $Category = Category::find($id);
    if(!$Category) return response()->json('Categoria no encontrada',404);

    $keysAllow = [
      'name'
    ];
    foreach ($keysAllow as $key){
      if (isset($request[$key])){
        $Category->{$key} = $request[$key];
      }
    }

    if(!$Category->save()) return response()->json('Database error',500);

    return response()->json('Categoria editada exitosamente',200);
  }

  public function products() { 
  
    return $this->hasMany(Product::class); 
  
  }

}
