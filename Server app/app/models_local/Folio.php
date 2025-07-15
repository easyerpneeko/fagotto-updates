<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class Folio extends Model
{
  protected $connection = 'mysql_local';

  protected $fillable = [
    'folio',
    'folioAsign',
    'sell_id',
    'type',
    'pdf_url',
    'xml_id',
    'xml_string',
    'response_json',
    'guia_id'
  ];

  public static function createFolio($request){
    $keysAllow = [
      'folio',
      'folioAsign',
      'sell_id',
      'type',
      'pdf_url',
      'xml_id',
      'xml_string',
      'response_json',
      'guia_id'
    ];
    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }

    return Folio::create($itemToSave);
  }
}
