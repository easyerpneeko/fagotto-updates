<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Config;

// Modelos
use App\models_local\Folio;
use App\models_local\XmlCargados;
use App\models_local\Sell;
use App\models_local\GuiasDespacho;
use App\models_local\Client;
use App\models_local\Requests;
use App\Aplication;

// Helpers y classes
use App\Classes\ServicesSII;
use App\Classes\History;
use App\Helpers\ConectionDB;
use App\Helpers\CurrentApp;


class SIIController extends Controller
{

  /*
      Que hace esta funcion?
      Respuesta: Obtiene el PDF generado
      por appoctava que es guardado en AWS
      (Amazon Web Services)
      2 horas y 2 dia
      Gracias yo del pasado. ;)
  */
  public static function getPutsContent($url) {

    return Self::CurlToGetPutContents($url);
    exit();

    $arrContextOptions = array(
        "ssl"=>array(
          'cafile'            =>  '/path/to/cacert.pem',
          "verify_peer"       =>  false,
          "verify_peer_name"  =>  false
        ),
    );

    //var_dump($url);exit();

    $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
    return $response;

  }

  public static function CurlToGetPutContents($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
  }

  public function readXML(Request $request, $id) {
    $_request = $request->all();

    $xml_string = $_request['xml_string'];

    // Transformando el xml
    $jsonArray = ServicesSII::xmlToArray($xml_string);

    // Cargando el folio
    $cargaDeFolio = ServicesSII::cargarFolio($xml_string, $id);
    if (!$cargaDeFolio[0]) return response()->json($cargaDeFolio[1], 400);

    // Obteniendo tipo de documento
    $typeDocument = ServicesSII::getTypeFile($jsonArray['CAF']['DA']['TD']);

    $dd = $jsonArray['CAF']['DA']['RNG']['D'];
    $hh = $jsonArray['CAF']['DA']['RNG']['H'];

    $dataXML = array(
      'typeNumber' => $jsonArray['CAF']['DA']['TD'],
      'type' => $typeDocument,
      'folio_inicial' => $dd,
      'folio_final' => $hh,
      'jsonXML' => json_encode($jsonArray),
      'contentXML' => $xml_string
    );

    // Obteniendo app
    $app = Aplication::find($id);

    // Creando xml
    $xml = XmlCargados::createXML($dataXML, $app);
    if(!$xml) return response()->json('Error del servidor',500);

    for ($folio=$dd; $folio <= $hh; $folio++) {
      if (!Folio::where('folio',$folio)->where('type', $typeDocument)->first()) {
        // Creando folio
        $query = Folio::createFolio(array('folio' => $folio, 'type' => $typeDocument, 'xml_id' => $xml->id));
        if(!$query) response()->json('Error del servidor',500);

      }else return response()->json('El folio '.$folio.' ya se encuentra registrado',404);
    }

    return response()->json('Folios cargados exitosamente',200);
  }

  // Procesar factura de pedido
  public static function processFacturaPedido(Request $request, $pedido, $client, $app){
      $_request = $request->all();

      if(!$pedido){
        // if(isset($_request['modal'])) return response()->json('Pedido no encontrado',400);
        return [
          'success' => false,
          'content' => 'Venta no encontrada',
          'code' => 404,
        ];
      }

      // $client = Client::find($clientId);

      if (!$client){
        // if(isset($_request['modal'])) return response()->json('El cliente es obligatorio para procesar una factura', 400);
        return [
          'success' => false,
          'content' => 'El cliente es obligatorio para procesar una factura',
          'code' => 400,
        ];
      }

      $pedido->rut = $client->rut;
      $pedido->city = $client->city;
      $pedido->comuna = $client->comuna;
      $pedido->razon_social = $client->razon_social;
      $pedido->direction = $client->direction;
      $pedido->giro = $client->giro;

      if($pedido->print == 1 && $pedido->pdf_url =! null){
        // Verificando que el pedido no este asignada a un folio cualquiera
        $b64Doc = chunk_split(base64_encode(Self::getPutsContent($pedido->pdf_url)));
        // if(isset($_request['modal'])) return response()->json($b64Doc, 200);
          return [
            'success' => true,
            'content' => $b64Doc,
            'code' => 200,
        ];
      }    

      // Verificando existencia de un folio
      $folio = Folio::whereNull('sell_id')->where('trash', 0)->where('type','factura')->first();
      if (!$folio){
        // if(isset($_request['modal'])) return response()->json('No hay folios de factura disponibles',400);
        return [
          'success' => false,
          'content' => 'No hay folios de factura disponibles',
          'code' => 400,
        ];
      }
      // dd($folio);
      // Procesando DTE de factura
      $folioData = ServicesSII::processPedidoFactura_con_Data(
        $pedido, 
        $folio, 
        ['forma'=>$request['forma'], 
        'comment'=>$request['comment'],
        'fecha_emision'=>$request['fecha_emision'],
        'fecha_vencimiento'=>$request['fecha_vencimiento'],
        'nro_transaccion'=>$request['nro_transaccion'],
        'documento_referencia'=>$request['documento_referencia'],
        'observacion'=>$request['observacion']]
      );

      if($folioData[1] == 'TVAL'){
        $folio->trash = 1;
        $folio->save();
        $folioData[1] = "El folio a utilizar ya estaba asignado, por favor intentelo nuevamente desde el detalle de la venta";
      }
       // Añadiendo movimiento a la base de datos del Historial
    History::createHistory($folioData, $pedido, $folio);
    if (!$folioData[0]){
      // TODO: Hacer que guarde el xml_string aunque no se haya creado el folio.
      // if(isset($_request['modal'])) return response()->json($folioData[1], 400);
      return [
        'success' => false,
        'content' => $folioData[1],
        'code' => 400,
      ];
    }

    // Asignando pdf de la venta realizada al folio y otros datos
    if(!isset($folioData[1]['no_send_data'])){
      $pedido->updated_at = date('Y-m-d H:i:s');
      unset($pedido->rut);
      unset($pedido->city);
      unset($pedido->comuna);
      unset($pedido->direction);
      unset($pedido->giro);
      unset($pedido->razon_social);
      $pedido->print = 1;
      $pedido->pdf_url = $folioData[1]['url'];

      // Convertimos el objeto stdClass a un array asociativo 
      $pedidoArray = (array) $pedido;

      // Guarda los cambios en la base de datos
      $resultadoSave = DB::table($app->database->name.'.requests')->where('id', $pedido->id)->update($pedidoArray);

      $folio->xml_string = $folioData[1]['xml_string'];
      $folio->sell_id = $pedido->id;
      $folio->pdf_url = $folioData[1]['url'];
      $folio->folioAsign = (integer) $folioData[1]['folioAsign'];
      $folio->save();

      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($folio->pdf_url)));
      // if(isset($_request['modal'])) return response()->json($b64Doc, 200);
        return [
          'success' => true,
          'content' => $b64Doc,
          'code' => 200,
        ];
    }

    // if(isset($_request['modal'])) return response()->json($folioData[1]['xml_string'], 200);

    return [
      'success' => true,
      'content' => $folioData[1]['xml_string'],
      'code' => 200,
    ];
  }
  // Procesar factura
  public static function processFactura(Request $request, $sellId){
    $_request = $request->all();

    $sell = Sell::find($sellId);
    if(!$sell){
      if(isset($_request['modal'])) return response()->json('Venta no encontrada',400);
      return [
        'success' => false,
        'content' => 'Venta no encontrada',
        'code' => 404,
      ];
    }

    $client = Client::find($sell->client);
    if (!$client){
      if(isset($_request['modal'])) return response()->json('El cliente es obligatorio para procesar una factura', 400);
      return [
        'success' => false,
        'content' => 'El cliente es obligatorio para procesar una factura',
        'code' => 400,
      ];
    }

    $sell->rut = $client->rut;
    $sell->city = $client->city;
    $sell->comuna = $client->comuna;
    $sell->razon_social = $client->razon_social;
    $sell->direction = $client->direction;
    $sell->giro = $client->giro;
    //$sell->observacion = $_request['observacion'];


    // Verificando que la venta no este asignada a un folio cualquiera
    $verify = Folio::where('sell_id', $sell->id)->where('type', 'factura')->first();
    if ($verify && $verify->pdf_url){
      //$b64Doc = chunk_split(base64_encode(file_get_contents($verify->pdf_url)));
      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($verify->pdf_url)));
      if(isset($_request['modal'])) return response()->json($b64Doc, 200);
      return [
        'success' => true,
        'content' => $b64Doc,
        'code' => 200,
      ];
    }

    // Verificando existencia de un folio
    $folio = Folio::whereNull('sell_id')->where('trash', 0)->where('type','factura')->first();
    if (!$folio){
      if(isset($_request['modal'])) return response()->json('No hay folios de factura disponibles',400);
      return [
        'success' => false,
        'content' => 'No hay folios de factura disponibles',
        'code' => 400,
      ];
    }

    // Procesando DTE de factura
    $folioData = ServicesSII::processFactura_con_Data($sell, $folio, ['forma'=>$request['forma'], 
    'comment'=>$request['comment'],
    'fecha_emision'=>$request['fecha_emision'],
    'fecha_vencimiento'=>$request['fecha_vencimiento'],
    'nro_transaccion'=>$request['nro_transaccion'],
    'documento_referencia'=>$request['documento_referencia'],
    'observacion'=>$request['observacion']]);
    if($folioData[1] == 'TVAL'){
      $folio->trash = 1;
      $folio->save();
      $folioData[1] = "El folio a utilizar ya estaba asignado, por favor intentelo nuevamente desde el detalle de la venta";
    }
    // Añadiendo movimiento a la base de datos del Historial
    History::createHistory($folioData, $sell, $folio);
    if (!$folioData[0]){
      // TODO: Hacer que guarde el xml_string aunque no se haya creado el folio.
      if(isset($_request['modal'])) return response()->json($folioData[1], 400);
      return [
        'success' => false,
        'content' => $folioData[1],
        'code' => 400,
      ];
    }

    // Asignando pdf de la venta realizada al folio y otros datos
    if(!isset($folioData[1]['no_send_data'])){
      $sell->updated_at = date('Y-m-d H:i:s');
      $sell->typeSell = $folio->type;
      $sell->sell_folio = $folio->folio;
      unset($sell->rut);
      unset($sell->city);
      unset($sell->comuna);
      unset($sell->direction);
      unset($sell->giro);
      unset($sell->razon_social);
      $sell->save();

      $folio->xml_string = $folioData[1]['xml_string'];
      $folio->sell_id = $sell->id;
      $folio->pdf_url = $folioData[1]['url'];
      $folio->folioAsign = (integer) $folioData[1]['folioAsign'];
      $folio->save();

      //$b64Doc = chunk_split(base64_encode(file_get_contents($folio->pdf_url)));
      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($folio->pdf_url)));
      if(isset($_request['modal'])) return response()->json($b64Doc, 200);
      return [
        'success' => true,
        'content' => $b64Doc,
        'code' => 200,
      ];
    }

    if(isset($_request['modal'])) return response()->json($folioData[1]['xml_string'], 200);

    return [
      'success' => true,
      'content' => $folioData[1]['xml_string'],
      'code' => 200,
    ];
  }

  // Procesar guia de despacho
  public static function processGuiaDespacho(Request $request, $guiaId){
    $_request = $request->all();

    $sell = GuiasDespacho::find($guiaId);
    if(!$sell){
      if(isset($_request['modal'])) return response()->json('Venta no encontrada',400);
      return [
        'success' => false,
        'content' => 'Venta no encontrada',
        'code' => 404,
      ];
    }

    $client = Client::find($sell->client);
    if (!$client){
      if(isset($_request['modal'])) return response()->json('El cliente es obligatorio para procesar una factura', 400);
      return [
        'success' => false,
        'content' => 'El cliente es obligatorio para procesar una factura',
        'code' => 400,
      ];
    }

    $sell->rut = $client->rut;
    $sell->city = $client->city;
    $sell->comuna = $client->comuna;
    $sell->razon_social = $client->razon_social;
    $sell->direction = $client->direction;
    $sell->giro = $client->giro;
    //$sell->observacion = $_request['observacion'];


    // Verificando que la venta no este asignada a un folio cualquiera
    $verify = Folio::where('guia_id', $sell->id)->where('type','guia_de_despacho')->first();
    if ($verify && $verify->pdf_url){
      //$b64Doc = chunk_split(base64_encode(file_get_contents($verify->pdf_url)));
      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($verify->pdf_url)));
      if(isset($_request['modal'])) return response()->json($b64Doc, 200);
      return [
        'success' => true,
        'content' => $b64Doc,
        'code' => 200,
      ];
    }

    // Verificando existencia de un folio
    $folio = Folio::whereNull('guia_id')->where('trash', 0)->where('type','guia_de_despacho')->first();
    if (!$folio){
      if(isset($_request['modal'])) return response()->json('No hay folios de guia despacho disponibles',400);
      return [
        'success' => false,
        'content' => 'No hay folios de guia despacho disponibles',
        'code' => 400,
      ];
    }

    // Procesando DTE de factura
    $folioData = ServicesSII::processGuiaDespacho($sell, $folio);
    if($folioData[1] == 'TVAL'){
      $folio->trash = 1;
      $folio->save();
      $folioData[1] = "El folio a utilizar ya estaba asignado, por favor intentelo nuevamente desde el detalle de guia de despacho";
    }
    // Añadiendo movimiento a la base de datos del Historial
    History::createGuiaHistory($folioData, $sell, $folio);
    if (!$folioData[0]){
      // TODO: Hacer que guarde el xml_string aunque no se haya creado el folio.
      if(isset($_request['modal'])) return response()->json($folioData[1], 400);
      return [
        'success' => false,
        'content' => $folioData[1],
        'code' => 400,
      ];
    }

    // Asignando pdf de la guia de despacho realizada al folio y otros datos
    if(!isset($folioData[1]['no_send_data'])){
      $sell->updated_at = date('Y-m-d H:i:s');
      //$sell->typeSell = $folio->type;
      $sell->guia_folio = $folio->folio;
      unset($sell->rut);
      unset($sell->city);
      unset($sell->comuna);
      unset($sell->direction);
      unset($sell->giro);
      unset($sell->razon_social);

      $folio->guia_id = $sell->id;

      $sell->save();

      $folio->xml_string = $folioData[1]['xml_string'];
      
      $folio->pdf_url = $folioData[1]['url'];
      $folio->folioAsign = (integer) $folioData[1]['folioAsign'];
      $folio->save();

      //$b64Doc = chunk_split(base64_encode(file_get_contents($folio->pdf_url)));
      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($folio->pdf_url)));
      if(isset($_request['modal'])) return response()->json($b64Doc, 200);
      return [
        'success' => true,
        'content' => $b64Doc,
        'code' => 200,
      ];
    }

    if(isset($_request['modal'])) return response()->json($folioData[1]['xml_string'], 200);

    return [
      'success' => true,
      'content' => $folioData[1]['xml_string'],
      'code' => 200,
    ];
  }

  // Procesar boleta
  public static function processBoleta(Request $request, $sellId){
    $_request = $request->all();

    $sell = Sell::find($sellId);
    if(!$sell){
      if(isset($_request['modal'])) return response()->json('Venta no encontrada',400);
      return [
        'success' => false,
        'content' => 'Venta no encontrada',
        'code' => 404,
      ];
    }

    $client = Client::find($sell->client);
    if (!$client) $sell->razon_social = '';
    else $sell->razon_social = $client->razon_social;


    // Verificando que la venta no este asignada a un folio cualquiera
    $verify = Folio::where('sell_id', $sell->id)->where('type', 'boleta')->first();
    if ($verify && $verify->pdf_url){
      //$b64Doc = chunk_split(base64_encode(file_get_contents($verify->pdf_url)));
      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($verify->pdf_url)));
      if(isset($_request['modal'])) return response()->json($b64Doc, 200);
      return [
        'success' => true,
        'content' => $b64Doc,
        'code' => 200,
      ];
    }

    // Verificando existencia de un folio
    $folio = Folio::whereNull('sell_id')->where('trash', 0)->where('type','boleta')->first();
    if (!$folio){
      if(isset($_request['modal'])) return response()->json('No hay folios de boleta disponibles',400);
      return [
        'success' => false,
        'content' => 'No hay folios de boleta disponibles',
        'code' => 400,
      ];
    }

    // Procesando DTE de boleta
    $folioData = ServicesSII::processBoleta($sell, $folio);
    if($folioData[1] == 'TVAL'){
      $folio->trash = 1;
      $folio->save();
      $folioData[1] = "El folio a utilizar ya estaba asignado, por favor intentelo nuevamente desde el detalle de la venta";
    }
    // Añadiendo movimiento a la base de datos del Historial
    History::createHistory($folioData, $sell, $folio);
    if (!$folioData[0]){
      if(isset($_request['modal'])) return response()->json($folioData[1], 400);
      return [
        'success' => false,
        'content' => $folioData[1],
        'code' => 400,
      ];
    }

    // Asignando pdf de la venta realizada al folio y otros datos
    if(!isset($folioData[1]['no_send_data'])){
      $sell->updated_at = date('Y-m-d H:i:s');
      $sell->typeSell = $folio->type;
      $sell->sell_folio = $folio->folio;
      unset($sell->razon_social);
      $sell->save();
      $folio->xml_string = $folioData[1]['xml_string'];
      $folio->sell_id = $sell->id;
      $folio->pdf_url = $folioData[1]['url'];
      $folio->folioAsign = (integer) $folioData[1]['folioAsign'];
      $folio->save();

      //$b64Doc = chunk_split(base64_encode(file_get_contents($folio->pdf_url)));
      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($folio->pdf_url)));
      if(isset($_request['modal'])) return response()->json($b64Doc, 200);

      return [
        'success' => true,
        'content' => $b64Doc,
        'code' => 200,
      ];
    }

    if(isset($_request['modal'])) return response()->json($folioData[1]['xml_string'], 200);

    return [
        'success' => true,
        'content' => $folioData[1]['xml_string'],
        'code' => 200,
      ];
  }

  // Procesar nota de credito (factura)
  public function notaDeCreditoFactura(Request $request, $sellId){
    
    $request_ = $request->all();

    $sell = Sell::find($sellId);

    if(!$sell) return response()->json('Venta no encontrada',400);
    // Verrificando que no este cancelada
    if($sell->typeSell == 'nota_de_credito') return response()->json('La venta ya se encuentra cancelada',400);

    $client = Client::find($sell->client);
    if (!$client) return response()->json('El cliente es obligatorio para procesar una factura', 400);

    $sell->rut = $client->rut;
    $sell->city = $client->city;
    $sell->comuna = $client->comuna;
    $sell->razon_social = $client->razon_social;
    $sell->direction = $client->direction;
    $sell->giro = $client->giro;

    // Verificando existencia de un folio de nota de credito
    $folio = Folio::whereNull('sell_id')->where('trash', 0)->where('type','nota_de_credito')->first();
    if (!$folio) return response()->json('No hay folios de nota de credito disponibles',400);

    // Verificando el folio de la venta
    $facturaExists = Folio::where('sell_id', $sell->id)->where('type','factura')->first();
    if (!$facturaExists) return response()->json('No existe un folio asignado a esta venta.', 400);

    $folioToCancel  = $facturaExists->folioAsign;
    // $dateToCancel   = date('Y-m-d',strtotime($facturaExists->created_at));
    $dateToCancel = date('Y-m-d',strtotime($request_['cancelDate']));

    $folioData = ServicesSII::cancelarFactura($sell,$folio,$folioToCancel,$dateToCancel);
    if($folioData[1] == 'TVAL'){
      $folio->trash = 1;
      $folio->save();
      $folioData[1] = "El folio a utilizar ya estaba asignado, por favor intentelo nuevamente";
    }
    // Añadiendo movimiento a la base de datos del Historial
    History::createHistory($folioData, $sell, $folio);
    if (!$folioData[0]) return response()->json($folioData[1],400);

    // Asignando pdf de la venta realizada al folio y otros datos
    if(!isset($folioData[1]['no_send_data'])){
      $sell->updated_at = date('Y-m-d H:i:s');
      $sell->typeSell = $folio->type;
      $sell->sell_folio = $folio->folio;
      unset($sell->rut);
      unset($sell->city);
      unset($sell->comuna);
      unset($sell->direction);
      unset($sell->giro);
      unset($sell->razon_social);
      $sell->save();

      $folio->xml_string = $folioData[1]['xml_string'];
      $folio->sell_id = $sell->id;
      $folio->pdf_url = $folioData[1]['url'];
      $folio->folioAsign = (integer) $folioData[1]['folioAsign'];
      $folio->save();
      //$b64Doc = chunk_split(base64_encode(file_get_contents($folio->pdf_url)));
      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($folio->pdf_url)));
      return response()->json($b64Doc, 200);
    }

    return response()->json($folioData[1]['xml_string'], 200);
  }

  // Procesar nota de credito (boleta)
  public function notaDeCreditoBoleta(Request $request, $sellId){
    $sell = Sell::find($sellId);
    if(!$sell) return response()->json('Venta no encontrada',400);

    // Verrificando que no este cancelada
    if($sell->typeSell == 'nota_de_credito') return response()->json('La venta ya se encuentra cancelada',400);

    // $client = Client::find($sell->client);
    // if (!$client) return response()->json('El cliente es obligatorio para procesar una factura', 400);
    //
    // $sell->rut = $client->rut;
    // $sell->city = $client->city;
    // $sell->comuna = $client->comuna;
    // $sell->razon_social = $client->razon_social;
    // $sell->direction = $client->direction;
    // $sell->giro = $client->giro;

    // Verificando existencia de un folio de nota de credito
    $folio = Folio::whereNull('sell_id')->where('trash', 0)->where('type','nota_de_credito')->first();
    if (!$folio) return response()->json('No hay folios de nota de credito disponibles',400);

    // Verificando el folio de la venta
    $facturaExists = Folio::where('sell_id', $sell->id)->where('type','boleta')->first();
    if (!$facturaExists) return response()->json('No existe un folio asignado esta venta.', 400);

    $folioToCancel  = $facturaExists->folioAsign;
    $dateToCancel   = date('Y-m-d',strtotime($facturaExists->created_at));

    $folioData = ServicesSII::cancelarBoleta($sell,$folio,$folioToCancel,$dateToCancel);
    if($folioData[1] == 'TVAL'){
      $folio->trash = 1;
      $folio->save();
      $folioData[1] = "El folio a utilizar ya estaba asignado, por favor intentelo nuevamente";
    }
    // Añadiendo movimiento a la base de datos del Historial
    History::createHistory($folioData, $sell, $folio);
    if (!$folioData[0]) return response()->json($folioData[1],400);

    // Asignando pdf de la venta realizada al folio y otros datos
    if(!isset($folioData[1]['no_send_data'])){
      $sell->updated_at = date('Y-m-d H:i:s');
      $sell->typeSell = $folio->type;
      $sell->sell_folio = $folio->folio;
      $sell->save();

      $folio->xml_string = $folioData[1]['xml_string'];
      $folio->sell_id = $sell->id;
      $folio->pdf_url = $folioData[1]['url'];
      $folio->folioAsign = (integer) $folioData[1]['folioAsign'];
      $folio->save();

      return response()->json($folio->pdf_url, 200);
    }

    return response()->json($folioData[1]['xml_string'], 200);
  }

  public function consultDTE(Request $request,$sellId){
    $_request = $request->all();
    $sell = Sell::find($sellId);
    if(!$sell){
      return response()->json('Venta no encontrada',400);
    }
    $folio = Folio::where('sell_id', $sell->id)->first();
    if (!$folio) {
      return response()->json('Folio no encontrado',400);
    }

    $consultaDTE = ServicesSII::consultarDTESII($folio,$sell);
    if(isset($_request['cronJob'])) return true;
    return $consultaDTE;
  }

  public function getFolios($id) {
    $app = Aplication::find($id);
    // $app1 = Aplication::where('id',$id)->with('database')->get();
    if(!$app) return response()->json('Aplicacion no encontrada', 404);

    // Establecer base de datos
    $config = new ConectionDB($app);
    $config->set_database($app->database);

    // Buscar folios
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.folios')->where('trash', 0)->whereNull('folioAsign')->get();

    return $pquery;
  }

  public function masiveConsultDTE(Request $request) {

    $aplications = Aplication::all();

    $arrayResponse = [];

    // Recorremos todas las aplicaciones
    foreach ($aplications as $app) {

      // Agregamos al array la aplicacion actual
      /*['mysql_local']->getDatabaseName()*/
      ConectionDB::ChangeDBToApp($app, true);
      $environment_vars = ServicesSII::getEnvs($app->id);

      $arrayResponse[$app->id] = [
        'database' => $app->database_app,
        'config_connection' => ConectionDB::getConnections()/*Config::get('database.connections.mysql_local')*/
      ];

      $sells = Sell::where('siiState','en_espera')->where('sell_folio','<>',null)->get();
      // Recorremos las ventas de estas aplicaciones
      foreach ($sells as $sell) {
        // Agregamos al array la venta actual
        $arrayResponse[$app->id][$sell->id] = [];

        // Obtenemos el folio de esta venta
        $folio = Folio::where('sell_id', $sell->id)->first();
        if ($folio) {
          // Procesamos...
          $consultaDTE = ServicesSII::consultarDTESII($folio,$sell,$app,$environment_vars);
          // Guardamos el folio y su resultado en el arreglo tambien
          $arrayResponse[$app->id][$sell->id] = [
            'folio' => $folio->folio,
            'folioAsign' => $folio->folioAsign,
            'folio_id' => $folio->id,
            'dte_estado' => $consultaDTE
          ];
        }

      }

    }

    return response()->json($arrayResponse,200);

  }

// -------------------------------------PEDIDOS REPOSTERIA----------------------------------------------------

  // Procesar factura de pedido
  public static function processFacturaPedidorReposteria(Request $request, $pedido, $client, $app){
    $_request = $request->all();

    if(!$pedido){
      // if(isset($_request['modal'])) return response()->json('Pedido no encontrado',400);
      return [
        'success' => false,
        'content' => 'Venta no encontrada',
        'code' => 404,
      ];
    }

    // $client = Client::find($clientId);

    if (!$client){
      // if(isset($_request['modal'])) return response()->json('El cliente es obligatorio para procesar una factura', 400);
      return [
        'success' => false,
        'content' => 'El cliente es obligatorio para procesar una factura',
        'code' => 400,
      ];
    }

    $pedido->rut = $client->rut;
    $pedido->city = $client->city;
    $pedido->comuna = $client->comuna;
    $pedido->razon_social = $client->razon_social;
    $pedido->direction = $client->direction;
    $pedido->giro = $client->giro;

    if($pedido->print == 1 && $pedido->pdf_url =! null){
      // Verificando que el pedido no este asignada a un folio cualquiera
      $b64Doc = chunk_split(base64_encode(Self::getPutsContent($pedido->pdf_url)));
      // if(isset($_request['modal'])) return response()->json($b64Doc, 200);
        return [
          'success' => true,
          'content' => $b64Doc,
          'code' => 200,
      ];
    }    

    // Verificando existencia de un folio
    $folio = Folio::whereNull('sell_id')->where('trash', 0)->where('type','factura')->first();
    if (!$folio){
      // if(isset($_request['modal'])) return response()->json('No hay folios de factura disponibles',400);
      return [
        'success' => false,
        'content' => 'No hay folios de factura disponibles',
        'code' => 400,
      ];
    }
    // dd($folio);
    // Procesando DTE de factura
    $folioData = ServicesSII::processPedidoReposteriaFactura_con_Data(
      $pedido, 
      $folio, 
      ['forma'=>$request['forma'], 
      'comment'=>$request['comment'],
      'fecha_emision'=>$request['fecha_emision'],
      'fecha_vencimiento'=>$request['fecha_vencimiento'],
      'nro_transaccion'=>$request['nro_transaccion'],
      'documento_referencia'=>$request['documento_referencia'],
      'observacion'=>$request['observacion']]
    );

    if($folioData[1] == 'TVAL'){
      $folio->trash = 1;
      $folio->save();
      $folioData[1] = "El folio a utilizar ya estaba asignado, por favor intentelo nuevamente desde el detalle de la venta";
    }
     // Añadiendo movimiento a la base de datos del Historial
  History::createHistory($folioData, $pedido, $folio);
  if (!$folioData[0]){
    // TODO: Hacer que guarde el xml_string aunque no se haya creado el folio.
    // if(isset($_request['modal'])) return response()->json($folioData[1], 400);
    return [
      'success' => false,
      'content' => $folioData[1],
      'code' => 400,
    ];
  }

  // Asignando pdf de la venta realizada al folio y otros datos
  if(!isset($folioData[1]['no_send_data'])){
    $pedido->updated_at = date('Y-m-d H:i:s');
    unset($pedido->rut);
    unset($pedido->city);
    unset($pedido->comuna);
    unset($pedido->direction);
    unset($pedido->giro);
    unset($pedido->razon_social);
    $pedido->print = 1;
    $pedido->pdf_url = $folioData[1]['url'];

    // Convertimos el objeto stdClass a un array asociativo 
    $pedidoArray = (array) $pedido;

    // Guarda los cambios en la base de datos
    $resultadoSave = DB::table($app->database->name.'.requests')->where('id', $pedido->id)->update($pedidoArray);

    $folio->xml_string = $folioData[1]['xml_string'];
    $folio->sell_id = $pedido->id;
    $folio->pdf_url = $folioData[1]['url'];
    $folio->folioAsign = (integer) $folioData[1]['folioAsign'];
    $folio->save();

    $b64Doc = chunk_split(base64_encode(Self::getPutsContent($folio->pdf_url)));
    // if(isset($_request['modal'])) return response()->json($b64Doc, 200);
      return [
        'success' => true,
        'content' => $b64Doc,
        'code' => 200,
      ];
  }

  // if(isset($_request['modal'])) return response()->json($folioData[1]['xml_string'], 200);

  return [
    'success' => true,
    'content' => $folioData[1]['xml_string'],
    'code' => 200,
  ];
}

  // Procesar nota de crédito para pedido (basado en processFacturaPedido)
  public static function processNotaCreditoPedido($request, $pedido, $client, $app){
      $_request = is_array($request) ? $request : $request->all();

      if(!$pedido){
        return [
          'success' => false,
          'content' => 'Pedido no encontrado',
          'code' => 404,
        ];
      }

      // Para nota de crédito, necesitamos encontrar la factura original del pedido
      // Buscar el sell_id asociado al pedido
      $existingSell = Sell::where('created_at', '>=', $pedido->created_at)
                          ->where('total', $pedido->price)
                          ->where('typeSell', 'factura')
                          ->first();

      if (!$existingSell) {
        return [
          'success' => false,
          'content' => 'No se encontró la factura original para cancelar',
          'code' => 400,
        ];
      }

      // Verificar que no esté ya cancelada
      if ($existingSell->typeSell == 'nota_de_credito') {
        return [
          'success' => false,
          'content' => 'La factura ya está cancelada',
          'code' => 400,
        ];
      }

      // Verificar existencia de un folio de nota de crédito
      $folio = Folio::whereNull('sell_id')->where('trash', 0)->where('type','nota_de_credito')->first();
      if (!$folio){
        return [
          'success' => false,
          'content' => 'No hay folios de nota de crédito disponibles',
          'code' => 400,
        ];
      }

      // Buscar el folio de la factura original
      $facturaExists = Folio::where('sell_id', $existingSell->id)->where('type','factura')->first();
      if (!$facturaExists) {
        return [
          'success' => false,
          'content' => 'No existe folio de factura para esta venta',
          'code' => 400,
        ];
      }

      $folioToCancel = $facturaExists->folioAsign;
      $dateToCancel = isset($_request['cancelDate']) ? date('Y-m-d', strtotime($_request['cancelDate'])) : date('Y-m-d');

      // Usar el servicio SII para cancelar (igual que notaDeCreditoFactura)
      $folioData = ServicesSII::cancelarFactura($existingSell, $folio, $folioToCancel, $dateToCancel);
      
      if($folioData[1] == 'TVAL'){
        $folio->trash = 1;
        $folio->save();
        return [
          'success' => false,
          'content' => 'El folio ya estaba asignado, intente nuevamente',
          'code' => 400,
        ];
      }

      if (!$folioData[0]) {
        return [
          'success' => false,
          'content' => $folioData[1],
          'code' => 400,
        ];
      }

      // Asignar PDF y otros datos (igual que notaDeCreditoFactura)
      if(!isset($folioData[1]['no_send_data'])){
        $existingSell->updated_at = date('Y-m-d H:i:s');
        $existingSell->typeSell = $folio->type;
        $existingSell->sell_folio = $folio->folio;
        $existingSell->save();

        $folio->xml_string = $folioData[1]['xml_string'];
        $folio->sell_id = $existingSell->id;
        $folio->pdf_url = $folioData[1]['url'];
        $folio->folioAsign = (integer) $folioData[1]['folioAsign'];
        $folio->save();

        // Generar PDF base64
        $b64Doc = chunk_split(base64_encode(Self::getPutsContent($folio->pdf_url)));
        return [
          'success' => true,
          'content' => $b64Doc,
          'code' => 200,
        ];
      }

      return [
        'success' => true,
        'content' => $folioData[1]['xml_string'],
        'code' => 200,
      ];
  }

}
