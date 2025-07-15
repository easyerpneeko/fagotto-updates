<!DOCTYPE html>
<?php
use Picqer\Barcode\BarcodeGeneratorPNG;
// This will output the barcode as HTML output to display in the browser
$generator = new BarcodeGeneratorPNG();

function formatoChilenoSub($cantidad,$numero, $decimales = null) {

  $numeroFinal = number_format($numero);

  if ($decimales) $numeroFinal = number_format($numero, $decimales);

  $numeroFinal = (float) $cantidad * (float) $numero;

  // FORMATO CHILENO
  // Coma por Pivote
  $numeroFinal = str_replace(',','%coma%',  $numeroFinal);
  // Punto por Coma
  $numeroFinal = str_replace('.',',',       $numeroFinal);
  // Pivote por Punto
  $numeroFinal = str_replace('%coma%','.',  $numeroFinal);

  return $numeroFinal;

}
function formatoChileno($numero, $decimales = null) {

  $numeroFinal = number_format($numero);

  if ($decimales) $numeroFinal = number_format($numero, $decimales);

  // FORMATO CHILENO

  // Coma por Pivote
  $numeroFinal = str_replace(',','%coma%',  $numeroFinal);
  // Punto por Coma
  $numeroFinal = str_replace('.',',',       $numeroFinal);
  // Pivote por Punto
  $numeroFinal = str_replace('%coma%','.',  $numeroFinal);

  return $numeroFinal;

}

function formatoChilenoBoleta($numero, $decimales = null) {

    $numeroFinal = number_format($numero);

    if ($decimales)
      $numeroFinal = number_format($numero, $decimales);

    // FORMATO CHILENO

    // Coma por Pivote
    $numeroFinal = str_replace(',','%coma%',  $numeroFinal);
    // Punto por Coma
    $numeroFinal = str_replace('.',',',       $numeroFinal);
    // Pivote por Punto
    $numeroFinal = str_replace('%coma%','.',  $numeroFinal);

    return $numeroFinal;

  }

function formatoChilenoSubBoletaSimilar($cantidad,$numero, $decimales = null) {

    $numeroFinal = number_format($numero);

    if ($decimales) $numeroFinal = number_format($numero, $decimales);

    $numeroFinal = (float) $cantidad * (float) $numero;

    // FORMATO CHILENO
    // Coma por Pivote
    $numeroFinal = str_replace(',','%coma%',  $numeroFinal);
    // Punto por Coma
    $numeroFinal = str_replace('.',',',       $numeroFinal);
    // Pivote por Punto
    $numeroFinal = str_replace('%coma%','.',  $numeroFinal);

    return $numeroFinal;

  }


  $imgLogo = null;

  if (isset($order['envs']) && isset($order['envs']->sii_logo) && isset($order['envs']->sii_logo->value)) {
    if ($order['envs']->sii_logo->value)
      $imgLogo = '../storage/app/'.$order['envs']->sii_logo->value;
  }

?>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>ticket</title>
  </head>
  <body class="body-ticket">
      <p class="fontZiseOld text-spacing-3 header-title-1 text-center text-uppercase text-bold">
          Resumen de la orden
      </p>

<table class="body-texts-table">
    

<tr>
@if ($imgLogo)
<td style="width:10%">
    <center>
      <img src="{{ $imgLogo }}" class="the-image-logo">
    </center>
  </div>
</td>
@endif;
  
  <td style="width:70%">

    <div class="header-box text-bold">
      <p class="text-spacing-3 header-title text-center text-uppercase mb-1 fs12 text-bold">
          R.U.T.: {{$order['envs']->sii_emisor_rut->value}}
      </p>
  </div>

  <p class="text-spacing-3 header-title-1 text-center text-uppercase fs12 text-bold">
    NO VALIDO COMO BOLETA ELECTRONICA
  </td>
</tr>
</table>
<p class="text-spacing-3 header-title text-center text-uppercase w-100 fs14 text-bold ">
    {{$order['envs']->sii_rnz_soc->value}}
</p>
<div>
  <p class="text-uppercase mb-0 fsDfl">
    Fecha de emision: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{DateTime::createFromFormat("Y-m-d H:i:s",$order['created_at'])->format("d-m-Y")}}
  </p>
  @if($order['waiter'] && $order['board'])
  <p class="text-uppercase mb-0 fsDfl">
    Mesero: {{ $order['waiter']->name }}
  </p>
  <p class="text-uppercase mb-0 fsDfl">
    Mesa: {{ $order['board']->name  }}
  </p>
  @if(!empty($order['client_ticket']))
    <p class="text-uppercase mb-2 fsDfl">
      Cliente: {{ $order['client_ticket']  }}
    </p>
  @endif
  @endif
</div>
<hr>
<table class="body-texts-table">
  <!-- titulo de tabla -->
  <tr>
    
    <td class="text-uppercase text-height-titles text-bold header-title fsDfl">NOMBRE</td>
    <td class="text-uppercase text-height-titles text-bold header-title fsDfl">CANT.</td>
    <td class="text-uppercase text-height-titles text-bold header-title fsDfl">TOTAL</td>
  </tr>
  <?php
    $totales = 0;
  ?>
  
  <?php foreach ($order['products'] as $product) { ?>
    <tr>
      
      <td class="text-uppercase fsDfl">{{$product->name}}</td>
      <td class="text-right fsDfl">{{ $product->quantity }}</td>
      <td class="fontZiseOld text-right">{{ formatoChileno($product->subtotal, 0) }}</td>
      @if(!empty($product->comment))
        <tr>
          <td colspan="3" class="fs14"> <small>{{$product->comment}}</small></td>
        </tr>
      @endif
    </tr>
    <?php
      $totales += $product->subtotal;
    ?>
  <?php } ?>
</table>
  <hr>
  <p class="text-spacing-0 header-title text-right text-uppercase mb-1 mt-1 fs16 text-bold">
    Subtotal: ${{ $totales }}
</p>  
<p class="text-spacing-0 header-title text-right text-uppercase mb-1 mt-1 fs16 text-bold">
  <?php
    $propina = (100*$order['tip'])/$order['total'];
  ?>
    Propina: <span class="ml-3 fs12">${{ formatoChileno($order['tip'], 2) }} ({{ number_format($propina) }}%)</span>
</p>
<p class="text-spacing-0 header-title text-right text-uppercase mb-1 mt-1 fs16 text-bold">
    TOTAL: <span class="ml-3 fs16">${{ formatoChileno($order['total']+$order['tip'], 0) }}</span>
</p>

@if($order['ticket_description'])
        <p class="text-spacing-0 text-uppercase mb-1 mt-1 description-padding">
          <label>Descripcion</label> <br /> 
          <span class="fontZiseSmall">{{ $order['description'] }}</span>
        </p>
      @endif
      @if(!$order['no_code_bar'])
        <p class="fontZiseOld text-spacing-3 header-title text-center text-uppercase mb-1 mt-1">
          <?php
          echo '<img class="barcode" src="data:image/png;base64,' . base64_encode(
            $generator->getBarcode($order['barcode'], $generator::TYPE_UPC_A)
            ) . '">';
          ?>
        </p>
      @endif
  </body>
</html>
<style media="screen">
  .fontZiseOld{
    font-size: 12.5px;
  }
  .description-padding {
    padding-left: 12px;
  }
  .fontZiseSmall{
    font-size: 15px;
    font-weight: normal;
  }
  * {
    margin: 0;
    margin: 6px 2px;
    padding: 0px;
    word-wrap: break-word !important;
    word-break: break-all !important;
    letter-spacing: 0.8px !important;
    font-size: 17px;
    font-weight: bold;
  }
  .barcode{
    width: 100%;
  }
  p{
    margin: 2px !important;
  }
  .box-fhater{
    margin: 0px !important;
    padding: 0px !important;
  }
  .header-box{
    border: 4px solid #000;
    padding-top: 5px;
  }
  /* styles table */
  .text-bold{
    font-weight: bold !important;
  }
  .w-100{
    width: 100% !important;
  }
  .img-code{
    width: 90% !important;
  }
  body, table, p, div{
    font-family: sans-serif !important;
  }
  table {
    border-collapse: collapse;
    width: 100%;
  }
  th, td {
    text-align: left;
    /*border-bottom: 0.8px solid #c4c4c4;*/
  }
  td {
    padding: 3px;
  }
  th {
    padding: 5px;
    padding-top: 3px;
  }
  /* font styles */
  .text-height-titles{
    line-height: 8.5px;
    margin-bottom: 0px;
  }
  .text-center{
    text-align: center !important;
  }
  .text-right{
    text-align: right !important;
  }
  .text-justify{
    text-align: justify !important;
  }
  .text-uppercase{
    text-transform: uppercase !important;
  }
  .text-capitalize{
    text-transform: capitalize !important;
  }
  .text-spacing-3{
    letter-spacing: 0.3px !important;
  }
  .header-title{
    margin: 0px !important;
    margin-bottom: 3px !important;
  }
  .header-title-1{
    margin: 0px !important;
    margin-bottom: 6px !important;
  }
  /* hr style */
  .pl-1{
    padding-left: 10px !important;
  }
  .pl-2{
    padding-left: 20px !important;
  }
  .pl-3{
    padding-left: 30px !important;
  }
  .pr-1{
    padding-left: 10px !important;
  }
  /* margins */
  .mt-1{
    margin-top: 10px !important;
  }
  .mt-2{
    margin-top: 20px !important;
  }
  .mt-2-5{
    margin-top: 25px !important;
  }
  .mt-3{
    margin-top: 30px !important;
  }
  .mt-4{
    margin-top: 40px !important;
  }
  .mt-5{
    margin-top: 50px !important;
  }
  .mb-1{
    margin-bottom: 10px !important;
  }
  .mb-2{
    margin-bottom: 20px !important;
  }
  .mb-3{
    margin-bottom: 30px !important;
  }
  .mb-4{
    margin-bottom: 40px !important;
  }
  .mb-5{
    margin-bottom: 50px !important;
  }
  .ml-1{
    margin-left: 10px !important;
  }
  .ml-2{
    margin-left: 20px !important;
  }
  .ml-3{
    margin-left: 30px !important;
  }
  .ml-4{
    margin-left: 40px !important;
  }
  .ml-5{
    margin-left: 50px !important;
  }
  .mr-1{
    margin-right: 10px !important;
  }
  .mr-2{
    margin-right: 20px !important;
  }
  .mr-3{
    margin-right: 30px !important;
  }
  .mr-4{
    margin-right: 40px !important;
  }
  .mr-5{
    margin-right: 50px;
  }
  .m-1{
    margin: 10px !important;
  }
  .m-2{
    margin: 20px !important;
  }
  .m-3{
    margin: 30px !important;
  }
  .m-4{
    margin: 40px !important;
  }
  .m-5{
    margin: 50px !important;
  }
  .body-ticket {
    padding-left: 0px;
    padding-right: 25px;
  }
  .fs7{
    font-size: 7px !important;
  }
  .fs12{
  font-size: 9px !important;
    }
    .fs14{
    font-size: 12px !important;
    }
    .fs16{
        font-size: 14px !important;
    }
    .fsDfl{
        font-size: 10px !important; 
    }
</style>
