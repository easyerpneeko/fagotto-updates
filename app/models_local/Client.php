<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $connection = 'mysql_local';
    protected $fillable = [
      'name',
      'lastname',
      'rut',
      'city',
      'comuna',
      'razon_social',
      'phone',
      'direction',
      'giro',
      'email'
    ];

    public static function createClient($request){
      $keysAllow = [
        'name',
        'lastname',
        'rut',
        'city',
        'comuna',
        'razon_social',
        'phone',
        'direction',
        'giro',
        'email'
      ];
      $itemToSave = [];
      foreach ($keysAllow as $key){
        if (isset($request[$key])) $itemToSave[$key] = $request[$key];
        else $itemToSave[$key] = null;
      }

      return Client::create($itemToSave);
    }

    public static function editClient($request, $id, $notResponse = false){
      $Client = Client::find($id);
      if (!$Client) {
        if ($notResponse) {
          throw new \Exception("Client Not Found", 1);
        }else{
          return response()->json('Cliente no encontrado', 404);
        }
      }

      $keysAllow = [
        'name',
        'lastname',
        'rut',
        'city',
        'comuna',
        'razon_social',
        'phone',
        'direction',
        'giro',
        'email'
      ];
      foreach ($keysAllow as $key)
          if (isset($request[$key]))
              $Client->{$key} = $request[$key];

      if(!$Client->save()) return false;
      return $Client;
    }
}
