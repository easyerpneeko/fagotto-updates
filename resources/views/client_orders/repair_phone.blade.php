<!DOCTYPE html>
<?php

use Illuminate\Support\Facades\Log;
use App\Helpers\CurrentApp;

function formatoChilenoSubCustom($numero, $decimales = null) {

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
/*
id : 5000
hacer correo con los datos ingresados de la persona :
el correo debe tener
Datos del Cliente :
----------------
Modelo select seleciono del equipo
falla :
Equipo enciende   x
huella  x
microfono  x
--------------------------*/

/*Log::info($order);
$order = json_decode(json_encode($order), 1);/*json_decode(json_encode($order), 1);*/
/*Log::info($order);*/

?>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Orden de reparacion</title>
  </head>
  <body class="body-ticket">

      <p class="fontZiseOld text-spacing-3 header-title-1 text-center text-uppercase text-bold">
          Resumen de la orden
      </p>
      <p class="text-spacing-3 header-title text-center text-uppercase">
          {{ $order->envs->stgg_header->value }}
      </p>
      <hr />
      <div>
        <span class="fontZiseOld text-uppercase">
          <b class="fontZiseOld">ID: </b>
          {{ $order->id }}
        </span><br />
        <span class="fontZiseOld text-uppercase">
          <b class="fontZiseOld">Fecha: </b>
          {{ DateTime::createFromFormat("Y-m-d H:i:s",$order->created_at)->format("Y/m/d H:i:s") }}
        </span><br />
        <hr />
        <span class="fontZiseOld text-uppercase">
          RUT: {{ $order->order->client->rut }}
        </span><br />
        <span class="fontZiseOld text-uppercase">
          Nombre: {{ $order->order->client->name }} {{ $order->order->client->lastname }}
        </span><br />
        @if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono'))
          <span class="fontZiseOld text-uppercase">
            Telefono: {{ $order->contact_phone }}
          </span><br />
        @endif
        <span class="fontZiseOld text-uppercase">
          Correo: {{ $order->contact_email }}
        </span><br />
        <hr />
        <span class="fontZiseOld text-uppercase">
          IMEI: {{ $order->device_imei }}
        </span><br />
        <span class="fontZiseOld text-uppercase">
          Falla: {{ $order->device_failure }}
        </span><br />
        <span class="fontZiseOld text-uppercase">
          Estado del equipo:
          <ul>
            <?php if ($order->device_condition && $order->device_condition !== ''): ?>
              <?php
                $statuses = [];
                if (
                    $order->envs
                    && $order->envs->client_orders_device_conditions
                    && $order->envs->client_orders_device_conditions->value)
                  $statuses = json_decode($order->envs->client_orders_device_conditions->value, 1);
               ?>
              <?php foreach ($statuses as $key => $value): ?>
                <?php if (in_array($key, json_decode( $order->device_condition, 1))): ?>
                  <li><span class="fontZiseOld text-uppercase">{{ $value }}</span></li>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </span><br />
        <hr />
        <span class="fontZiseOld text-uppercase">
          Presupuesto: ${{ formatoChilenoSubCustom($order->budget, 2) }}
        </span><br />
        @if ($order->technician_id && $order->technician)
          <span class="fontZiseOld text-uppercase">
            Tecnico: {{ $order->technician->fullname }}
          </span><br />
        @endif
      </div>

  </body>
</html>
<style media="screen">
  .fontZiseOld{
    font-size: 12.5px;
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
    padding-left: 25px;
    padding-right: 25px;
  }
</style>
