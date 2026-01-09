<?php namespace App\Classes;

use SoapClient;
use SimpleXMLElement;
use App\Classes\StringXML;
use App\models_local\Product;
use App\models_local\ProductSell;
use App\models_local\Sell;
use App\Aplication;
use App\Helpers\CurrentApp;

use App\Classes\ProcessResult as PR;

define("SPACESREPLACE", ['<?xml version="1.0"?>','
','
',"/\r|\n/",'          ','',' ']);

//ASIGNAR FOLIO -> FALSE
/*;sasd*/
class ServicesSII {

  // Cargar Folios
  public static function cargarFolio($xmlstring, $id) {

    $environment_vars = ServicesSII::getEnvs($id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){

      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");


      $parametros = array("STRINGCAF" => $xmlstring,"AMBIENTE" => $ambiente,"TOKEN" => $Token);

      try{
        //iniciar cliente soap
        $resultado = $client->__SoapCall("CargaFolio", $parametros);

        $DescripcionResultado=$resultado[0]->DescripcionResultado;
        $IdResultadoFE =$resultado[0]->IdResultadoFE;
        $ResultadoFE =$resultado[0]->ResultadoFE;
        $TiempoEjecucion=$resultado[0]->TiempoEjecucion;

        //Si hay algún problema intermedio será atrapado aquí.
        if ($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL')
        return [true,$ResultadoFE];

        return [false,"Error al cargar el CAF, ".$ResultadoFE.' Descripcion resultado: '.$DescripcionResultado];

      }catch (SoapFault $e){
        return [false,"Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }

      return [false,"Error"];
    }

    return [true, 'exito'];
  }

  // Procesar factura
  public static function processFactura($sell, $folio) {
    // Obteniendo variables de entorno
    $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    // Aqui es donde se genera el XML "factura"
    $xml_dte = StringXML::facturaXML($sell, $folio->folio, $environment_vars);
    $folio->xml_string = $xml_dte;
    $folio->save();

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){
      // Obteniendo token
      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

      // ADICIONAL
      $xml_adicional = '<Adicional><Uno></Uno><Dos></Dos><Tres></Tres><Cuatro></Cuatro><Cinco></Cinco><Seis></Seis><Siete></Siete><Ocho></Ocho><Nueve></Nueve><Diez></Diez><Once></Once><Doce></Doce><Trece></Trece><Catorce></Catorce><Quince></Quince><Dieciseis></Dieciseis><Diecisiete></Diecisiete><Dieciocho></Dieciocho><Diecinueve></Diecinueve><Veinte></Veinte><Veintiuno></Veintiuno><Veintidos></Veintidos><Veintitres></Veintitres><Veinticuatro></Veinticuatro><Veinticinco></Veinticinco><Veintiseis></Veintiseis></Adicional>';

      $parametros = array(
        "STRINGXML" => $xml_dte,
        "STRINGXMLADICIONAL" => $xml_adicional,
        "ASIGNAFOLIO" => "False",
        "TIPOIMPRESO" => "1",
        "AMBIENTE" => $ambiente,
        "TOKEN" => $Token
      );

      try{
        //iniciar cliente soap
        $resultado = $client->__SoapCall("ProcesaDte", $parametros);

        $folio->response_json = json_encode($resultado[0]);
        $folio->save();

        $DescripcionResultado = $resultado[0]->DescripcionResultado;
        $IdResultadoFE = $resultado[0]->IdResultadoFE;
        $ResultadoFE = $resultado[0]->ResultadoFE;

        if ($DescripcionResultado == 'TINV')
        if ($ResultadoFE && $ResultadoFE !== 'NULL') return [false,"Error al procesar DTE ".$ResultadoFE];
        else return [false,"Token invalido"];

        if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

        if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
        return [false,"Error al procesar DTE ".$ResultadoFE];

        //Si hay algún problema intermedio será atrapado aquí.
        $ppfactura = [true, [
          'xml_string' => $xml_dte,
          'result'     => $DescripcionResultado,
          'idResult'   => $IdResultadoFE,
          'resultFE'   => $ResultadoFE,
          'folioAsign' => $resultado[0]->FolioAsignado,
          'url'        => $resultado[0]->UrlPdf,
        ]];

        if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
        return $ppfactura;

        return [false,"Error al procesar DTE ".$ResultadoFE];

      }catch (SoapFault $e){
        return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }
      return [false, "Error"];
    }

    return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
  }
  public static function processPedidoFactura_con_Data($pedido, $folio, $data) {
    // Mapear observacion a comment para compatibilidad con StringXML
    if (!isset($data['comment']) && isset($data['observacion'])) {
        $data['comment'] = $data['observacion'];
    }
    
    // Obteniendo variables de entorno
    $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    // Aqui es donde se genera el XML "factura"
    $xml_dte = StringXML::facturaPedidoXML_con_Data($pedido, $folio->folio, $environment_vars, $data);
    // dd($xml_dte);
    $folio->xml_string = $xml_dte;
    $folio->save();

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){
      // Obteniendo token
      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

      // ADICIONAL
      $xml_adicional = '<Adicional><Uno></Uno><Dos></Dos><Tres></Tres><Cuatro></Cuatro><Cinco></Cinco><Seis></Seis><Siete>'.$data['observacion'].'</Siete><Ocho></Ocho><Nueve></Nueve><Diez></Diez><Once></Once><Doce></Doce><Trece></Trece><Catorce></Catorce><Quince></Quince><Dieciseis></Dieciseis><Diecisiete></Diecisiete><Dieciocho></Dieciocho><Diecinueve></Diecinueve><Veinte></Veinte><Veintiuno></Veintiuno><Veintidos></Veintidos><Veintitres></Veintitres><Veinticuatro></Veinticuatro><Veinticinco></Veinticinco><Veintiseis></Veintiseis></Adicional>';

      $parametros = array(
        "STRINGXML" => $xml_dte,
        "STRINGXMLADICIONAL" => $xml_adicional,
        "ASIGNAFOLIO" => "False",
        "TIPOIMPRESO" => "1",
        "AMBIENTE" => $ambiente,
        "TOKEN" => $Token
      );
      // dd($parametros);
      try{
        //iniciar cliente soap
        $resultado = $client->__SoapCall("ProcesaDte", $parametros);

        $folio->response_json = json_encode($resultado[0]);
        $folio->save();

        $DescripcionResultado = $resultado[0]->DescripcionResultado;
        $IdResultadoFE = $resultado[0]->IdResultadoFE;
        $ResultadoFE = $resultado[0]->ResultadoFE;

        if ($DescripcionResultado == 'TINV')
        if ($ResultadoFE && $ResultadoFE !== 'NULL') return [false,"Error al procesar DTE ".$ResultadoFE];
        else return [false,"Token invalido"];

        if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

        if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
        return [false,"Error al procesar DTE ".$ResultadoFE];

        //Si hay algún problema intermedio será atrapado aquí.
        $ppfactura = [true, [
          'xml_string' => $xml_dte,
          'result'     => $DescripcionResultado,
          'idResult'   => $IdResultadoFE,
          'resultFE'   => $ResultadoFE,
          'folioAsign' => $resultado[0]->FolioAsignado,
          'url'        => $resultado[0]->UrlPdf,
        ]];

        if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
        return $ppfactura;

        return [false,"Error al procesar DTE ".$ResultadoFE];

      }catch (SoapFault $e){
        return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }
      return [false, "Error"];
    }

    return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
  }

  public static function processFactura_con_Data($sell, $folio, $data) {
    // Mapear observacion a comment para compatibilidad con StringXML
    if (!isset($data['comment']) && isset($data['observacion'])) {
        $data['comment'] = $data['observacion'];
    }
    
    // Obteniendo variables de entorno
    $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    // Aqui es donde se genera el XML "factura"
    $xml_dte = StringXML::facturaXML_con_Data($sell, $folio->folio, $environment_vars, $data);
    $folio->xml_string = $xml_dte;
    $folio->save();

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){
      // Obteniendo token
      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

      // ADICIONAL
      $xml_adicional = '<Adicional><Uno></Uno><Dos></Dos><Tres></Tres><Cuatro></Cuatro><Cinco></Cinco><Seis></Seis><Siete>'.$data['observacion'].'</Siete><Ocho></Ocho><Nueve></Nueve><Diez></Diez><Once></Once><Doce></Doce><Trece></Trece><Catorce></Catorce><Quince></Quince><Dieciseis></Dieciseis><Diecisiete></Diecisiete><Dieciocho></Dieciocho><Diecinueve></Diecinueve><Veinte></Veinte><Veintiuno></Veintiuno><Veintidos></Veintidos><Veintitres></Veintitres><Veinticuatro></Veinticuatro><Veinticinco></Veinticinco><Veintiseis></Veintiseis></Adicional>';

      $parametros = array(
        "STRINGXML" => $xml_dte,
        "STRINGXMLADICIONAL" => $xml_adicional,
        "ASIGNAFOLIO" => "False",
        "TIPOIMPRESO" => "1",
        "AMBIENTE" => $ambiente,
        "TOKEN" => $Token
      );

      try{
        //iniciar cliente soap
        $resultado = $client->__SoapCall("ProcesaDte", $parametros);

        $folio->response_json = json_encode($resultado[0]);
        $folio->save();

        $DescripcionResultado = $resultado[0]->DescripcionResultado;
        $IdResultadoFE = $resultado[0]->IdResultadoFE;
        $ResultadoFE = $resultado[0]->ResultadoFE;

        if ($DescripcionResultado == 'TINV')
        if ($ResultadoFE && $ResultadoFE !== 'NULL') return [false,"Error al procesar DTE ".$ResultadoFE];
        else return [false,"Token invalido"];

        if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

        if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
        return [false,"Error al procesar DTE ".$ResultadoFE];

        //Si hay algún problema intermedio será atrapado aquí.
        $ppfactura = [true, [
          'xml_string' => $xml_dte,
          'result'     => $DescripcionResultado,
          'idResult'   => $IdResultadoFE,
          'resultFE'   => $ResultadoFE,
          'folioAsign' => $resultado[0]->FolioAsignado,
          'url'        => $resultado[0]->UrlPdf,
        ]];

        if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
        return $ppfactura;

        return [false,"Error al procesar DTE ".$ResultadoFE];

      }catch (SoapFault $e){
        return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }
      return [false, "Error"];
    }

    return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
  }

  public static function processGuiaDespacho($sell, $folio, $data = []) {
    // Obteniendo variables de entorno
    $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    // Aqui es donde se genera el XML "factura"
    $xml_dte = StringXML::guiaDespachoXML($sell, $folio->folio, $environment_vars);
    $folio->xml_string = $xml_dte;
    $folio->save();

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){
      // Obteniendo token
      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

      // ADICIONAL
      $xml_adicional = '<Adicional><Uno></Uno><Dos></Dos><Tres></Tres><Cuatro></Cuatro><Cinco></Cinco><Seis></Seis><Siete></Siete><Ocho></Ocho><Nueve></Nueve><Diez></Diez><Once></Once><Doce></Doce><Trece></Trece><Catorce></Catorce><Quince></Quince><Dieciseis></Dieciseis><Diecisiete></Diecisiete><Dieciocho></Dieciocho><Diecinueve></Diecinueve><Veinte></Veinte><Veintiuno></Veintiuno><Veintidos></Veintidos><Veintitres></Veintitres><Veinticuatro></Veinticuatro><Veinticinco></Veinticinco><Veintiseis></Veintiseis></Adicional>';

      $parametros = array(
        "STRINGXML" => $xml_dte,
        "STRINGXMLADICIONAL" => $xml_adicional,
        "ASIGNAFOLIO" => "False",
        "TIPOIMPRESO" => "1",   // 3 no manda error de internal server error al dejar en 9 termico
        "AMBIENTE" => $ambiente,
        "TOKEN" => $Token
      );

      try{
        //iniciar cliente soap
        $resultado = $client->__SoapCall("ProcesaDte", $parametros);

        $folio->response_json = json_encode($resultado[0]);
        $folio->save();

        $DescripcionResultado = $resultado[0]->DescripcionResultado;
        $IdResultadoFE = $resultado[0]->IdResultadoFE;
        $ResultadoFE = $resultado[0]->ResultadoFE;

        if ($DescripcionResultado == 'TINV')
        if ($ResultadoFE && $ResultadoFE !== 'NULL') return [false,"Error al procesar DTE ".$ResultadoFE];
        else return [false,"Token invalido"];

        if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

        if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
        return [false,"Error al procesar DTE ".$ResultadoFE];

        //Si hay algún problema intermedio será atrapado aquí.
        $ppfactura = [true, [
          'xml_string' => $xml_dte,
          'result'     => $DescripcionResultado,
          'idResult'   => $IdResultadoFE,
          'resultFE'   => $ResultadoFE,
          'folioAsign' => $resultado[0]->FolioAsignado,
          'url'        => $resultado[0]->UrlPdf,
        ]];

        if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
        return $ppfactura;

        return [false,"Error al procesar DTE ".$ResultadoFE];

      }catch (SoapFault $e){
        return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }
      return [false, "Error"];
    }

    return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
  }

  // Procesar boleta
  public static function processBoleta($sell, $folio) {
    // Obteniendo variables de entorno
    $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    // Aqui es donde se genera el XML "boleta"
    $xml_dte = StringXML::boletaXML($sell, $folio->folio, $environment_vars);
    $folio->xml_string = $xml_dte;
    $folio->save();

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){
      // Obteniendo token
      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

      // ADICIONAL
      $xml_adicional = '<Adicional><Uno></Uno><Dos></Dos><Tres></Tres><Cuatro></Cuatro><Cinco></Cinco><Seis></Seis><Siete></Siete><Ocho></Ocho><Nueve></Nueve><Diez></Diez><Once></Once><Doce></Doce><Trece></Trece><Catorce></Catorce><Quince></Quince><Dieciseis></Dieciseis><Diecisiete></Diecisiete><Dieciocho></Dieciocho><Diecinueve></Diecinueve><Veinte></Veinte><Veintiuno></Veintiuno><Veintidos></Veintidos><Veintitres></Veintitres><Veinticuatro></Veinticuatro><Veinticinco></Veinticinco><Veintiseis></Veintiseis></Adicional>';

      $parametros = array(
        "STRINGXML" => $xml_dte,
        "STRINGXMLADICIONAL" => $xml_adicional,
        "ASIGNAFOLIO" => "False",
        "TIPOIMPRESO" => "1",
        "AMBIENTE" => $ambiente,
        "TOKEN" => $Token
      );

      try{
        //iniciar cliente soap
        $resultado = $client->__SoapCall("ProcesaDte", $parametros);

        $folio->response_json = json_encode($resultado[0]);
        $folio->save();

        $DescripcionResultado=$resultado[0]->DescripcionResultado;
        $IdResultadoFE =$resultado[0]->IdResultadoFE;
        $ResultadoFE = $resultado[0]->ResultadoFE;

        if ($DescripcionResultado == 'TINV')
        if ($ResultadoFE && $ResultadoFE !== 'NULL') return [false,"Error al procesar DTE ".$ResultadoFE];
        else return [false,"Token invalido"];

        if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

        if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
        return [false,"Error al procesar DTE ".$ResultadoFE];

        //Si hay algún problema intermedio será atrapado aquí.
        $ppfactura = [true, [
          'xml_string' => $xml_dte,
          'result'     => $DescripcionResultado,
          'idResult'   => $IdResultadoFE,
          'resultFE'   => $ResultadoFE,
          'folioAsign' => $resultado[0]->FolioAsignado,
          'url'        => $resultado[0]->UrlPdf,
        ]];

        if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
        return $ppfactura;

        return [false,"Error al procesar DTE ".$ResultadoFE];

      }catch (SoapFault $e){
        return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }

      return [false, "Error"];
    }

    return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
  }

  // Procesar nota de credito (factura)
  public static function cancelarFactura($sell, $folio, $folioCancelar, $fechaFolio) {
    // Obteniendo variables de entorno
    $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    // Aqui es donde se genera el XML "factura"
    $xml_dte = StringXML::notaXMLFactura($sell, $folio->folio, $environment_vars, $folioCancelar, $fechaFolio);
    $folio->xml_string = $xml_dte;
    $folio->save();

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){
      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

      // ADICIONAL
      $xml_adicional = '<Adicional><Uno></Uno><Dos></Dos><Tres></Tres><Cuatro></Cuatro><Cinco></Cinco><Seis></Seis><Siete></Siete><Ocho></Ocho><Nueve></Nueve><Diez></Diez><Once></Once><Doce></Doce><Trece></Trece><Catorce></Catorce><Quince></Quince><Dieciseis></Dieciseis><Diecisiete></Diecisiete><Dieciocho></Dieciocho><Diecinueve></Diecinueve><Veinte></Veinte><Veintiuno></Veintiuno><Veintidos></Veintidos><Veintitres></Veintitres><Veinticuatro></Veinticuatro><Veinticinco></Veinticinco><Veintiseis></Veintiseis></Adicional>';

      $parametros = array(
        "STRINGXML" => $xml_dte,
        "STRINGXMLADICIONAL" => $xml_adicional,
        "ASIGNAFOLIO" => "False",
        "TIPOIMPRESO" => "1",
        "AMBIENTE" => $ambiente,
        "TOKEN" => $Token
      );

      try{
        $resultado = $client->__SoapCall("ProcesaDte", $parametros);

        $folio->response_json = json_encode($resultado[0]);
        $folio->save();

        $DescripcionResultado = $resultado[0]->DescripcionResultado;
        $IdResultadoFE = $resultado[0]->IdResultadoFE;
        $ResultadoFE = $resultado[0]->ResultadoFE;

        if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

        if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
        return [false,"Error al procesar DTE para cancelar nota de credito, ".$ResultadoFE];

        //Si hay algún problema intermedio será atrapado aquí.
        $ppfactura = [true, [
          'xml_string' => $xml_dte,
          'result'     => $DescripcionResultado,
          'idResult'   => $IdResultadoFE,
          'resultFE'   => $ResultadoFE,
          'folioAsign' => $resultado[0]->FolioAsignado,
          'url'        => $resultado[0]->UrlPdf,
        ]];

        if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
        return $ppfactura;

        return [false,"Error al procesar DTE ".$ResultadoFE];

      }catch (SoapFault $e){
        return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }
      return [false, "Error"];
    }
    return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
  }

  // Procesar nota de credito (boleta)
  public static function cancelarBoleta($sell, $folio, $folioCancelar, $fechaFolio) {
    // Obteniendo variables de entorno
    $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    // Aqui es donde se genera el XML "factura"
    $xml_dte = StringXML::notaXMLBoleta($sell, $folio->folio, $environment_vars, $folioCancelar, $fechaFolio);
    $folio->xml_string = $xml_dte;
    $folio->save();

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){
      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

      // ADICIONAL
      $xml_adicional = '<Adicional><Uno></Uno><Dos></Dos><Tres></Tres><Cuatro></Cuatro><Cinco></Cinco><Seis></Seis><Siete></Siete><Ocho></Ocho><Nueve></Nueve><Diez></Diez><Once></Once><Doce></Doce><Trece></Trece><Catorce></Catorce><Quince></Quince><Dieciseis></Dieciseis><Diecisiete></Diecisiete><Dieciocho></Dieciocho><Diecinueve></Diecinueve><Veinte></Veinte><Veintiuno></Veintiuno><Veintidos></Veintidos><Veintitres></Veintitres><Veinticuatro></Veinticuatro><Veinticinco></Veinticinco><Veintiseis></Veintiseis></Adicional>';

      $parametros = array(
        "STRINGXML" => $xml_dte,
        "STRINGXMLADICIONAL" => $xml_adicional,
        "ASIGNAFOLIO" => "False",
        "TIPOIMPRESO" => "1",
        "AMBIENTE" => $ambiente,
        "TOKEN" => $Token
      );

      try{
        $resultado = $client->__SoapCall("ProcesaDte", $parametros);

        $folio->response_json = json_encode($resultado[0]);
        $folio->save();

        $DescripcionResultado = $resultado[0]->DescripcionResultado;
        $IdResultadoFE = $resultado[0]->IdResultadoFE;
        $ResultadoFE = $resultado[0]->ResultadoFE;

        if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

        if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
        return [false,"Error al procesar DTE para cancelar nota de credito, ".$ResultadoFE];

        //Si hay algún problema intermedio será atrapado aquí.
        $ppfactura = [true, [
          'xml_string' => $xml_dte,
          'result'     => $DescripcionResultado,
          'idResult'   => $IdResultadoFE,
          'resultFE'   => $ResultadoFE,
          'folioAsign' => $resultado[0]->FolioAsignado,
          'url'        => $resultado[0]->UrlPdf,
        ]];

        if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
        return $ppfactura;

        return [false,"Error al procesar DTE ".$ResultadoFE];
      }catch (SoapFault $e){
        return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }
      return [false, "Error"];
    }
    return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
  }

  // Consulta SII
  public static function consultarDTESII($folio, $sell, $currentApp = null, $environment_vars = null, $response = false) {

    if (!$currentApp)
      $currentApp = CurrentApp::App();

    if (!$environment_vars) {
      $environment_vars = ServicesSII::getEnvs($currentApp->id);
    }
    $token = ServicesSII::obtenerToken($environment_vars);

    if ($token[0]) {/*echo $token[1];*/$Token = $token[1];}

    //Desahabilitar cache
    ini_set("soap.wsdl_cache_enabled", "0");
    //Reviso si es factura o boleta
    $tipoDTE = ($folio->type == 'factura')?"33":"39";
    //Establecer parametros de envío Ejemplo:
    $parametros = array("TIPODTE" => $tipoDTE,"FOLIODTE" => $folio->folio,"AMBIENTE" => "1","TOKEN" => $Token);

    //Dirección donde se encuentra el servicio
    $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");
    $badStates = array('1','3','4','5','11','12','13','14','15');
    //ObtenerToken
    try{
      //iniciar cliente soap
      $resultado = $client->__SoapCall("ConsultaEstadoDte", $parametros);

      $IdResultadoFE =$resultado[0]->IdResultadoFE;
      $ResultadoFE =$resultado[0]->ResultadoFE;
      $DescripcionResultado=$resultado[0]->DescripcionResultado;

      if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0){

        $folio->glosa_sii = $resultado[0]->GlosaEstadoSII;

        if ($resultado[0]->CodEstadoSII == "0") {
          $sell->siiState = 'aceptada';
        }elseif (in_array($resultado[0]->CodEstadoSII, $badStates)) {
          $sell->siiState = 'rechazada';
        }else{
          $sell->siiState = 'en_espera';
        }

        $folio->save();
        $sell->save();
        return response()->json($resultado[0]->GlosaEstadoSII,200);
      }
      return response()->json("Error al procesar DTE: ".$ResultadoFE,500);
    }catch (SoapFault $e){
      return response()->json($e,500);
    }
  }

  // Obtener token
  public static function obtenerToken($environment_vars) {
    //Desahabilitar cache
    ini_set("soap.wsdl_cache_enabled", "0");

    //Establecer parametros de envío Ejemplo:
    $parametros = array(
      "RUTACCESOAPI" => $environment_vars['sii_rut']['value'],
      "PASSWORDACCESOAPI" => $environment_vars['sii_password']['value']
    );

    //Dirección donde se encuentra el servicio
    $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

    //ObtenerToken
    try{
      //iniciar cliente soap
      $resultado = $client->__SoapCall("ObtenerToken", $parametros);

      //Parametros de salida
      $DescripcionResultado=$resultado[0]->DescripcionResultado;
      $Token=$resultado[0]->Token;
      $FechaHoraToken=$resultado[0]->FechaHoraRegistro;
      $TiempoEjecucion=$resultado[0]->TiempoEjecucion;

      return [true,$Token];

      //Si hay algún problema intermedio ser atrapado aquí.
    }catch (SoapFault $e){
      return [false,"Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
    }

    return [false,"Error"];
  }

  public static function xmlToArray($xml_string){
    // Transformando de string a XML
    $xml = simplexml_load_string($xml_string);
    // Transformando de xml a json
    $json = json_encode($xml);
    // Transformando de json a array
    $jsonArray = json_decode($json,true);

    return $jsonArray;
  }

  public static function getTypeFile($document){
    $typeDocument = array(
      ['type' => 'factura', 'value' => '33'],
      ['type' => 'boleta', 'value' => '39'],
      ['type' => 'nota_de_credito', 'value' => '61'],
      ['type' => 'guia_de_despacho', 'value' => '52']
    );
    
    // Convertir a string para asegurar comparación correcta
    $document = (string) $document;
    
    foreach ($typeDocument as $key) {
      if ($document == $key['value']) return $key['type'];
    }
    
    // Si no encuentra el tipo, lanzar una excepción con info útil
    throw new \Exception("Tipo de documento no reconocido: TD={$document}. Tipos válidos: 33 (Factura), 39 (Boleta), 52 (Guía Despacho), 61 (Nota Crédito)");
  }

  public static function getEnvs($id){
    $app = Aplication::find($id);
    if(!$app) return false;

    $environment_vars = json_decode($app->environment_vars,true);
    return $environment_vars;
  }

  public static function ArrayToXML($array, &$xml_user_info) {
      foreach($array as $key => $value) {
        if(is_array($value)) {
          if(!is_numeric($key)){
            $subnode = $xml_user_info->addChild("$key");
            ServicesSII::ArrayToXML($value, $subnode);
          }else{
            $subnode = $xml_user_info->addChild("item$key");
            ServicesSII::ArrayToXML($value, $subnode);
          }
        }else {
          $xml_user_info->addChild("$key",htmlspecialchars("$value"));
        }
      }
    }

  // Obtener persona
  public static function getPerson($rut) {
    //Desahabilitar cache
    ini_set("soap.wsdl_cache_enabled", "0");

    //Establecer parametros de envío Ejemplo: "76689863-7"
    $parametros = array("RUTRECEPTOR" => $rut,"TOKEN" => "www.loss.cl");

    //Dirección donde se encuentra el servicio
    $client = new SoapClient("http://www.appoctava.cl/wsdatosreceptor/WebService.php?wsdl");

    try{
      //iniciar cliente soap
      $resultado = $client->__SoapCall("DatosReceptor", $parametros);

      //Parametros de salida
      $IdResultado=$resultado[0]->IdResultado;
      $DescripcionResultado=$resultado[0]->DescripcionResultado;

      // Error
      if ((!$IdResultado && $IdResultado !== '0') || $IdResultado == '1')
        return PR::Create(false, $DescripcionResultado);

      if ($IdResultado == '0' && isset($resultado[0]->Rut)) {
        return PR::Create(true,[
            'rut'             => $resultado[0]->Rut,
            'razon_social'    => $resultado[0]->RazonSocial,
            'giro'            => $resultado[0]->Giro,
            'direction'       => $resultado[0]->Direccion,
            'comuna'          => $resultado[0]->Comuna,
            'city'            => $resultado[0]->Ciudad,
        ]);
      }

      return PR::Create(false, $DescripcionResultado);

    }catch (SoapFault $e){
      //Si hay algún problema intermedio deberia ser atrapado aquí.
      return PR::Create(false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>");
    }

  }

  // -----------------------------------REPOSTERIA----------------------------------------
  public static function processPedidoReposteriaFactura_con_Data($pedido, $folio, $data) {
    // Mapear observacion a comment para compatibilidad con StringXML
    if (!isset($data['comment']) && isset($data['observacion'])) {
        $data['comment'] = $data['observacion'];
    }
    
    // Obteniendo variables de entorno
    $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
    if(!$environment_vars) [false, 'Aplicacion no encontrada'];

    $ambiente = $environment_vars['sii_ambiente']['value'];
    if(!$ambiente || $ambiente === 'false') $ambiente = "0";
    else $ambiente = "1";

    // Aqui es donde se genera el XML "factura"
    $xml_dte = StringXML::facturaPedidorReposteriaXML_con_Data($pedido, $folio->folio, $environment_vars, $data);
    dd($xml_dte);
    $folio->xml_string = $xml_dte;
    $folio->save();

    if(!$environment_vars['sii_no_enviar_datos']['value'] || $environment_vars['sii_no_enviar_datos']['value'] != 1){
      // Obteniendo token
      $token = ServicesSII::obtenerToken($environment_vars);
      if ($token[0]) {$Token = $token[1];}

      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");

      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/ws/WebService.php?wsdl");

      // ADICIONAL
      $xml_adicional = '<Adicional><Uno></Uno><Dos></Dos><Tres></Tres><Cuatro></Cuatro><Cinco></Cinco><Seis></Seis><Siete>'.$data['observacion'].'</Siete><Ocho></Ocho><Nueve></Nueve><Diez></Diez><Once></Once><Doce></Doce><Trece></Trece><Catorce></Catorce><Quince></Quince><Dieciseis></Dieciseis><Diecisiete></Diecisiete><Dieciocho></Dieciocho><Diecinueve></Diecinueve><Veinte></Veinte><Veintiuno></Veintiuno><Veintidos></Veintidos><Veintitres></Veintitres><Veinticuatro></Veinticuatro><Veinticinco></Veinticinco><Veintiseis></Veintiseis></Adicional>';

      $parametros = array(
        "STRINGXML" => $xml_dte,
        "STRINGXMLADICIONAL" => $xml_adicional,
        "ASIGNAFOLIO" => "False",
        "TIPOIMPRESO" => "1",
        "AMBIENTE" => $ambiente,
        "TOKEN" => $Token
      );
      // dd($parametros);
      try{
        //iniciar cliente soap
        $resultado = $client->__SoapCall("ProcesaDte", $parametros);

        $folio->response_json = json_encode($resultado[0]);
        $folio->save();

        $DescripcionResultado = $resultado[0]->DescripcionResultado;
        $IdResultadoFE = $resultado[0]->IdResultadoFE;
        $ResultadoFE = $resultado[0]->ResultadoFE;

        if ($DescripcionResultado == 'TINV')
        if ($ResultadoFE && $ResultadoFE !== 'NULL') return [false,"Error al procesar DTE ".$ResultadoFE];
        else return [false,"Token invalido"];

        if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

        if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
        return [false,"Error al procesar DTE ".$ResultadoFE];

        //Si hay algún problema intermedio será atrapado aquí.
        $ppfactura = [true, [
          'xml_string' => $xml_dte,
          'result'     => $DescripcionResultado,
          'idResult'   => $IdResultadoFE,
          'resultFE'   => $ResultadoFE,
          'folioAsign' => $resultado[0]->FolioAsignado,
          'url'        => $resultado[0]->UrlPdf,
        ]];

        if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
        return $ppfactura;

        return [false,"Error al procesar DTE ".$ResultadoFE];

      }catch (SoapFault $e){
        return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
      }
      return [false, "Error"];
    }

    return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
  }
}
