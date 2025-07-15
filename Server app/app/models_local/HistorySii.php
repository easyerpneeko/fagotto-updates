<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class HistorySii extends Model
{
  protected $connection = 'mysql_local';

  protected $fillable = [
    'message',
    'xml_string',
    'sell_id',
    'guia_id',
    'typeFolio',
    'folio',
    'pdf_url',
    'response_json'
  ];

  public static function createHistory($request, $sell = null, $folio = null, $xml_string = null){
    $keysAllow = [
      'message',
      'xml_string',
      'sell_id',
      'typeFolio',
      'folio',
      'guia_id',
      'pdf_url',
      'response_json'
    ];
    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }

    // Guardamos el string del XML
    // TODO: Hacer que guarde el xml_string aunque no se haya creado el folio.
    if ($xml_string) {
      $itemToSave['xml_string'] = $xml_string;
    }

    return HistorySii::create($itemToSave);
  }
}
