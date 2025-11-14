<!DOCTYPE html>
<?php
  function deFormatoChilenoAddtionBoleta($cantidad,$numero, $decimales = null) {

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
  function formatoChilenoAddtionBoleta($numero, $decimales = null) {

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

?>
<html lang="en" dir="ltr">

  <head>
    <meta charset="utf-8">
    <title></title>
  </head>

  <body class="body-boleta">
    <div class="header-box text-bold">
      <p class="text-spacing-3 header-title text-center text-uppercase mb-1 fs12 text-bold">
          R.U.T.: {{$data['envs']->sii_emisor_rut->value}}
      </p>
      <p class="text-spacing-3 header-title text-center text-uppercase mb-1 fs12 text-bold">
          {{$data['app_name']}}
      </p>
      <p class="text-spacing-3 header-title text-center text-uppercase mb-1 fs12 text-bold">
          N° {{$data['id']}}
      </p>
    </div>
    <p class="text-spacing-3 header-title-1 text-center text-uppercase fs12 text-bold">
        NO VALIDO COMO BOLETA
    </p>
    <p class="text-spacing-3 header-title text-center text-uppercase w-100">
        {{$data['envs']->sii_rnz_soc->value}}
    </p>
    <p class="text-spacing-3 header-title-2 text-center text-uppercase w-100">
        {{$data['envs']->sii_giro_emisor->value}}
    </p>
    <p class="text-spacing-3 header-title text-center text-uppercase w-100">
        {{$data['envs']->sii_dirorigen->value}}
    </p>
    <p class="text-spacing-3 header-title text-center text-uppercase w-100  mb-1">
        {{$data['envs']->sii_comuna_origen->value}}
    </p>
    <div>
      <p class="text-uppercase mb-2">
        Fecha de emision: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{DateTime::createFromFormat("Y-m-d H:i:s",$data['created_at'])->format("Y-m-d")}}
      </p>
    </div>
    <table class="body-texts-table">
      <!-- titulo de tabla -->
      <tr>
        <td class="text-uppercase text-height-titles">CODIGO</td>
        <td class="text-uppercase text-height-titles">TIPO</td>
        <td class="text-uppercase text-height-titles">CANT.</td>
        <td class="text-uppercase text-height-titles">TOTAL</td>
      </tr>
      <tr>
        <td>AAAAAA</td>
        <td class="text-uppercase">{{ (isset($data['waiter']) && isset($data['waiter']->name)) ? $data['waiter']->name : 'Venta rapida' }}</td>
        <td class="text-uppercase">{{$data->quantity}}</td>
        <td class="text-right">${{ formatoChilenoAddtionBoleta($data->balance, 2) }}</td>
      </tr>
    </table>
    <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 fs12 text-bold">
        TOTAL: <span class="ml-3 fs12">${{ formatoChilenoAddtionBoleta($data->balanceTotal, 2) }}</span>
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
  .header-box{
    border: 4px solid #000;
    padding-top: 5px;
  }
  .fs12{
    font-size: 11px !important;
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
