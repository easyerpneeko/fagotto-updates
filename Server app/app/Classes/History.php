<?php namespace App\Classes;

use App\models_local\HistorySii;
/**/
class History {

  //Generar xml para la factura
  public static function createHistory($response, $sell, $folio) {
    $data = array(
      'sell_id' => $sell->id,
      'typeFolio' => $folio->type,
      'folio' => $folio->folio,
      'response_json' => $folio->response_json
    );
    if($response[0]){
      $data['message'] = 'Procesamiento de folio exitoso';
      $data['xml_string'] = $response[1]['xml_string'];
      if(isset($response[1]['url'])) $data['pdf_url'] = $response[1]['url'];
    }else{
      $data['message'] = $response[1];
    }

    HistorySii::createHistory($data);
  }

  public static function createGuiaHistory($response, $sell, $folio) {
    $data = array(
      'guia_id' => $sell->id,
      'typeFolio' => $folio->type,
      'folio' => $folio->folio,
      'response_json' => $folio->response_json
    );
    if($response[0]){
      $data['message'] = 'Procesamiento de folio exitoso';
      $data['xml_string'] = $response[1]['xml_string'];
      if(isset($response[1]['url'])) $data['pdf_url'] = $response[1]['url'];
    }else{
      $data['message'] = $response[1];
    }

    HistorySii::createHistory($data);
  }

}
