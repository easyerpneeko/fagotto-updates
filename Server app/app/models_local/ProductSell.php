<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

// Helpers
use App\Helpers\CurrentApp;
use App\models_local\Sell;

// use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSell extends Model
{
    // use SoftDeletes;

    protected $connection = 'mysql_local';
    protected $table = "products_sells";
    protected $fillable = [
      'price',
      'quantity',
      'unitary_price',
      'gananciaTotal',
      'product',
      'sell',
    ];

    public static function createProductSell($request){
      $keysAllow = [
        'price',
        'quantity',
        'unitary_price',
        'product',
        'sell',
      ];
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $keysAllow[] = 'gananciaTotal';
      }
      $itemToSave = [];

      foreach ($keysAllow as $key){
        if (isset($request[$key])) $itemToSave[$key] = $request[$key];
        else $itemToSave[$key] = null;
      }

      return ProductSell::create($itemToSave);
    }

    public function sell()
    {
      return $this->hasOne(Sell::class, 'sell');
    }
    
    public function product()
    {
      return $this->hasOne(Product::class);
    }
}
