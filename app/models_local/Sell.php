<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

// Helpers
use App\Helpers\CurrentApp;
use App\models_local\ProductSell;

class Sell extends Model
{
    protected $connection = 'mysql_local';
    protected $fillable = [
      'total',
      'cancel',
      'trash',
      'client',
      'user',
      'user_trash',
      'gananciaTotal',
      'fast_sell',
      'sell_folio',
      'typeSell',
      'siiState',
      'other_type',
      'special_payment_info',
      'uber_payment_info',
      'rappi_payment_info',
      'pedidos_ya_payment_info',
      'tip',
      'discount',
      'paymode',
      'fecha_emision',
      'fecha_vencimiento',
      'nro_transaccion',
      'documento_referencia',
      'order_id',
      //'created_at'
    ];
    public static function createSell($request){
      $itemToSave = [];
      $keysAllow = [
        'total',
        'user',
        'fast_sell',
        'tip',
        'discount',
        'paymode',
        'order_id',
        'fecha_emision',
        'fecha_vencimiento',
        'nro_transaccion',
        'documento_referencia'
        //'created_at'
      ];
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $keysAllow[] = 'client';
      }
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $keysAllow[] = 'gananciaTotal';
      }
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
        $keysAllow[] = 'sell_folio';
        $keysAllow[] = 'typeSell';
        if(isset($request['other_type'])) $keysAllow[] = 'other_type';
        if(isset($request['special_payment_info'])) $keysAllow[] = 'special_payment_info';
        if(isset($request['uber_payment_info'])) $keysAllow[] = 'uber_payment_info';
        if(isset($request['rappi_payment_info'])) $keysAllow[] = 'rappi_payment_info';
        if(isset($request['pedidos_ya_payment_info'])) $keysAllow[] = 'pedidos_ya_payment_info';
      }

      foreach ($keysAllow as $key){
        if (isset($request[$key])) $itemToSave[$key] = $request[$key];
        else $itemToSave[$key] = null;
      }
      
      return Sell::create($itemToSave);
    }
    
    public function productsSells()
    {
        return $this->belongsTo(ProductSell::class);
    }

    public function devolution()
    {
      return $this->hasMany(Devolution::class);
    }
}
