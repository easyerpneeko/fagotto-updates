<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\ConectionDB;

class XmlCargados extends Model
{
  protected $connection = 'mysql_local';

  protected $fillable = [
    'typeNumber',
    'type',
    'folio_inicial',
    'folio_final',
    'jsonXML',
    'contentXML'
  ];

  public static function createXML($request, $app){
    $keysAllow = [
      'typeNumber',
      'type',
      'folio_inicial',
      'folio_final',
      'jsonXML',
      'contentXML'
    ];
    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }

    $config = new ConectionDB($app);
    $config->set_database($app->database);
    return XmlCargados::create($itemToSave);
  }
}
