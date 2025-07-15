<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CurrentApp;
use App\Helpers\SiiHelper;
use App\Factura;

class FacturaController extends Controller
{
  public function uploadXML(Request $request, $isATesting = false) {

    $xml_string = $request->input('xmlfolio');

    $xml = simplexml_load_string($xml_string);
    $json = json_encode($xml);
    $jsonArray = json_decode($json,TRUE);

    $codeTypeDocument = $jsonArray['CAF']['DA']['TD'];

    $typeDocument = null;
    if ($codeTypeDocument == '39' || $codeTypeDocument === 39){
      $typeDocument = 'boleta';
    }

    if ($codeTypeDocument == '33' || $codeTypeDocument === 33){
      $typeDocument = 'factura';
    }

    $dd = $jsonArray['CAF']['DA']['RNG']['D'];
    $hh = $jsonArray['CAF']['DA']['RNG']['H'];
    //Modificar los envs
    $app = CurrentApp::App();
    $envs = $app->getEnvs();

    //Aqui modifico las envs correspondientes
    foreach ($envs as $envKey => $envData){
      if ($envKey == 'xml_code_of_folio'){
        $envs[$envKey]['value'] = $json;
      }
      if ($envKey == 'xml_code_of_folio_boleta' && $typeDocument == 'boleta') {
        $envs[$envKey]['value'] = $json;
      }
      if ($envKey == 'xml_code_of_folio_factura' && $typeDocument == 'factura') {
        $envs[$envKey]['value'] = $json;
      }
    }

    if (!$app->saveEnvs($envs)) return response()->json("Error al modificar envs",500);
    // $cf = ConfigManager::edit('xml_code_of_folio',$json,$request);
    // if ($typeDocument == 'boleta') {
    //   ConfigManager::edit('xml_code_of_folio_boleta',$json,$request);
    // }else if ($typeDocument == 'factura') {
    //   ConfigManager::edit('xml_code_of_folio_factura',$json,$request);
    // }

    $cargaDeFolio = SiiHelper::cargarFolio($xml_string, $isATesting);
    //

    if (!$cargaDeFolio[0]) return redirect()->route('config')->with('error',$cargaDeFolio[1]);

    for ($i=$dd; $i <= $hh; $i++) {
      if (!Factura::where('folio','=',$i)->first()) {
        $factura = new Factura();
        $factura->orden = null;
        $factura->folio = $i;
        $factura->folioAsign = null;
        $factura->url = null;
        $factura->typefolio = $typeDocument;
        $factura->save();
      }else{
        return response()->json('Uno de los folios ya fue usado');
      }
    }

    return response()->json('Archivo Cargado con exito',200);
  }
}
