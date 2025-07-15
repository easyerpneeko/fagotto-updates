<?php namespace App\Classes\ClientOrders\PhoneRepairOrder;

// Illuminate
use Illuminate\Support\Facades\Log;

//
use App\Helpers\CurlHelper;
use App\Classes\ClientOrders\PhoneRepairOrder\ImeiInterpreter;

class ImeiService {

  public static function examine($imei) {
    $response = Self::fetch($imei);
    $info     = ImeiInterpreter::interpretate($response);
    return $info;
  }

  public static function fetch($imei) {

    if ($imei === '') return false;

    if ($imei === 'testingimeihf1') {
      return json_decode('[{"resultado":"03","lang":"es","imeiNumber":"355400116125140"},{"resultado":"01","estado":"ACTIVO","homologacion":"4G e inferiores","baseOabi":"BDC","tipoInscripcion":"Validaci\u00f3n","fechaInscripcion":"2020-09-03","fechaVencimiento":"","imeiNumber":"355400116125140","cod_company":"WOM S.A. - NEXTEL S.A.","cod_motivo":"Mora Equipo","fecha_bloqueo":"2021-08-15","estado_claro":"Equipo Desbloqueado","radioDifusion":{"code":"111","message":"NA"}}]', 1);
    }else if ($imei === 'testingimeihf2') {
      return json_decode('[{"resultado":"03","lang":"es","imeiNumber":"353813088615538"},{"resultado":"01","estado":"ACTIVO","homologacion":"","baseOabi":"BDC","tipoInscripcion":"Registro Otros Dispositivos","fechaInscripcion":"2021-05-11","fechaVencimiento":"","imeiNumber":"353813088615538","cod_company":"WOM S.A. - NEXTEL S.A.","cod_motivo":"Robo","fecha_bloqueo":"2021-09-01","estado_claro":"Equipo Desbloqueado","radioDifusion":{"code":"","message":""}}]', 1);
    }else if ($imei === 'testingimeihf3') {
      return json_decode('[{"resultado":"03","lang":"es","imeiNumber":"356393102686069"},{"resultado":"01","estado":"ACTIVO","homologacion":"4G e inferiores","baseOabi":"BDC","tipoInscripcion":"Validaci\u00f3n","fechaInscripcion":"2019-10-08","fechaVencimiento":"","imeiNumber":"356393102686069","cod_company":"ENTEL PCS TELECOMUNICACIONES S.A.","cod_motivo":"Extravio","fecha_bloqueo":"2021-09-01","estado_claro":"Equipo Desbloqueado","radioDifusion":{"code":"111","message":"NA"}}]', 1);
    }

    try {
      $response = CurlHelper::factory('https://digital.clarochile.cl/wcm-iframe/consulta_imei/inc/registros.php')
      ->setHeaders(['Content-Type' => CurlHelper::MIME_X_WWW_FORM])
      ->setPostFields([
        'imei' => $imei,
        'lang' => 'es'
      ])->exec();
    } catch (\Exception $e) {
      Self::handleError('Error al hacer fetch ', $e);
      return false;
    }

    if ((string) $response['status'] === '200') {
      if ($response['data']) {
        return $response['data'];
      }
      return null;
    }

    return false;
  }

  private static function handleError($description, $e) {
    Log::info($description.' en ImeiService');
    Log::info($e);
  }

}

?>
