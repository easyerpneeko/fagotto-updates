<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataBase extends Model
{
  protected $connection = 'mysql';
  protected $fillable = [
    'name',
    'username',
    'password'
  ];
  
  public function applications()
  {
        return $this->hasMany('App\Aplication', 'database_id');
  }

  public static function createDataBase($request){
    $keysAllow = [
      'name',
      'username',
      'password'
    ];
    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }

    return DataBase::create($itemToSave);
  }
}
