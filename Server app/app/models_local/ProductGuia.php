<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

// Helpers
use App\Helpers\CurrentApp;

class ProductGuia extends Model
{
    protected $connection = 'mysql_local';
    protected $table = "products_guia_despacho";
    protected $fillable = [
      'price',
      'quantity',
      'unitary_price',
      'gananciaTotal',
      'product',
      'guia',
    ];

    public static function createProductGuia($request){
      $keysAllow = [
        'price',
        'quantity',
        'unitary_price',
        'product',
        'guia',
      ];
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $keysAllow[] = 'gananciaTotal';
      }
      $itemToSave = [];

      foreach ($keysAllow as $key){
        if (isset($request[$key])) $itemToSave[$key] = $request[$key];
        else $itemToSave[$key] = null;
      }

      return ProductGuia::create($itemToSave);
    }
}
