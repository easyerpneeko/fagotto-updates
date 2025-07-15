<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
  protected $connection = 'mysql';
  protected $fillable = [
    'username',
    'rut',
    'sexo',
    'description',
    'direction',
    'email',
  ];
  public static function createClient($request){
    $keysAllow = [
      'username',
      'rut',
      'sexo',
      'description',
      'direction',
      'email',
    ];
    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }

    return Client::create($itemToSave);
  }

  public static function editClient($request, $id){
    $Client = Client::find($id);
    if (!$Client) return response()->json('Cliente no encontrado', 404);

    $keysAllow = [
      'username',
      'rut',
      'sexo',
      'description',
      'direction',
      'email',
    ];

    foreach ($keysAllow as $key)
        if (isset($request[$key]))
            $Client->{$key} = $request[$key];

    return $Client->save();
  }
}
