<?php namespace App\Classes;

use App\models_local\Product;
use App\models_local\ProductSell;
use App\models_local\ProductGuia;


class StringXML {

  public static function getProductName($productID) {
    $product = Product::find($productID)->name;
    $product = str_replace("ñ","n",$product);
    $product = str_replace("'","",$product);
    return $product;
  }

  //Generar xml para la factura
  public static function facturaXML($sell, $folio, $environment_vars) {
    // Variables de entorno
    // nodo
    /*

    */
    $RutEmisor = $environment_vars['sii_rut']['value'];
    $RnzSoc = $environment_vars['sii_rnz_soc']['value'];
    $GirEmis = $environment_vars['sii_giro_emisor']['value'];
    $Acteco = $environment_vars['sii_arteco']['value'];
    $DirOrig = $environment_vars['sii_dirorigen']['value'];
    $CmoOrig = $environment_vars['sii_comuna_origen']['value'];
    $CiudOrig = $environment_vars['sii_ciudad_origen']['value'];
    $ivaAmount = (float) $environment_vars['sii_iva_amount']['value'];

    // Varibles para los calculos y el xml
    $i = 1;
    $PU = 0;
    $PUS = 0;
    $PN = 0;

    $PT = 0;

    $PPT = 0;
    $detallesProducts='';
    $fechaEmis = date('Y-m-d');

    // pivotes de todos los productos
    $ordenProducts = ProductSell::where('sell',$sell->id)->get();

    foreach ($ordenProducts as $key => $value) {

      $value->price = (double) $value->price;
      $product = Self::getProductName($value->product);

      $detallesProducts .= '<Detalle>';
      //sustituir por "$i" si no funciona
      $detallesProducts .= '<NroLinDet>'.$i.'</NroLinDet>';
      $detallesProducts .= '<NmbItem>'.strtoupper($product).'</NmbItem>';
      $detallesProducts .= '<QtyItem>'.$value->quantity.'</QtyItem>';
      $detallesProducts .= '<UnmdItem>UN</UnmdItem>';


      $PU = round(((float) $value->price)); //Precio unitario

      $PUS = ($PU / (($ivaAmount/100) + 1)); //Precio unitario sin iva

      $detallesProducts .= '<PrcItem>'.$PUS.'</PrcItem>';

      // Monto total del producto sin iva (subtotal neto)
      $PPT = round($PUS*$value->quantity);

      $detallesProducts .= '<MontoItem>'.$PPT.'</MontoItem>';

      $detallesProducts .= '</Detalle>';

      // Sumatoria de monto neto
      $PN += round((float) $PPT); // Monto neto

      // Monto total
      /*
        PD: Mal. esto se calcula despues.
        $PPT = round($PU*$value->quantity);
        $PT += round((float) $PPT);
      */

      $i++;

    }

    // $PT = round($PN * (($ivaAmount/100) + 1)); // Monto total <--- por que lo quitaste?
    $PT = round($PN * (($ivaAmount/100) + 1)); // Monto total
    $ivaPrice = round($PT - $PN);
    $ivaAmount = round($ivaAmount);//$ivaAmount (el numerito)
    /*
      <FmaPago>1</FmaPago>
      contado UwU
      <FmaPago>2</FmaPago>
      credito :3
      <FmaPago>3</FmaPago>
      gratis <3

      mas lindo
    */

    $xml_dte = '<DTE version="1.0"><Documento ID="F437T33"><Encabezado><IdDoc><TipoDTE>33</TipoDTE><Folio>'.$folio.'</Folio><FchEmis>'.$fechaEmis.'</FchEmis><FmaPago>1</FmaPago></IdDoc><Emisor><RUTEmisor>'.$RutEmisor.'</RUTEmisor><RznSoc>'.$RnzSoc.'</RznSoc><GiroEmis>'.$GirEmis.'</GiroEmis><Acteco>'.$Acteco.'</Acteco><DirOrigen>'.$DirOrig.'</DirOrigen><CmnaOrigen>'.$CmoOrig.'</CmnaOrigen><CiudadOrigen>'.$CiudOrig.'</CiudadOrigen></Emisor><Receptor><RUTRecep>'.strtoupper($sell->rut).'</RUTRecep><RznSocRecep>'.$sell->razon_social.'</RznSocRecep><GiroRecep>'.$sell->giro.'</GiroRecep><DirRecep>'.$sell->direction.
    '</DirRecep><CmnaRecep>'.$sell->comuna.'</CmnaRecep><CiudadRecep>'.$sell->city.'</CiudadRecep></Receptor>
    <Totales>
    <MntNeto>'.$PN.'</MntNeto>
    <MntExe>0</MntExe>
    <TasaIVA>'.$ivaAmount.'</TasaIVA>
    <IVA>'.$ivaPrice.'</IVA>
    <MntTotal>'.$PT.'</MntTotal>
    </Totales></Encabezado>'.$detallesProducts.'</Documento></DTE>';

    return $xml_dte;

  }

  /*
    Tipo DTE
    33 => Factura
    39 => Boleta
    61 => Nota de Credito
    52 => Guia de Despacho
  */


  public static function DTE($data) {

    $XML_ID_DOC = Self::IdDoc($data);

    ob_start();
    ?>
<DTE version="1.0">
  <Documento ID="<?= $data['ID'] ?>">
      <Encabezado>
        <?= $XML_ID_DOC ?>
        <Emisor>
          <RUTEmisor><?= $data['emisor_rut'] ?></RUTEmisor>
          <RznSoc><?= $data['emisor_razon_social'] ?></RznSoc>
          <GiroEmis><?= $data['emisor_giro'] ?></GiroEmis>
          <Acteco><?= $data['emisor_acteco'] ?></Acteco>
          <DirOrigen><?= $data['emisor_origen'] ?></DirOrigen>
          <CmnaOrigen><?= $data['emisor_comuna'] ?></CmnaOrigen>
          <CiudadOrigen><?= $data['emisor_ciudad'] ?></CiudadOrigen>
        </Emisor>
        <Receptor>
          <RUTRecep><?= strtoupper($data['receptor_rut']) ?></RUTRecep>
          <RznSocRecep><?= $data['receptor_razon_social'] ?></RznSocRecep>
          <GiroRecep><?= $data['receptor_giro'] ?></GiroRecep>
          <DirRecep><?= $data['receptor_direccion'] ?></DirRecep>
          <CmnaRecep><?= $data['receptor_comuna'] ?></CmnaRecep>
          <CiudadRecep><?= $data['receptor_ciudad'] ?></CiudadRecep>
        </Receptor>
        <?= $data['XML_TOTALES'] ?>
      </Encabezado>
    <?= $data['XML_DETALLE'] ?>
    <?= $data['XML_REFERENCIA'] ?>
  </Documento>
</DTE>
    <?php
    $xml_dte = ob_get_contents();
    ob_end_clean();

    return $xml_dte;
  }

  public static function Totales($data) {
    ob_start();
    ?>
    <Totales>
          <MntNeto><?= ceil($data['subtotal']) ?></MntNeto>
    <?php
    switch($data['tipo']) {
      case 33: // factura
      case '33':
      ?>
      <MntExe>0</MntExe>
      <?php
      break;
    }
    ?>
          <TasaIVA><?= $data['iva_tasa'] ?></TasaIVA>
          <IVA><?= $data['iva_costo'] ?></IVA>
          <MntTotal><?= round($data['total'],2) ?></MntTotal>
    <?php
      switch($data['tipo']) {
        case 52: // guia de despacho
        case '52':
        case 33: // factura
        case '33':
        // nothing to add
        break;
        default:
      }
      ?>
      </Totales>
      <?php
      $xml = ob_get_contents();
      ob_end_clean();
  
      return $xml;

  }


  public static function DetallePedido($product) {
    ob_start();
    ?>
    <Detalle>
      <NroLinDet><?= $product['i'] ?></NroLinDet>
      <NmbItem><?= strtoupper($product['nombre']) ?></NmbItem>
      <QtyItem><?= ($product['cantidad']) ?></QtyItem>
      <UnmdItem>UN</UnmdItem>
      <PrcItem><?= ( $product['precio'] ) ?></PrcItem>   
      <MontoItem><?=( $product['total'] ) ?></MontoItem> 
    </Detalle>
  <?php
    $xml = ob_get_contents();
    ob_end_clean();
    return $xml;
  }

//JC - 
  public static function Detalle($product) {
    ob_start();
    ?>
<Detalle>
  <NroLinDet><?= $product['i'] ?></NroLinDet>
  <NmbItem><?= strtoupper($product['nombre']) ?></NmbItem>
  <QtyItem><?= ($product['cantidad']) ?></QtyItem>
  <UnmdItem>UN</UnmdItem>
  <PrcItem><?= ( $product['precio']) ?></PrcItem>   
  <MontoItem><?=( $product['total']) ?></MontoItem> 
</Detalle>
        <?php
    $xml = ob_get_contents();
    ob_end_clean();


    return $xml;
  }
  /*
    TpoDocRef
    801 -> Orden de Compra
    803 -> Contrato
    802 -> Nota de Pedido
    52 -> Guia de Despacho Electronica
    50 -> Guia de Despacho Manual
  */
  public static function Referencia($data) {
    $xml = '';

    if (($data['comentario'] != '') || ($data['comentario'] != null)) {
       if (($data['documento_referencia'] == '') || ($data['documento_referencia'] == null) || ($data['documento_referencia'] == null) 
      || ($data['fecha_emision'] == '') || ($data['fecha_emision'] == null)) return ''; 
      ob_start();
      ?>
  <Referencia>
    <NroLinRef>1</NroLinRef>
    <TpoDocRef><?= $data['documento_referencia'] ?></TpoDocRef>
    <FolioRef><?= $data['comentario'] ?></FolioRef>
    <FchRef><?= $data['fecha_emision']  ?></FchRef>
  </Referencia>
      <?php
      $xml = ob_get_contents();
      ob_end_clean();
      }
      return $xml;
  }

  public static function IdDoc($data) {

    ob_start();
    ?>
    <IdDoc>
          <TipoDTE><?= $data['tipo'] ?></TipoDTE>
          <Folio><?= $data['folio'] ?></Folio>
          <FchEmis><?= $data['fecha_emision'] ?></FchEmis>       
    <?php
    switch($data['tipo']) {
      case 33: // factura
      case '33':
        ?>
        <FmaPago><?= $data['forma'] ?></FmaPago>
        <?php if ($data['nro_transaccion'] != '') { ?>
        <TermPagoGlosa><?= $data['nro_transaccion'] ?></TermPagoGlosa>
        <?php } ?>
        <FchVenc><?= $data['fecha_vencimiento'] ?></FchVenc>
        <?php
        break;
      case 39: // boleta
      case '39':
        ?>
        <IndServicio>3</IndServicio>
        <?php
        break;
      case 52: // Guia de Despacho
      case '52':
        /*
          TipoDespacho = 1 / Receptor
          Despacho por cuenta del receptor del
          Documento Tributario Electronico (DTE)

          TipoDespacho = 2 / Emisor
          Despacho por cuenta  del emisor a 
          instalaciones del  cliente
          
          TipoDespacho = 3 / Tercero
          Despacho por cuenta  del emisor a 
          otras  instalaciones

          IndTraslado
          1 = : Operación constituye venta 
          2 = Ventas por efectuar 
          3: Consignaciones
          4: Entrega gratuita
          5: Traslados internos
          6: Otros traslados no venta
          7: Guía de devolución
          8: Traslado para 
          exportación. (no venta)
          9: Venta para exportación
        */
        ?>
        <TipoDespacho><?= $data['despacho'] ?></TipoDespacho>
        <IndTraslado><?= $data['translado'] ?></IndTraslado>
        <?php
        break;
      case 61: // Nota de Credito
      case '61':
      break;
    }
    ?>
    </IdDoc>
    <?php
    $xml = ob_get_contents();
    ob_end_clean();
    return $xml;

  }

  //JC - factura xml
  // genera folio factura
  public static function facturaXML_con_Data($sell, $folio, $environment_vars, $data) {
    // Variables de entorno
    // nodo
    /*

    */
    $RutEmisor = $environment_vars['sii_rut']['value'];
    $RnzSoc = $environment_vars['sii_rnz_soc']['value'];
    $GirEmis = $environment_vars['sii_giro_emisor']['value'];
    $Acteco = $environment_vars['sii_arteco']['value'];
    $DirOrig = $environment_vars['sii_dirorigen']['value'];
    $CmoOrig = $environment_vars['sii_comuna_origen']['value'];
    $CiudOrig = $environment_vars['sii_ciudad_origen']['value'];
    $ivaAmount = (float) $environment_vars['sii_iva_amount']['value'];

    // Varibles para los calculos y el xml
    $i = 1;
    $PU = 0;
    $PUS = 0;
    $PN = 0;

    $PT = 0;

    $PPT = 0;
    $detallesProducts='';
    $fechaEmis = date('Y-m-d');

    // pivotes de todos los productos
    $ordenProducts = ProductSell::where('sell',$sell->id)->get();
    $XML_DETALLE = '';
    foreach ($ordenProducts as $key => $value) {

      $value->price = (double) $value->price;
      $product = Self::getProductName($value->product);

      $PU = ((float) $value->price); //Precio unitario

      $PUS = $PU / (($ivaAmount/100) + 1); //Precio unitario sin iva
      $PUS = (double) ($PUS); 
       // Monto total del producto sin iva (subtotal neto)
       $PPT = $PUS*$value->quantity;

       // Sumatoria de monto neto
      $PN += (float) $PPT; // Monto neto


      $XML_DETALLE .= Self::Detalle(['nombre'=>$product,
                                          'i'=>$i,
                                          'cantidad'=>$value->quantity,
                                          'precio'=>round( $PUS,2),
                                          'total'=>round( $PPT)]);

      

      // Monto total
      /*
        PD: Mal. esto se calcula despues.
        $PPT = round($PU*$value->quantity);
        $PT += round((float) $PPT);
      */

      $i++;

    }

    // $PT = round($PN * (($ivaAmount/100) + 1)); // Monto total <--- por que lo quitaste?
    $PT = round($PN * (($ivaAmount/100) + 1),2); // Monto total
    $ivaPrice = round(($PT - $PN),2);
    $ivaAmount = round($ivaAmount,2);//$ivaAmount (el numerito)

    $XML_TOTALES = Self::Totales([ 
      'tipo'=>33,
      'subtotal'=>round( $PN),
      'iva_tasa'=>round( $ivaAmount),
      'iva_costo'=>round( $ivaPrice),
      'total'=>    round($PT)]);
    /*
      <FmaPago>1</FmaPago>
      contado UwU
      <FmaPago>2</FmaPago>
      credito :3
      <FmaPago>3</FmaPago>
      gratis <3

      mas lindo
    */

    if (!isset($data['fecha_emision']) || $data['fecha_emision'] == NULL || $data['fecha_emision'] == '') {
      $data['fecha_emision'] = $fechaEmis;
    }

    $XML_REFERENCIA = Self::Referencia(['comentario'=>$data['comment'],
                                'fecha_emision'=>$data['fecha_emision'],
                                'documento_referencia'=>$data['documento_referencia']]);


    return Self::DTE(['ID'=>'F437T33',
      'tipo'=>33,
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
      'receptor_rut'=>strtoupper($sell->rut),
      'receptor_razon_social'=>$sell->razon_social,
      'receptor_giro'=>$sell->giro,
      'receptor_direccion'=>$sell->direction,
      'receptor_comuna'=>$sell->comuna,
      'receptor_ciudad'=>$sell->city,
      // 'subtotal'=>$PN,
      // 'iva_tasa'=>$ivaAmount,
      // 'iva_costo'=>$ivaPrice,
      // 'total'=>$PT,
      'XML_REFERENCIA'=>$XML_REFERENCIA,
      'XML_DETALLE'=>$XML_DETALLE,
      'XML_TOTALES'=>$XML_TOTALES,
      'nro_transaccion' => $data['nro_transaccion']]);

  }

  // genera folio factura
  public static function facturaPedidoXML_con_Data($pedido, $folio, $environment_vars, $data) {
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



    if (isset($pedido->emergency) && $pedido->emergency > 0) {
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
          if ($value->category === 2) {
              $unitario = round(bcdiv($value->costo, $value->vasos, 2));
              $quantity = $value->vasos;
              $total = bcmul($quantity, $unitario, 2);
          } else {
              $unitario = round(bcdiv($value->costo, $value->quantity, 2));
              $quantity = $value->quantity;
              $total = round(bcmul($quantity, $unitario, 2));
          }

          $XML_DETALLE .= Self::DetallePedido([
              'nombre' => $value->name,
              'i' => $i,
              'cantidad' => $quantity,
              'precio' => $unitario,
              'total' => $total
          ]);
          $i++;
          $subtotal = bcadd($subtotal, $total, 2); // Sumar con precisión
      }

      $montoEmergencia = (isset($pedido->emergency) && $pedido->emergency != 0) ? $pedido->emergency : '0.00';
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

      if ($montoEmergencia > 0) {
          $montoSinIVA = round(bcdiv($montoEmergencia, bcadd('1.00', bcdiv($ivaAmount, '100', 2), 2), 2));
          $subtotal = bcadd($subtotal, $montoSinIVA, 2);

          $XML_DETALLE .= Self::DetallePedido([
              'nombre' => 'Emergencia',
              'i' => $i,
              'cantidad' => 1,
              'precio' => $montoSinIVA,
              'total' => $montoSinIVA
          ]);
          $i++;
      }

      // No añadir $pedido->subtotal de nuevo si ya se incluye en el subtotal calculado arriba
      // Si es necesario añadir, usa:
      // $subtotal = bcadd($subtotal, $pedido->subtotal, 2);

      $ivaPrice = round($subtotal*($ivaAmount/100)); // Monto del IVA del pedido
      $total = $subtotal + $ivaPrice; // Monto total

      $XML_TOTALES = Self::Totales([
          'tipo' => 33,
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

      $XML_REFERENCIA = Self::Referencia(['comentario'=>$data['comment'],
                                  'fecha_emision'=>$data['fecha_emision'],
                                  'documento_referencia'=>$data['documento_referencia']]);


      return Self::DTE(['ID'=>'F437T33',
      'tipo'=>33,
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
      // 'subtotal'=>$PN,
      // 'iva_tasa'=>$ivaAmount,
      // 'iva_costo'=>$ivaPrice,
      // 'total'=>$PT,
      'XML_REFERENCIA'=>$XML_REFERENCIA,
      'XML_DETALLE'=>$XML_DETALLE,
      'XML_TOTALES'=>$XML_TOTALES,
      'nro_transaccion' => $data['nro_transaccion']]);

    }// FIN DEL PEDIDO EMERGENCIA
    
    
    // INICIO PEDIDO NORMAL
    else{ 
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

      $precioVaso = 0;

      //Eliminar los "vasos de la formula"
      foreach ($products as $key => $value) {
        if ($value->id == 1) { 
          //Guardamos el precio del vaso
          $precioVaso = $value->price;
          //Eliminamos el vaso de la lista de productos
          unset($products[$key]); }
      }

      $XML_DETALLE = '';
      // Varibles para los calculos y el xml
      $i = 1;
      $subtotal = '0'; // Inicializar como string para precisión decimal

      // Pasamos los items al detalle solo nombre y cantidad
      foreach ($products as $key => $value) {
        // eliminar los vasos de los productos

        
        //Cant productos que conforman la formula Vaso (Salsa,Queso,Huevo,Harina) = 4;
        $cantidadProd = 4;

        $montoPorcentaje = $precioVaso / $cantidadProd;  // 25% para cada producto
        
        //Salsas
        if($value->category === 2){
          $unitario = round($montoPorcentaje, 2);
          $quantity = $value->vasos;
          $total = round($value->vasos * $unitario);

        //$value->price, esta variable contiene la cantidad que lleva la formula 22gr o 27gr

        //Por kilos Queso , separo el queso para pasar el monto a kilos
        }else if($value->id == 5){
          
          $unitario = round($montoPorcentaje, 2);
          $quantity = (($value->quantity*1000)/$value->price);
          $total = round($quantity * $unitario);
        //Por kilos harina
        }else if($value->id == 9 ){
          $unitario = round($montoPorcentaje, 2);
          $quantity = ($value->quantity*$value->price);
          $total = round($quantity * $unitario);
        //Opcionales 
        }else if($value->category === 3){
          $unitario = round($value->compra);
          $quantity = $value->quantity;
          $total = round($quantity * $unitario);

        //Por unidad 
        }else if($value->category === 1){
          $unitario = round($montoPorcentaje, 2);
          $quantity = $value->quantity * $value->price;
          $total = round($quantity * $unitario);
        }

        $XML_DETALLE .= Self::DetallePedido(
                                            [ 'nombre'=>$value->name,
                                              'i'=>$i,
                                              'cantidad'=>$quantity,
                                              'precio'=>floatval($unitario),
                                              'total'=>floatval($total)
                                            ]
                                          );

        $i++;
        $subtotal = bcadd($subtotal, $total, 2); // Sumar con precisión

      }
        
      $montoDespacho = (isset($pedido->despacho) && $pedido->despacho != 0) ? $pedido->despacho : '0.00';

      if ($montoDespacho > 0) {
        $montoSinIVA = ceil(bcdiv($montoDespacho, bcadd('1.00', bcdiv($ivaAmount, '100', 2), 2), 2));
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

      // No añadir $pedido->subtotal de nuevo si ya se incluye en el subtotal calculado arriba
      // Si es necesario añadir, usa:
      // $subtotal = bcadd($subtotal, $pedido->subtotal, 2);

      $ivaPrice = round($subtotal*($ivaAmount/100)); // Monto del IVA del pedido
      $total = $subtotal + $ivaPrice; // Monto total

      $XML_TOTALES = Self::Totales([
          'tipo' => 33,
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

      $XML_REFERENCIA = Self::Referencia(['comentario'=>$data['comment'],
                                  'fecha_emision'=>$data['fecha_emision'],
                                  'documento_referencia'=>$data['documento_referencia']]);


      return Self::DTE(['ID'=>'F437T33',
        'tipo'=>33,
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
        // 'subtotal'=>$PN,
        // 'iva_tasa'=>$ivaAmount,
        // 'iva_costo'=>$ivaPrice,
        // 'total'=>$PT,
        'XML_REFERENCIA'=>$XML_REFERENCIA,
        'XML_DETALLE'=>$XML_DETALLE,
        'XML_TOTALES'=>$XML_TOTALES,
        'nro_transaccion' => $data['nro_transaccion']]);
    }
  }
  // genera folio guia de despacho
  public static function guiaDespachoXML($sell, $folio, $environment_vars) {
    // Variables de entorno
    // nodo
    /*

    */
    $RutEmisor = $environment_vars['sii_rut']['value'];
    $RnzSoc = $environment_vars['sii_rnz_soc']['value'];
    $GirEmis = $environment_vars['sii_giro_emisor']['value'];
    $Acteco = $environment_vars['sii_arteco']['value'];
    $DirOrig = $environment_vars['sii_dirorigen']['value'];
    $CmoOrig = $environment_vars['sii_comuna_origen']['value'];
    $CiudOrig = $environment_vars['sii_ciudad_origen']['value'];
    $ivaAmount = (float) $environment_vars['sii_iva_amount']['value'];

    // Varibles para los calculos y el xml
    $i = 1;
    $PU = 0;
    $PUS = 0;
    $PN = 0;

    $PT = 0;

    $PPT = 0;
    $detallesProducts='';
    $fechaEmis = date('Y-m-d');

    // pivotes de todos los productos $data
    $ordenProducts = ProductGuia::where('guia',$sell->id)->get();
    $XML_DETALLE = '';
    foreach ($ordenProducts as $key => $value) {

      $value->price = (double) $value->price;
      $product = Self::getProductName($value->product);

      $PU = round(((float) $value->price)); //Precio unitario

      $PUS = round($PU / (($ivaAmount/100) + 1)); //Precio unitario sin iva

       // Monto total del producto sin iva (subtotal neto)
       $PPT = round($PUS*$value->quantity);

       // Sumatoria de monto neto
      $PN += round((float) $PPT); // Monto neto


      $XML_DETALLE .= Self::Detalle(['nombre'=>$product,
                                          'i'=>$i,
                                          'cantidad'=>$value->quantity,
                                          'precio'=>$PUS,
                                          'total'=>$PPT]);

      

      // Monto total
      /*
        PD: Mal. esto se calcula despues.
        $PPT = round($PU*$value->quantity);
        $PT += round((float) $PPT);
      */

      $i++;

    }

    // $PT = round($PN * (($ivaAmount/100) + 1)); // Monto total <--- por que lo quitaste?
    $PT = (double) ($PN * (($ivaAmount/100) + 1)); // Monto total
    $ivaPrice = (double) ($PT - $PN);
    $ivaAmount = (double) ($ivaAmount);//$ivaAmount (el numerito)

    $XML_TOTALES = Self::Totales([ 
    'tipo'=>52,
    'subtotal'=>$PN,
    'iva_tasa'=>$ivaAmount,
    'iva_costo'=>$ivaPrice,
    'total'=>$PT]);
    /*
      <FmaPago>1</FmaPago>
      contado UwU
      <FmaPago>2</FmaPago>
      credito :3
      <FmaPago>3</FmaPago>
      gratis <3

      mas lindo
    */

    $XML_REFERENCIA = Self::Referencia(['comentario'=>$sell->comment,
                                'fecha_emision'=>$fechaEmis,
                                'documento_referencia'=>(isset($sell->comment)) ? $sell->comment : '']);


    return Self::DTE(['ID'=>'R76220409-6T52F11',
      'tipo'=>52, // guia de despacho
      'despacho'=>$sell->despacho,
      'translado'=>$sell->translado,
      'folio'=>$folio,
      'fecha_emision'=>$fechaEmis,
      'emisor_rut'=>$RutEmisor,
      'emisor_razon_social'=>$RnzSoc,
      'emisor_giro'=>$GirEmis,
      'emisor_acteco'=>$Acteco,
      'emisor_origen'=>$DirOrig,
      'emisor_comuna'=>$CmoOrig,
      'emisor_ciudad'=>$CiudOrig,
      'receptor_rut'=>strtoupper($sell->rut),
      'receptor_razon_social'=>$sell->razon_social,
      'receptor_giro'=>$sell->giro,
      'receptor_direccion'=>$sell->direction,
      'receptor_comuna'=>$sell->comuna,
      'receptor_ciudad'=>$sell->city,
      'XML_REFERENCIA'=>$XML_REFERENCIA,
      'XML_DETALLE'=>$XML_DETALLE,
      'XML_TOTALES'=>$XML_TOTALES]);

  }
  

  

  //Generar xml para la boleta
  public static function boletaXML($sell, $folio, $environment_vars) {
    // Variables de entorno
    $RutEmisor = $environment_vars['sii_rut']['value'];
    $RnzSoc = $environment_vars['sii_rnz_soc']['value'];
    $GirEmis = $environment_vars['sii_giro_emisor']['value'];
    $Acteco = $environment_vars['sii_arteco']['value'];
    $DirOrig = $environment_vars['sii_dirorigen']['value'];
    $CmoOrig = $environment_vars['sii_comuna_origen']['value'];
    $CiudOrig = $environment_vars['sii_ciudad_origen']['value'];
    $ivaAmount = (float) $environment_vars['sii_iva_amount']['value'];

    // Varibles para los calculos y el xml
    $i = 1;
    $PU = 0;
    $PUS = 0;
    $PN = 0;
    $PT = 0;
    $PPT = 0;
    $detallesProducts='';
    $fechaEmis = date('Y-m-d');

    // pivotes de todos los productos
    $ordenProducts = ProductSell::where('sell',$sell->id)->get();

    foreach ($ordenProducts as $key => $value) {
      $value->price = (double) $value->price;
      $product = Self::getProductName($value->product);

      $detallesProducts .= '<Detalle>';
      //sustituir por "$i" si no funciona
      $detallesProducts .= '<NroLinDet>'.$i.'</NroLinDet>';

      $detallesProducts .= '<CdgItem>
      <TpoCodigo>INT1</TpoCodigo>
      <VlrCodigo>AAAAAA</VlrCodigo>
      </CdgItem>';

      $detallesProducts .= '<NmbItem>'.strtoupper($product).'</NmbItem>';
      $detallesProducts .= '<QtyItem>'.$value->quantity.'</QtyItem>';

      $PU = round(((float) $value->price)); // Precio unitario
      $detallesProducts .= '<PrcItem>'.$PU.'</PrcItem>';

      $PPT = round(((float) $value->price)*$value->quantity); // Monto total de los productos
      $detallesProducts .= '<MontoItem>'.$PPT.'</MontoItem>';
      $detallesProducts .= '</Detalle>';

      // Sacar precio neto
      $PT += round((float) $PPT);//<--- Monto total
      $PUS = round($PU / (($ivaAmount/100) + 1)); // Precio unitario sin iva
      $PPT = round($PUS*$value->quantity); // Monto total del producto sin iva
      $PN += round((float) $PPT); // Monto neto

      $i++;
    }


    //* $PT = round($PN *(($ivaAmount/100) + 1)); // Monto total

    // IVA Numerito
    $ivaAmount =  round($ivaAmount);
    // Neto = Total (PT) - 19%0
    $MontoNeto =  round($PT/(($ivaAmount/100) + 1));
    // Precio Iva = Total - Monto Neto
    $PrecioIva = $PT - $MontoNeto;


    $xml_dte = '<DTE version="1.0">
    <Documento ID="F437T33">
    <Encabezado><IdDoc><TipoDTE>39</TipoDTE><Folio>'.$folio.'</Folio><FchEmis>'.$fechaEmis.'</FchEmis><IndServicio>3</IndServicio></IdDoc><Emisor><RUTEmisor>'.$RutEmisor.'</RUTEmisor></Emisor><Receptor><RUTRecep>66666666-6</RUTRecep><RznSocRecep>'.$sell->razon_social.'</RznSocRecep></Receptor>
    <Totales>
    <MntNeto>'.$MontoNeto.'</MntNeto>
    <IVA>'.$PrecioIva.'</IVA>
    <MntTotal>'.$PT.'</MntTotal>
    </Totales>
    </Encabezado>'.$detallesProducts.'
    </Documento>
    </DTE>';

    // Monto neto e iva
    // <MntNeto>'.$PN.'</MntNeto><IVA>'.$ivaPrice.'</IVA>

    return $xml_dte;
  }

  //Generar xml para la nota de credito (factura)
  public static function notaXMLFactura($sell, $folio, $environment_vars, $folioCancelar, $fechaFolio) {
    // Variables de entorno
    $RutEmisor = $environment_vars['sii_rut']['value'];
    $RnzSoc = $environment_vars['sii_rnz_soc']['value'];
    $GirEmis = $environment_vars['sii_giro_emisor']['value'];
    $Acteco = $environment_vars['sii_arteco']['value'];
    $DirOrig = $environment_vars['sii_dirorigen']['value'];
    $CmoOrig = $environment_vars['sii_comuna_origen']['value'];
    $CiudOrig = $environment_vars['sii_ciudad_origen']['value'];
    $ivaAmount = (float) $environment_vars['sii_iva_amount']['value'];

    // Varibles para los calculos y el xml
    $i = 1;
    $PU = 0;
    $PUS = 0;
    $PN = 0;
    $PT = 0;
    $PPT = 0;
    $detallesProducts='';
    $fechaEmis = date('Y-m-d');

    // pivotes de todos los productos
    $ordenProducts = ProductSell::where('sell',$sell->id)->get();

    foreach ($ordenProducts as $key => $value) {
      $value->price = (double) $value->price;
      $product = Product::find($value->product)->name;
      $product = str_replace("ñ","n",$product);
      $product = str_replace("'","",$product);
      //
      $detallesProducts .= '<Detalle>';
      $detallesProducts .= '<NroLinDet>'.$i.'</NroLinDet>';
      $detallesProducts .= '<NmbItem>'.strtoupper($product).'</NmbItem>';
      $detallesProducts .= '<QtyItem>'.$value->quantity.'</QtyItem>';
      $detallesProducts .= '<UnmdItem>UN</UnmdItem>';

      $PU = round(((float) $value->price)); //Precio unitario
      $PUS = round($PU / (($ivaAmount/100) + 1)); //Precio unitario sin iva
      $detallesProducts .= '<PrcItem>'.$PUS.'</PrcItem>';

      $PPT = round($PUS*$value->quantity); // Monto total del producto sin iva
      $detallesProducts .= '<MontoItem>'.$PPT.'</MontoItem>';
      $detallesProducts .= '</Detalle>';

      $PN += round((float) $PPT); // Monto neto

      //$PPT = round($PU*$value->quantity);
      //$PT += round((float) $PPT); // Monto total <- Mal

      $i++;

    }

    $ivaAmount = round($ivaAmount);
    $PT = round($PN * (($ivaAmount/100) + 1)); // Monto total
    $ivaPrice = round($PT - $PN);

    $xml_dte = '<DTE version="1.0">
    <Documento ID="R78276600-7T61F1">
    <Encabezado>
    <IdDoc>
    <TipoDTE>61</TipoDTE>
    <Folio>'.$folio.'</Folio>
    <FchEmis>'.$fechaEmis.'</FchEmis>
    </IdDoc>
    <Emisor>
    <RUTEmisor>'.$RutEmisor.'</RUTEmisor>
    <RznSoc>'.$RnzSoc.'</RznSoc>
    <GiroEmis>'.$GirEmis.'</GiroEmis>
    <Acteco>'.$Acteco.'</Acteco>
    <DirOrigen>'.$DirOrig.'</DirOrigen>
    <CmnaOrigen>'.$CmoOrig.'</CmnaOrigen>
    <CiudadOrigen>'.$CiudOrig.'</CiudadOrigen>
    </Emisor>
    <Receptor>
    <RUTRecep>'.strtoupper($sell->rut).'</RUTRecep>
    <RznSocRecep>'.$sell->razon_social.'</RznSocRecep>
    <GiroRecep>'.$sell->giro.'</GiroRecep>
    <DirRecep>'.$sell->direction.'</DirRecep>
    <CmnaRecep>'.$sell->comuna.'</CmnaRecep>
    <CiudadRecep>'.$sell->city.'</CiudadRecep>
    </Receptor>
    <Totales>
    <MntNeto>'.$PN.'</MntNeto>
    <TasaIVA>'.$ivaAmount.'</TasaIVA>
    <IVA>'.$ivaPrice.'</IVA>
    <MntTotal>'.$PT.'</MntTotal>
    </Totales>
    </Encabezado>
    <Detalle>
    <NroLinDet>1</NroLinDet>
    <NmbItem>ANULA DOCUMENTO DE REFERENCIA</NmbItem>
    <MontoItem>'.$PN.'</MontoItem>
    </Detalle>
    <Referencia>
    <NroLinRef>1</NroLinRef>
    <TpoDocRef>33</TpoDocRef>
    <FolioRef>'.$folioCancelar.'</FolioRef>
    <FchRef>'.$fechaFolio.'</FchRef>
    <CodRef>1</CodRef>
    <RazonRef>ANULA DOCUMENTO DE REFERENCIA</RazonRef>
    </Referencia>
    </Documento>
    </DTE>';

    return $xml_dte;
  }

  //Generar xml para la nota de credito (boleta)
  public static function notaXMLBoleta($sell, $folio, $environment_vars, $folioCancelar, $fechaFolio) {
    // Variables de entorno
    $RutEmisor = $environment_vars['sii_rut']['value'];
    $RnzSoc = $environment_vars['sii_rnz_soc']['value'];
    $GirEmis = $environment_vars['sii_giro_emisor']['value'];
    $Acteco = $environment_vars['sii_arteco']['value'];
    $DirOrig = $environment_vars['sii_dirorigen']['value'];
    $CmoOrig = $environment_vars['sii_comuna_origen']['value'];
    $CiudOrig = $environment_vars['sii_ciudad_origen']['value'];
    $ivaAmount = (float) $environment_vars['sii_iva_amount']['value'];

    // Varibles para los calculos y el xml
    $i = 1;
    $PU = 0;
    $PUS = 0;
    $PN = 0;
    $PT = 0;
    $PPT = 0;
    $detallesProducts='';
    $fechaEmis = date('Y-m-d');

    // pivotes de todos los productos
    $ordenProducts = ProductSell::where('sell',$sell->id)->get();

    foreach ($ordenProducts as $key => $value) {

      $value->price = (double) $value->price;
      $product = Product::find($value->product)->name;
      $product = str_replace("ñ","n",$product);
      $product = str_replace("'","",$product);
      //
      $detallesProducts .= '<Detalle>';
      $detallesProducts .= '<NroLinDet>'.$i.'</NroLinDet>';
      $detallesProducts .= '<NmbItem>'.strtoupper($product).'</NmbItem>';
      $detallesProducts .= '<QtyItem>'.$value->quantity.'</QtyItem>';
      $detallesProducts .= '<UnmdItem>UN</UnmdItem>';


      $PU = round(((float) $value->price)); //Precio unitario con iva
      $PUS = round($PU / (($ivaAmount/100) + 1)); //Precio unitario sin iva
      $detallesProducts .= '<PrcItem>'.$PU.'</PrcItem>';

      $PPT = round($PU*$value->quantity); // Monto total del producto con iva
      $PPTI = round($PUS*$value->quantity); // Monto total del producto sin iva
      $detallesProducts .= '<MontoItem>'.$PPT.'</MontoItem>';
      $detallesProducts .= '</Detalle>';

      $PN += round((float) $PPTI); // Monto neto
      $PT += round((float) $PPT); // Monto total

      $i++;

    }

    //$PN = round($PT / (($ivaAmount/100) + 1)); <- Descomentar si el monto Neto no coincide con el Monto total sin iva (error que tuvimos con facturas el 30112020)

    // $PT = round($PN * (($ivaAmount/100) + 1)); // Monto total

    $ivaAmount =  round($ivaAmount);
    // Neto = Total (PT) - 19%0
    $MontoNeto =  round($PT/(($ivaAmount/100) + 1));
    // Precio Iva = Total - Monto Neto
    $PrecioIva = $PT - $MontoNeto;

    $xml_dte = '<DTE version="1.0">
    <Documento ID="R78276600-7T61F1">
    <Encabezado>
    <IdDoc>
    <TipoDTE>61</TipoDTE>
    <Folio>'.$folio.'</Folio>
    <FchEmis>'.$fechaEmis.'</FchEmis>
    </IdDoc>
    <Emisor>
    <RUTEmisor>'.$RutEmisor.'</RUTEmisor>
    <RznSoc>'.$RnzSoc.'</RznSoc>
    <GiroEmis>'.$GirEmis.'</GiroEmis>
    <Acteco>'.$Acteco.'</Acteco>
    <DirOrigen>'.$DirOrig.'</DirOrigen>
    <CmnaOrigen>'.$CmoOrig.'</CmnaOrigen>
    <CiudadOrigen>'.$CiudOrig.'</CiudadOrigen>
    </Emisor>
    <Receptor>
    <RUTRecep>66666666-6</RUTRecep>
    <RznSocRecep>SIN RAZON SOCIAL</RznSocRecep>
    <GiroRecep>SIN GIRO COMERCIAL</GiroRecep>
    <DirRecep>SIN DIRECCION</DirRecep>
    <CmnaRecep>SIN COMUNA</CmnaRecep>
    <CiudadRecep>SIN CIUDAD</CiudadRecep>
    </Receptor>
    <Totales>
    <MntNeto>'.$MontoNeto.'</MntNeto>
    <TasaIVA>'.$ivaAmount.'</TasaIVA>
    <IVA>'.$PrecioIva.'</IVA>
    <MntTotal>'.$PT.'</MntTotal>
    </Totales>
    </Encabezado>
    <Detalle>
    <NroLinDet>1</NroLinDet>
    <NmbItem>ANULA DOCUMENTO DE REFERENCIA</NmbItem>
    <MontoItem>0</MontoItem>
    </Detalle>
    <Referencia>
    <NroLinRef>1</NroLinRef>
    <TpoDocRef>39</TpoDocRef>
    <FolioRef>'.$folioCancelar.'</FolioRef>
    <FchRef>'.$fechaFolio.'</FchRef>
    <CodRef>1</CodRef>
    <RazonRef>ANULA DOCUMENTO DE REFERENCIA</RazonRef>
    </Referencia>
    </Documento>
    </DTE>';

    return $xml_dte;
  }
  
  // ---------------------------------------------------REPOSTERIA---------------------------------------------------
  // genera folio factura
    public static function facturaPedidorReposteriaXML_con_Data($pedido, $folio, $environment_vars, $data) {
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
  
        // if ($montoEmergencia > 0) {
        //     $montoSinIVA = round(bcdiv($montoEmergencia, bcadd('1.00', bcdiv($ivaAmount, '100', 2), 2), 2));
        //     $subtotal = bcadd($subtotal, $montoSinIVA, 2);
  
        //     $XML_DETALLE .= Self::DetallePedido([
        //         'nombre' => 'Emergencia',
        //         'i' => $i,
        //         'cantidad' => 1,
        //         'precio' => $montoSinIVA,
        //         'total' => $montoSinIVA
        //     ]);
        //     $i++;
        // }
  
        // No añadir $pedido->subtotal de nuevo si ya se incluye en el subtotal calculado arriba
        // Si es necesario añadir, usa:
        // $subtotal = bcadd($subtotal, $pedido->subtotal, 2);
  

        $ivaPrice = round($subtotal*($ivaAmount/100)); // Monto del IVA del pedido
                
        $total = $subtotal + $ivaPrice; // Monto total

        $XML_TOTALES = Self::Totales([
            'tipo' => 33,
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
  
        $XML_REFERENCIA = Self::Referencia(['comentario'=>$data['comment'],
                                    'fecha_emision'=>$data['fecha_emision'],
                                    'documento_referencia'=>$data['documento_referencia']]);
  
  
        return Self::DTE(['ID'=>'F437T33',
        'tipo'=>33,
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
        // 'subtotal'=>$PN,
        // 'iva_tasa'=>$ivaAmount,
        // 'iva_costo'=>$ivaPrice,
        // 'total'=>$PT,
        'XML_REFERENCIA'=>$XML_REFERENCIA,
        'XML_DETALLE'=>$XML_DETALLE,
        'XML_TOTALES'=>$XML_TOTALES,
        'nro_transaccion' => $data['nro_transaccion']]);
  
      // FIN DEL PEDIDO EMERGENCIA
      
    }
}
