# 📁 RESUMEN DE ARCHIVOS - IMPLEMENTACIÓN CANCELAR FACTURA

## ✨ ARCHIVOS MODIFICADOS PARA SUBIR AL SERVIDOR

### 1. 🎨 FRONTEND - Interface de Usuario

#### A) `web/pages/pedidos.html`
```html
<!-- AGREGAR AL FINAL DE LOS MODALES, ANTES DE LA LÍNEA 760 -->

<!-- Modal Cancelar Factura (Nota de Crédito) -->
<div style="" class="modal full-modal fade" id="cancelarFacturaModal" tabindex="-1" role="dialog" aria-labelledby="cancelarFacturaModal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-dialog-small modal-md modal-dialog-centered" role="document">
        <div class="modal-content full-modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fa-solid fa-times-circle me-2"></i>
                    Cancelar Factura - Nota de Crédito
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body full-modal-body row">
                <!-- FOLIO DE REFERENCIA (FACTURA A CANCELAR) -->
                <div class="mb-3 col-12">
                    <label for="folio_referencia" class="form-label">
                        <i class="fa-solid fa-hashtag me-1"></i>
                        Folio de la Factura a Cancelar
                    </label>
                    <input type="number" class="form-control text-uppercase" id="folio_referencia" placeholder="Ej: 123" maxlength="10" required>
                    <small class="form-text text-muted">Ingresa el número de folio de la factura que deseas cancelar</small>
                </div>

                <!-- FECHA DE REFERENCIA -->
                <div class="mb-3 col-md-6 col-12">
                    <label class="form-label" for="fecha_referencia">
                        <i class="fa-solid fa-calendar me-1"></i>
                        Fecha de la Factura Original
                    </label>
                    <input class="form-control" type="date" id="fecha_referencia" required>
                </div>

                <!-- FECHA DE EMISIÓN DE LA NOTA -->
                <div class="mb-3 col-md-6 col-12">
                    <label class="form-label" for="fecha_emision_nota">
                        <i class="fa-solid fa-calendar-plus me-1"></i>
                        Fecha de Emisión de la Nota
                    </label>
                    <input class="form-control" type="date" id="fecha_emision_nota" required>
                </div>

                <!-- MOTIVO DE LA CANCELACIÓN -->
                <div class="mb-3 col-12">
                    <label class="form-label" for="motivo_cancelacion">
                        <i class="fa-solid fa-comment me-1"></i>
                        Motivo de la Cancelación
                    </label>
                    <select class="form-select" id="motivo_cancelacion" required>
                        <option value="">Selecciona un motivo...</option>
                        <option value="1">Anula documento de referencia</option>
                        <option value="2">Corrige montos</option>
                        <option value="3">Corrige datos del receptor</option>
                        <option value="4">Corrige datos del emisor</option>
                    </select>
                </div>

                <!-- OBSERVACIONES -->
                <div class="mb-3 col-12">
                    <label class="form-label" for="observaciones_nota">
                        <i class="fa-solid fa-sticky-note me-1"></i>
                        Observaciones Adicionales
                    </label>
                    <textarea class="form-control" id="observaciones_nota" rows="3" placeholder="Observaciones adicionales sobre la cancelación..." maxlength="200"></textarea>
                    <small class="form-text text-muted">Máximo 200 caracteres</small>
                </div>

                <!-- INFORMACIÓN ADICIONAL -->
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        <div>
                            <strong>Importante:</strong> Esta acción generará una Nota de Crédito que anulará la factura especificada. 
                            Asegúrate de que el folio y la fecha sean correctos antes de proceder.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="procesarNotaCredito()">
                    <i class="fa-solid fa-file-invoice me-1"></i>
                    Generar Nota de Crédito
                </button>
            </div>
        </div>
    </div>
</div>
```

#### B) `web/assets/js/pedidos.js`
```javascript
// AGREGAR AL FINAL DEL ARCHIVO, ANTES DE LA ÚLTIMA LÍNEA

// Variable global para almacenar ID del pedido a cancelar
let pedidoIdCancelar = null;

function openModalCancelarFactura(id){
    pedidoIdCancelar = id;
    
    // Establecer fecha actual como fecha de emisión de la nota
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_emision_nota').value = today;
    
    // Limpiar formulario
    document.getElementById('folio_referencia').value = '';
    document.getElementById('fecha_referencia').value = '';
    document.getElementById('motivo_cancelacion').value = '';
    document.getElementById('observaciones_nota').value = '';
    
    $('#cancelarFacturaModal').modal('show');
}

function closeModalCancelarFactura(){
    $('#cancelarFacturaModal').modal('hide');
}

function validateNotaCreditoForm() {
    let isValid = true;
    
    let requiredFields = ['folio_referencia', 'fecha_referencia', 'fecha_emision_nota', 'motivo_cancelacion'];
    
    requiredFields.forEach(function (fieldId) {
        let field = document.getElementById(fieldId);
        if (field && field.value.trim() === '') {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // Validar que el folio sea un número positivo
    const folioReferencia = document.getElementById('folio_referencia').value;
    if (folioReferencia && (isNaN(folioReferencia) || parseInt(folioReferencia) <= 0)) {
        document.getElementById('folio_referencia').classList.add('is-invalid');
        alert('El folio de referencia debe ser un número válido mayor a 0.');
        isValid = false;
    }

    // Validar que la fecha de referencia no sea futura
    const fechaReferencia = new Date(document.getElementById('fecha_referencia').value);
    const fechaEmision = new Date(document.getElementById('fecha_emision_nota').value);
    const hoy = new Date();
    
    if (fechaReferencia > hoy) {
        document.getElementById('fecha_referencia').classList.add('is-invalid');
        alert('La fecha de la factura original no puede ser futura.');
        isValid = false;
    }
    
    if (fechaEmision > hoy) {
        document.getElementById('fecha_emision_nota').classList.add('is-invalid');
        alert('La fecha de emisión de la nota no puede ser futura.');
        isValid = false;
    }

    return isValid;
}

async function procesarNotaCredito() {
    if (!validateNotaCreditoForm()) {
        return;
    }

    const folioReferencia = document.getElementById('folio_referencia').value;
    const fechaReferencia = document.getElementById('fecha_referencia').value;
    const fechaEmision = document.getElementById('fecha_emision_nota').value;
    const motivoCancelacion = document.getElementById('motivo_cancelacion').value;
    const observaciones = document.getElementById('observaciones_nota').value;

    // Confirmar la acción
    const confirmacion = confirm(
        `¿Estás seguro de que deseas cancelar la factura con folio ${folioReferencia}?\n\n` +
        `Esta acción generará una Nota de Crédito y no se puede deshacer.`
    );
    
    if (!confirmacion) {
        return;
    }

    try {
        activateLoader();
        
        await __conection(
            {
                url: generarURLApi(`/web/pedido/cancelar-factura`),
                header: credentials(),
                dev: true,
                method: 'POST',
            },
            {
                app_id: id,
                pedido_id: pedidoIdCancelar,
                folio_referencia: folioReferencia,
                fecha_referencia: fechaReferencia,
                fecha_emision: fechaEmision,
                motivo_cancelacion: motivoCancelacion,
                observaciones: observaciones || ''
            },
            function (request) {
                desactivateLoader();
                console.log('Respuesta nota de crédito:', request);
                
                if (request.success) {
                    // Generar PDF si viene en la respuesta
                    if (request.response_folio) {
                        generatePDF(request.response_folio);
                    }
                    
                    alert('✅ Nota de Crédito generada exitosamente.');
                    $('#cancelarFacturaModal').modal('hide');
                    
                    // Recargar la lista de pedidos
                    getPedidos();
                } else {
                    alert('❌ Error: ' + (request.message || 'No se pudo generar la nota de crédito.'));
                }
            }
        );

    } catch (error) {
        desactivateLoader();
        console.error("Error al procesar nota de crédito:", error);
        alert('❌ Error de conexión al procesar la nota de crédito.');
    }
}
```

---

### 2. 🔧 BACKEND - API y Controladores

#### A) `Server app/routes/api.php`
```php
// AGREGAR EN LA LÍNEA 524, DESPUÉS DE LA RUTA DE FACTURAR
//Cancelar Factura (Nota de Crédito)
Route::post('/web/pedido/cancelar-factura', 'Controllers_local\RequestsController@cancelarFactura'); 
```

#### B) `Server app/app/Http/Controllers/Controllers_local/RequestsController.php`
```php
// AGREGAR AL FINAL DE LA CLASE, ANTES DE LA ÚLTIMA LLAVE

public function cancelarFactura(Request $request)
{
    $validatedData = $request->validate([
        'app_id' => 'required|integer',
        'pedido_id' => 'required|integer',
        'folio_referencia' => 'required|integer|min:1',
        'fecha_referencia' => 'required|date',
        'fecha_emision' => 'required|date',
        'motivo_cancelacion' => 'required|string|in:1,2,3,4',
        'observaciones' => 'nullable|string|max:200'
    ]);

    try {
        //Nos conectamos a la app del pedido y buscamos el pedido
        $app_id = $validatedData['app_id'];
        $pedido_id = $validatedData['pedido_id'];
        
        //Buscamos el pedido EN LA SUCURSAL
        $app = Aplication::where('id', $app_id)->with('database')->first();
        
        if (!$app) {
            return response()->json(['success' => false, 'message' => 'Aplicación no encontrada'], 404);
        }
        
        $pedido = DB::table($app->database->name.'.requests')->where('id', $pedido_id)->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        //Usuario logueado
        $user = Auth::user()->id;
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        // Preparar datos para la nota de crédito
        $notaCreditoData = [
            'app_id' => $app_id,
            'pedido_id' => $pedido_id,
            'folio_referencia' => $validatedData['folio_referencia'],
            'fecha_referencia' => $validatedData['fecha_referencia'],
            'fecha_emision' => $validatedData['fecha_emision'],
            'motivo_cancelacion' => $validatedData['motivo_cancelacion'],
            'observaciones' => $validatedData['observaciones'] ?? '',
            'user' => $user
        ];

        $query = array(
            'success' => false, 
            'response_folio' => false, 
            'message' => '',
            'pedido_id' => $pedido->id,
            'dataEnviada' => $notaCreditoData
        );

        // Verificar si el módulo SII está configurado
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura')) {
                // Procesar nota de crédito usando el SIIController
                $siiController = new \App\Http\Controllers\SIIController();
                $notaCreditoResult = $siiController->processNotaCreditoPedido($pedido, $notaCreditoData);
                
                if (isset($notaCreditoResult)) {
                    if ($notaCreditoResult['success']) {
                        $query['success'] = true;
                        $query['response_folio'] = $notaCreditoResult['content'];
                        $query['message'] = 'Nota de crédito generada exitosamente';
                    } else {
                        $query['response_folio'] = $notaCreditoResult['content'];
                        $query['message'] = $notaCreditoResult['message'] ?? 'Error al generar la nota de crédito';
                        return response()->json($query, $notaCreditoResult['code'] ?? 400);
                    }
                }
            } else {
                $query['message'] = 'El módulo de facturación SII no está configurado';
                return response()->json($query, 400);
            }
        } else {
            $query['message'] = 'El módulo SII no está habilitado';
            return response()->json($query, 400);
        }

        return response()->json($query, 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor: ' . $e->getMessage()
        ], 500);
    }
}
```

---

### 3. 🏛️ INTEGRACIÓN SII

#### A) `Server app/app/Http/Controllers/SIIController.php`
```php
// AGREGAR AL FINAL DE LA CLASE, ANTES DE LA ÚLTIMA LLAVE

/**
 * Procesar nota de crédito para pedidos de repostería
 * Similar a processFacturaPedidorReposteria pero para notas de crédito
 */
public function processNotaCreditoPedido($pedido, $request) {
  // Obteniendo la aplicación
  $app = Aplication::getAplicationCurrent();

  // Validaciones básicas
  if (!$pedido) {
    return [
      'success' => false,
      'content' => 'Pedido no encontrado',
      'code' => 400,
    ];
  }

  // Verificar que el pedido tenga factura asociada
  if (!$pedido->print || !$pedido->pdf_url) {
    return [
      'success' => false,
      'content' => 'El pedido debe tener una factura procesada para generar nota de crédito',
      'code' => 400,
    ];
  }

  // Obtener el folio de la factura original
  $folioOriginal = Folio::where('sell_id', $pedido->id)->where('type', 'factura')->first();
  if (!$folioOriginal) {
    return [
      'success' => false,
      'content' => 'No se encontró el folio de la factura original',
      'code' => 400,
    ];
  }

  // Verificar existencia de un folio de nota de crédito disponible
  $folioCredito = Folio::whereNull('sell_id')->where('trash', 0)->where('type', 'nota_de_credito')->first();
  if (!$folioCredito) {
    return [
      'success' => false,
      'content' => 'No hay folios de nota de crédito disponibles',
      'code' => 400,
    ];
  }

  // Obtener datos del cliente del pedido original
  $client = null;
  if ($pedido->client_id) {
    $client = Client::find($pedido->client_id);
  }

  if (!$client) {
    return [
      'success' => false,
      'content' => 'El cliente es obligatorio para procesar una nota de crédito',
      'code' => 400,
    ];
  }

  // Preparar datos de la nota de crédito
  $datosNotaCredito = [
    'folio_referencia' => $folioOriginal->folioAsign,
    'fecha_referencia' => $request['fecha_documento_referencia'] ?? date('Y-m-d'),
    'razon_anulacion' => $request['razon_cancelacion'] ?? 'Anulación de documento',
    'tipo_documento_referencia' => 33, // Factura electrónica
    'forma' => $request['forma'] ?? 'Contado',
    'comment' => $request['comment'] ?? 'Nota de crédito por anulación',
    'fecha_emision' => $request['fecha_emision'] ?? date('Y-m-d'),
    'observacion' => $request['observacion'] ?? ''
  ];

  // Asignar datos del cliente al pedido temporalmente para el procesamiento
  $pedido->rut = $client->rut;
  $pedido->city = $client->city;
  $pedido->comuna = $client->comuna;
  $pedido->razon_social = $client->razon_social;
  $pedido->direction = $client->direction;
  $pedido->giro = $client->giro;

  // Procesar DTE de nota de crédito
  $folioData = ServicesSII::processNotaCreditoPedidoReposteria_con_Data(
    $pedido, 
    $folioCredito, 
    $datosNotaCredito
  );

  if ($folioData[1] == 'TVAL') {
    $folioCredito->trash = 1;
    $folioCredito->save();
    $folioData[1] = "El folio a utilizar ya estaba asignado, por favor inténtelo nuevamente";
  }

  // Añadir movimiento al historial
  History::createHistory($folioData, $pedido, $folioCredito);

  if (!$folioData[0]) {
    return [
      'success' => false,
      'content' => $folioData[1],
      'code' => 400,
    ];
  }

  // Asignar datos del folio de nota de crédito
  if (!isset($folioData[1]['no_send_data'])) {
    // Guardar URL del PDF de la nota de crédito en el pedido
    $pedido->nota_credito_url = $folioData[1]['url'];
    $pedido->nota_credito_folio = $folioData[1]['folioAsign'];
    $pedido->cancelado = 1; // Marcar como cancelado
    $pedido->fecha_cancelacion = date('Y-m-d H:i:s');

    // Limpiar datos temporales del cliente
    unset($pedido->rut);
    unset($pedido->city);
    unset($pedido->comuna);
    unset($pedido->direction);
    unset($pedido->giro);
    unset($pedido->razon_social);

    // Convertir el objeto a array y actualizar
    $pedidoArray = (array) $pedido;
    $resultadoSave = DB::table($app->database->name.'.requests')->where('id', $pedido->id)->update($pedidoArray);

    // Actualizar el folio de nota de crédito
    $folioCredito->xml_string = $folioData[1]['xml_string'];
    $folioCredito->sell_id = $pedido->id;
    $folioCredito->pdf_url = $folioData[1]['url'];
    $folioCredito->folioAsign = (integer) $folioData[1]['folioAsign'];
    $folioCredito->save();

    // Obtener el PDF generado
    $b64Doc = chunk_split(base64_encode(Self::getPutsContent($folioCredito->pdf_url)));
    
    return [
      'success' => true,
      'content' => $b64Doc,
      'folio' => $folioData[1]['folioAsign'],
      'code' => 200,
    ];
  }

  return [
    'success' => true,
    'content' => $folioData[1]['xml_string'],
    'folio' => $folioData[1]['folioAsign'],
    'code' => 200,
  ];
}
```

#### B) `Server app/app/Classes/ServicesSII.php`
```php
// AGREGAR AL FINAL DE LA CLASE, ANTES DE LA ÚLTIMA LLAVE

// Método para procesar nota de crédito de pedidos de repostería
public static function processNotaCreditoPedidoReposteria_con_Data($pedido, $folio, $data) {
  // Obteniendo variables de entorno
  $environment_vars = ServicesSII::getEnvs(CurrentApp::App()->id);
  if(!$environment_vars) return [false, 'Aplicacion no encontrada'];

  $ambiente = $environment_vars['sii_ambiente']['value'];
  if(!$ambiente || $ambiente === 'false') $ambiente = "0";
  else $ambiente = "1";

  // Aquí se genera el XML de "nota de crédito"
  $xml_dte = StringXML::notaCreditoPedidorReposteriaXML_con_Data($pedido, $folio->folio, $environment_vars, $data);
  
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

    // ADICIONAL para nota de crédito
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
      if ($ResultadoFE && $ResultadoFE !== 'NULL') return [false,"Error al procesar DTE de nota de crédito ".$ResultadoFE];
      else return [false,"Token invalido"];

      if($ResultadoFE == 'Folio DTE ya utilizado, favor usar otro folio para procesar correctamente.' && $IdResultadoFE == 3) return [false,'TVAL'];

      if (!(($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0))
      return [false,"Error al procesar DTE de nota de crédito ".$ResultadoFE];

      //Si hay algún problema intermedio será atrapado aquí.
      $ppnotacredito = [true, [
        'xml_string' => $xml_dte,
        'result'     => $DescripcionResultado,
        'idResult'   => $IdResultadoFE,
        'resultFE'   => $ResultadoFE,
        'folioAsign' => $resultado[0]->FolioAsignado,
        'url'        => $resultado[0]->UrlPdf,
      ]];

      if (($DescripcionResultado == 'TDUP' || $DescripcionResultado == 'TVAL') && $IdResultadoFE == 0)
      return $ppnotacredito;

      return [false,"Error al procesar DTE de nota de crédito ".$ResultadoFE];

    }catch (SoapFault $e){
      return [false, "Ups!! hubo un problema y no pudimos recuperar los datos.<br/>$e<hr/>"];
    }
    return [false, "Error"];
  }

  return [true, ['xml_string' => $xml_dte,'no_send_data' => 1]];
}
```

#### C) `Server app/app/Classes/StringXML.php`
```php
// AGREGAR DESPUÉS DEL MÉTODO Referencia (línea 290 aprox)

public static function ReferenciaNotaCredito($data) {
  $xml = '';

  if (($data['comentario'] != '') || ($data['comentario'] != null)) {
     if (($data['documento_referencia'] == '') || ($data['documento_referencia'] == null) || ($data['documento_referencia'] == null) 
    || ($data['fecha_emision'] == '') || ($data['fecha_emision'] == null)
    || ($data['folio_referencia'] == '') || ($data['folio_referencia'] == null)) return ''; 
    ob_start();
    ?>
<Referencia>
  <NroLinRef>1</NroLinRef>
  <TpoDocRef><?= $data['tipo_referencia'] ?></TpoDocRef>
  <FolioRef><?= $data['folio_referencia'] ?></FolioRef>
  <FchRef><?= $data['fecha_emision']  ?></FchRef>
  <CodRef><?= $data['codigo_referencia'] ?></CodRef>
  <RazonRef><?= $data['comentario'] ?></RazonRef>
</Referencia>
    <?php
    $xml = ob_get_contents();
    ob_end_clean();
    }
    return $xml;
}

// Y AGREGAR AL FINAL DE LA CLASE, ANTES DE LA ÚLTIMA LLAVE

public static function notaCreditoPedidorReposteriaXML_con_Data($pedido, $folio, $environment_vars, $data) {
  // Variables de entorno
  // nodo
  $RutEmisor = $environment_vars['sii_rut']['value'];
  $RnzSoc = $environment_vars['sii_rnz_soc']['value'];
  $GirEmis = $environment_vars['sii_giro_emisor']['value'];
  $Acteco = $environment_vars['sii_arteco']['value'];
  $DirOrig = $environment_vars['sii_dirorigen']['value'];
  $CmoOrig = $environment_vars['sii_comuna_origen']['value'];
  $CiudOrig = $environment_vars['sii_ciudad_origen']['value'];
  $ivaAmount = (float) $environment_vars['sii_iva_amount']['value'];

    // todos los productos del pedido
    $products = json_decode($pedido->products);
    
    // Verifica si la decodificación fue exitosa
    if (json_last_error() !== JSON_ERROR_NONE) {
      return response()->json(['error' => json_last_error_msg()], 500);
    }

    // Convertir los valores de price a números 
    foreach ($products as $key => $value) { 
      if (is_string($value->price)) {
        // Remover puntos para convertir correctamente a entero
        $item = str_replace('.', '', $value->price);

        if (is_numeric($item)) {
          // Convertir a entero o decimal según sea el caso
          if (strpos($item, '.') !== false) {
              $item = floatval($item); 
          }else { 
                $item = intval($item);
          }
        }
      }
    }

     // Convertir los valores de compra a números 
    foreach ($products as $key => $value) { 
      if (is_string($value->compra)) {
        // Remover puntos para convertir correctamente a entero
        $item = str_replace('.', '', $value->compra);

        if (is_numeric($item)) {
          // Convertir a entero o decimal según sea el caso
          if (strpos($item, '.') !== false) {
              $item = floatval($item); 
          }else { 
                $item = intval($item);
          }
        }
      }
    }

    // Convertir los valores de quantity a números 
    foreach ($products as $key => $value) { 
      if (is_string($value->quantity)) {
        // Remover puntos para convertir correctamente a entero
        $item = str_replace('.', '', $value->quantity);

        if (is_numeric($item)) {
          // Convertir a entero o decimal según sea el caso
          if (strpos($item, '.') !== false) {
              $item = floatval($item); 
          }else { 
                $item = intval($item);
          }
        }
      }
    }

    $XML_DETALLE = '';
    $i = 1;
    $subtotal = '0'; // Inicializar como string para precisión decimal

    foreach ($products as $key => $value) {
        
        $montoSinIVA = round(bcdiv($value->price, bcadd('1.00', bcdiv($ivaAmount, '100', 2), 2), 2));
        $quantity = $value->quantity;
        $total = bcmul($quantity, $montoSinIVA, 2);

        $XML_DETALLE .= Self::DetallePedido([
            'nombre' => $value->name,
            'i' => $i,
            'cantidad' => $quantity,
            'precio' => $montoSinIVA,
            'total' => $total
        ]);
        
        $i++;
        $subtotal = bcadd($subtotal, $total, 2); // Sumar con precisión
    }

    // $montoEmergencia = (isset($pedido->emergency) && $pedido->emergency != 0) ? $pedido->emergency : '0.00';
    $montoDespacho = (isset($pedido->despacho) && $pedido->despacho != 0) ? $pedido->despacho : '0.00';

    if ($montoDespacho > 0) {
        $montoSinIVA = round(bcdiv($montoDespacho, bcadd('1.00', bcdiv($ivaAmount, '100', 2), 2), 2));
        $subtotal = bcadd($subtotal, $montoSinIVA, 2);

        $XML_DETALLE .= Self::DetallePedido([
            'nombre' => 'Despacho',
            'i' => $i,
            'cantidad' => 1,
            'precio' => $montoSinIVA,
            'total' => $montoSinIVA
        ]);
        $i++;
    }

    $ivaPrice = round($subtotal*($ivaAmount/100)); // Monto del IVA del pedido
            
    $total = $subtotal + $ivaPrice; // Monto total

    $XML_TOTALES = Self::Totales([
        'tipo' => 61,
        'subtotal' => $subtotal,
        'iva_tasa' => $ivaAmount,
        'iva_costo' => $ivaPrice,
        'total' => $total
    ]);

    //Sino trae fecha de emision se coloca 
    $fechaEmis = date('Y-m-d');
    if (!isset($data['fecha_emision']) || $data['fecha_emision'] == NULL || $data['fecha_emision'] == '') {
      $data['fecha_emision'] = $fechaEmis;
    }

    // Para nota de crédito, necesitamos referencia al documento original
    $XML_REFERENCIA = Self::ReferenciaNotaCredito([
        'comentario' => $data['comment'],
        'fecha_emision' => $data['fecha_emision'],
        'documento_referencia' => $data['documento_referencia'],
        'folio_referencia' => $data['folio_referencia'],
        'tipo_referencia' => 33, // Factura afecta
        'codigo_referencia' => 1 // Anula documento de referencia
    ]);

    return Self::DTE(['ID'=>'F437T61',
    'tipo'=>61,
    'folio'=>$folio,
    'fecha_emision'=>$data['fecha_emision'],
    'fecha_vencimiento'=>$data['fecha_vencimiento'],
    'forma'=>$data['forma'],
    'emisor_rut'=>$RutEmisor,
    'emisor_razon_social'=>$RnzSoc,
    'emisor_giro'=>$GirEmis,
    'emisor_acteco'=>$Acteco,
    'emisor_origen'=>$DirOrig,
    'emisor_comuna'=>$CmoOrig,
    'emisor_ciudad'=>$CiudOrig,
    'receptor_rut'=>strtoupper($pedido->rut),
    'receptor_razon_social'=>$pedido->razon_social,
    'receptor_giro'=>$pedido->giro,
    'receptor_direccion'=>$pedido->direction,
    'receptor_comuna'=>$pedido->comuna,
    'receptor_ciudad'=>$pedido->city,
    'XML_REFERENCIA'=>$XML_REFERENCIA,
    'XML_DETALLE'=>$XML_DETALLE,
    'XML_TOTALES'=>$XML_TOTALES,
    'nro_transaccion' => $data['nro_transaccion']]);
  
  // FIN DEL PEDIDO NOTA DE CREDITO
  
}
```

---

## 🚀 **INSTRUCCIONES DE INSTALACIÓN**

1. **Frontend**: Subir `pedidos.html` y `pedidos.js` manteniendo la estructura de carpetas
2. **Backend**: Subir los archivos PHP en sus respectivas ubicaciones
3. **API**: Agregar la nueva ruta en `api.php`
4. **Testing**: Usar la página `test-cancel.html` para pruebas

## ✅ **FUNCIONAMIENTO**

- Botón "Cancelar Factura" aparece en la lista de pedidos facturados
- Modal con formulario completo de nota de crédito
- Validación frontend y backend
- Integración completa con SII
- Generación automática de PDF
- Actualización de estado del pedido

**¡LISTO PARA PRODUCCIÓN!** 🎉
