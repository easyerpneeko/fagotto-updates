
<!DOCTYPE html>
<?php
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

  $imgLogo = null;

  if (isset($sell['envs']) && isset($sell['envs']->sii_logo) && isset($sell['envs']->sii_logo->value)) {
    if ($sell['envs']->sii_logo->value)
      $imgLogo = '../storage/app/'.$sell['envs']->sii_logo->value;
  }

?>
<html lang="en" dir="ltr">

  <head>
    <meta charset="utf-8">
    <title></title>
  </head>

  <body class="body-boleta">
  <table class="body-texts-table">
    

  <tr>
@if ($imgLogo)
<td style="width:30%">
  <div class="header-logo mb-1">
    <center>
      <img src="{{ $imgLogo }}" class="the-image-logo">
    </center>
  </div>
</td>
@endif;
  
  <td style="width:70%">

    <div class="header-box text-bold">
      <p class="text-spacing-3 header-title text-center text-uppercase mb-1 fs12 text-bold">
      R.U.T.: {{$sell['envs']->sii_emisor_rut->value}}
      </p>
  </div>

  <p class="text-spacing-3 header-title-1 text-center text-uppercase fs12 text-bold">
    NO VALIDO COMO BOLETA ELECTRONICA
  </p>

  </td>

</tr>


</table>



<p class="text-spacing-3 header-title text-center text-uppercase w-100 fs14 text-bold ">
    {{$sell['envs']->sii_rnz_soc->value}}
</p>
<br>
<table class="body-texts-table">


    <tr>
      <td class="text-uppercase fs7">Giro</td>
      <td class="text-left fsDfl">{{$sell['envs']->sii_giro_emisor->value}}</td>
    </tr>

    <tr>
      <td class="text-uppercase fs7">Direccion</td>
      <td class="text-left fsDfl">{{$sell['envs']->sii_dirorigen->value}}</td>
    </tr>
  
    <tr>
      <td class="text-uppercase fs7">Comuna</td>
      <td class="text-left fsDfl">{{$sell['envs']->sii_comuna_origen->value}}</td>
    </tr>

    <tr>
    <td class="text-uppercase fs7">Contacto</td>
      <td class="text-left fsDfl">{{$sell['envs']->stgg_contacto->value}}</td>
    </tr>

</table>

  <hr style="width:50%;margin: auto;">

<hr>
<table class="body-texts-table">
      <!-- titulo de tabla -->
      <tr>
        
        <td class="text-uppercase text-height-titles text-bold header-title">NOMBRE</td>
        <td class="text-uppercase text-height-titles text-bold header-title">PRECIO</td>
        <td class="text-uppercase text-height-titles text-bold header-title">CANT.</td>
        <td class="text-uppercase text-height-titles text-bold header-title">TOTAL</td>
      </tr>
      <?php
        $totales = 0;
      ?>
      
      <?php foreach ($sell['products'] as $product) { ?>
        <tr>
          
          <td class="text-uppercase">{{$product->name}}</td>
          <td class="text-right">{{ formatoChilenoBoleta($product->unitary_price, 0) }}</td>
          <td class="text-right">{{ $product->quantity }}</td>
          <td class="text-right">{{ formatoChilenoSubBoletaSimilar($product->quantity, $product->totalPrice) }}</td>
          @if(!empty($product->comment))
            <tr>
              <td colspan="4" class="fs14"> <small>{{$product->comment}}</small></td>
            </tr>
          @endif
          <?php
            // ayudar al calculo de la propina
            $totales += ($product->unitary_price*$product->quantity);
          ?>
        </tr>
      <?php } ?>
    </table>
      <hr>

  <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 fs12 text-bold">
    Subtotal: ${{ $totales }}
</p>  
    <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 fs12 text-bold">
      <?php
        
        $propina = (100*$sell['tip'])/$totales;
      ?>
        Propina: <span class="ml-3 fs12">${{ formatoChilenoBoleta($sell['tip'], 0) }} ({{ number_format($propina) }}%)</span>
    </p>
    <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 fs12 text-bold">
      
        Descuento: <span class="ml-3 fs12">${{ number_format($sell['discount']) }}</span>
    </p>
    <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 fs12 text-bold">
        TOTAL: <span class="ml-3 fs12">${{ formatoChilenoBoleta($sell['total'] - $sell['discount']) }}</span>
    </p>
    <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 fs12 text-bold">
        TIPO: <span class="ml-3 fs12">{{ $sell['paymode'] }}</span>
    </p>
    

    </p>
  </body>
</html>
<style media="screen">
@font-face {
  font-family: arial-black;
  src: url({{ storage_path('fonts\arial-black.ttf') }}) format("truetype");
}
*{
  margin: 0;
  margin: 6px 2px;
  padding: 0px;
  word-wrap: break-word !important;
  word-break: break-all !important;
  font-size: 7.25px;
  letter-spacing: 0.8px !important;
  font-weight: bold;
}
/*.body-texts-table {
  font-size: 7.25px;
}*/
p{
  margin: 2px !important;
}
.box-fhater{
  margin: 0px !important;
  padding: 0px !important;
}
.the-image-logo {
  max-width: 200px;
  height: auto;
}
.header-box{
  border: 4px solid #000;
  padding-top: 5px;
}
.header-box-without-padding {
  border: 4px solid #000;
}
.header-logo{
  /*padding-top: 5px;*/
}
.fs12{
  font-size: 11px !important;
}
.fs14{
  font-size: 14px !important;
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
.text-spacing-3{
  letter-spacing: 0.3px !important;
}
.header-title{
  margin: 0px !important;
  margin-bottom: 3px !important;
}

.header-title-1 {
  margin: 0px !important;
  margin-bottom: 6px !important;
}

.header-title-2 {
  margin: 0px !important;
  margin-bottom: 4px !important;
  font-size: 7.5px !important;
}
/* hr style */
.hr-style-mb{
  border: 0.8px solid #000 !important;
  margin-bottom: 8px;
}
.hr-style{
  border: 0.8px solid #000 !important;
  margin: 0px !important;
}
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

.body-boleta {
  /*background: red;*/
  padding-left: 0px;
  padding-right: 25px;
}
</style>
