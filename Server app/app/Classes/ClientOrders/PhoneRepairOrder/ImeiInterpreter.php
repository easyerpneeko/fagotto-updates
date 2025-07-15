<?php namespace App\Classes\ClientOrders\PhoneRepairOrder;

// Illuminate
use Illuminate\Support\Facades\Log;

class ImeiInterpreter {

  const COMPATIBILIDAD_BANDAS
    = [
        "2G", "3G", "4G", "3G e inferiores",
        "4G y 3G, sin soporte de 2G", "4G y 2G, sin soporte de 3G"
      ];

  public static function interpretate($response) {
    $data = $response[1];
    //
    $estadoImei       = $data['estado'];
    $bandas           = $data['homologacion'];
    $tipoInscripcion  = $data['tipoInscripcion'];
    $baseOabi         = $data['baseOabi'];
    //
    $cod_motivo       = $data['cod_motivo'];
    $estado_claro     = $data['estado_claro'];
    $cod_company      = $data['cod_company'];
    $fecha_bloqueo    = $data['fecha_bloqueo'];
    //
    $fechaInscripcion    = $data['fechaInscripcion'];
    $fechaVencimiento    = $data['fechaVencimiento'];
    //
    $description = Self::resolveHeaderText($estadoImei, $bandas, $tipoInscripcion, $baseOabi).'';
    $description .= Self::resolveBlockerText($cod_motivo, $estado_claro, $cod_company, $fecha_bloqueo).'';
    //
    $object = new \stdClass();
    $object->description = $description;
    $object->json = [];

    if ($estadoImei && $estadoImei !== '')
      $object->json['activo']           = ['Estado IMEI',          $estadoImei];

    if ($bandas && $bandas !== '')
      $object->json['bandas']           = ['Bandas',          $bandas];

    if ($tipoInscripcion && $tipoInscripcion !== '')
      $object->json['inscripcion']      = ['Tipo de inscripcion',          $tipoInscripcion];

    if ($estado_claro && $estado_claro !== '')
      $object->json['estado']           = ['Estado',          $estado_claro];

    if ($cod_company && $cod_company !== '')
      $object->json['company']          = ['Compañia',          $cod_company];

    if ($fechaInscripcion && $fechaInscripcion !== '')
      $object->json['inscripcion_date'] = ['Fecha de inscripcion',          $fechaInscripcion];

    return $object;
  }

  public static function resolveBlockerText($cod_motivo, $estado_claro, $cod_company, $fecha_bloqueo) {
    if ($cod_motivo != "No se encontraron registros" && $estado_claro == "Equipo Bloqueado")
        return 'No obstante, se encuentra  bloqueado por '. $cod_motivo . ' , desde el ' . $fecha_bloqueo . ' , por denuncia en la empresa ' . $cod_company . '.';
    return null;
  }

  public static function resolveHeaderText($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($text = Self::Caso1($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso2($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso3($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso4($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso5($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso6($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso7($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso8($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso9($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso10($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso11($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso12($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso13($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso14($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    if ($text = Self::Caso15($estadoImei, $bandas, $tipoInscripcion, $baseOabi)) return $text;
    return '...Informacion no disponible...';
  }

  public static function Caso1($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "ACTIVO" && $bandas == "4G e inferiores" && $baseOabi == "BDC")
      return 'Tu equipo se encuentra inscrito en el sistema y puede operar en todas las redes móviles nacionales';
    return null;
  }

  public static function Caso2($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "ACTIVO" && in_array($bandas, Self::COMPATIBILIDAD_BANDAS) && $baseOabi == "BDC")
      return 'Tu equipo se encuentra inscrito en el sistema y puede operar en las redes móviles nacionales, según las restricciones que indica el sello.';
    return null;
  }

  public static function Caso3($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "INACTIVO" && $bandas == "4G e inferiores" && $baseOabi == "BDC")
      return 'Tu equipo se encuentra inscrito en el sistema y puede operar en todas las redes móviles nacionales.';
    return null;
  }

  public static function Caso4($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "INACTIVO" && in_array($bandas, Self::COMPATIBILIDAD_BANDAS) && $baseOabi == "BDC")
      return 'Tu equipo se encuentra inscrito en el sistema y puede operar en las redes móviles nacionales, según las restricciones que indica el sello.';
    return null;
  }

  public static function Caso5($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "ACTIVO" && $baseOabi == "BDC" && $tipoInscripcion == "Registro Otros Dispositivos")
      return 'Tu equipo se encuentra inscrito en el sistema, pero no hay información sobre si puede operar en las redes móviles de todas las empresas país.';
    return null;
  }

  public static function Caso6($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "INACTIVO" && $baseOabi == "BDC" && $tipoInscripcion == "Registro Otros Dispositivos")
      return 'Tu equipo se encuentra inscrito en el sistema, pero no hay información sobre si puede operar en las redes móviles de todas las empresas país.';
    return null;
  }

  public static function Caso7($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "ACTIVO" && $baseOabi == "BDC" && $tipoInscripcion == "Inscripción Administrativa")
      return 'Tu equipo celular se encuentra inscrito en el sistema a través de Inscripción Administrativa por haber sido adquirido directamente en el extranjero, pero no hay información sobre la compatibilidad con SAE o si puede operar en las redes móviles de todas las empresas del país';
    return null;
  }

  public static function Caso8($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "INACTIVO" && $baseOabi == "BDC" && $tipoInscripcion == "Inscripción Administrativa")
      return 'Tu equipo celular se encuentra inscrito en el sistema a través de Inscripción Administrativa por haber sido adquirido directamente en el extranjero, pero no hay información sobre la compatibilidad con SAE o si puede operar en las redes móviles de todas las empresas del país';
    return null;
  }

  public static function Caso9($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "ACTIVO" && $baseOabi == "BDC" && $tipoInscripcion == "Pre-Homologación")
      return 'Se trata de un equipo de prueba que se encuentra inscrito temporalmente y puede operar en las redes móviles nacionales sólo por 4 meses a contar de la fecha de inscripción';
    return null;
  }

  public static function Caso10($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "INACTIVO" && $baseOabi == "BDC" && $tipoInscripcion == "Pre-Homologación")
      return 'Se trata de un equipo de prueba que se encuentra inscrito temporalmente y puede operar en las redes móviles nacionales sólo por 4 meses a contar de la fecha de inscripción';
    return null;
  }

  public static function Caso11($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "ACTIVO" && $baseOabi == "BDH")
      return 'Tu equipo se encuentra inscrito en el sistema por haber cursado tráfico con anterioridad al 10/11/2018, pero no hay información sobre la compatibilidad con SAE (para los teléfonos), o si puede operar en las redes móviles de todas las empresas del páis';
    return null;
  }

  public static function Caso12($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "INACTIVO" && $baseOabi == "BDH")
      return 'Tu equipo se encuentra inscrito en el sistema por haber cursado tráfico con anterioridad al 10/11/2018, pero no hay información sobre la compatibilidad con SAE (para los teléfonos), o si puede operar en las redes móviles de todas las empresas del páis';
    return null;
  }

  public static function Caso13($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "ESTADO_1" || $estadoImei == "ESTADO_2" || $estadoImei == "ESTADO_3" || $estadoImei == "ESTADO_99" && $baseOabi == "BDT")
      return 'Tu equipo no se encuentra inscrito pero puede operar temporalmente en las redes móviles nacionales por 30 días a contar de la fecha en que por primera vez insertó la Sim Card o chip de un operador nacional. Si tu equipo lo trajiste desde el extranjero, lo debes inscribir antes de la fecha de vencimiento, o será bloqueado. Ingresa a https://multibanda.cl/ia, para conocer el procedimiento. Si tu equipo fue adquirido en Chile, quedarpa inhabilitado para su uso en las redes nacionales transcurrido el plazo de 30 días contado desde la fecha que se insertó por primera vez el chip. Puedes solicitar a la empresa que te lo vendió que, de acuerdo a lo que indica la Ley del Consumidor, proceda a: - La devolución del dinero, o, - El cambio de equipo por uno homologado; y, - Eventualmente, indemnización de daños y perjuicios (esto último, en tribunales).';
    return null;
  }

  public static function Caso14($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
    if ($estadoImei == "ESTADO_4" || $estadoImei == "ESTADO_5" && $baseOabi == "BDT")
      return 'Tu equipo no se encuentra inscrito y ya expiró el periodo de 30 días a contar de la fecha en que por primera vez insertó la Sim Card o chip de un operador nacional, por lo que está bloqueado. Si tu equipo lo trajiste desde el extranjero, lo debes inscribir siguiendo las instrucciones que se indican en https://multibanda.cl/ia. Si tu equipo fue adquirido en Chile, quedará definitivamente inhabilitado para su uso en las redes nacionales. Puedes solicitar a la empresa que te lo vendió que, de acuerdo a lo que indica la Ley del Consumidor, proceda a: - La devolución del dinero, o, - El cambio de equipo por uno homologado; y, - Eventualmente, indemnización de daños y perjuicios (esto último, en tribunales)';
    return null;
  }

  public static function Caso15($estadoImei, $bandas, $tipoInscripcion, $baseOabi) {
      if ($baseOabi != "BDT" && $baseOabi != "BDH" && $baseOabi != "BDC" || $estadoImei == "ESTADO_6")
        return 'Tu equipo no se encuentra inscrito en el sistema y no puede funcionar en las redes móviles nacionales. Si tu equipo lo trajiste desde el extranjero, lo debes inscribir. Ingresa a https://multibanda.cl/ia, para conocer el procedimiento. Si tu equipo fue adquirido en Chile y aparece como no inscrito, es porque no está homologado y quedará inhabilitado para su uso en las redes nacionales transcurrido el plazo de 30 días contado desde la fecha que insertes por primera vez un chip. Puedes solicitar a la empresa que te lo vendió que, de acuerdo a lo que indica la Ley del Consumidor, proceda a: - La devolución del dinero, o, - El cambio de equipo por uno homologado; y, - Eventualmente, indemnización de daños y perjuicios (esto último, en tribunales).';
      return null;
    }

}

?>
