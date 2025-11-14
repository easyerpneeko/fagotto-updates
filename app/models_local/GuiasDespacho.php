<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

// Helpers
use App\Helpers\CurrentApp;

class GuiasDespacho extends Model
{
    protected $connection = 'mysql_local';
    protected $fillable = [
      'total',
      'cancel',
      'trash',
      'client',
      'user',
      'guia_folio',
      'despacho',
      'translado',
      'comment'
    ];
    public static function createGuiasDespacho($request){
      $itemToSave = [];
      $keysAllow = [
        'total',
        'user',
        'translado',
        'despacho',
        'comment',
      ];
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $keysAllow[] = 'client';
      }
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')) {
        $keysAllow[] = 'gananciaTotal';
      }
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
        $keysAllow[] = 'guia_folio';
      }

      foreach ($keysAllow as $key){
        if (isset($request[$key])) $itemToSave[$key] = $request[$key];
        else $itemToSave[$key] = null;
      }
      
      return GuiasDespacho::create($itemToSave);
    }
}
