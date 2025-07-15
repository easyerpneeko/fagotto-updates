<!DOCTYPE html>
<?php

use Illuminate\Support\Facades\Log;
use App\Helpers\CurrentApp;

function formatoChilenoSubCustomEmail($numero, $decimales = null) {

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
<!DOCTYPE html>
<html lang="es" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ORDEN DE REPARACION</title>
    <style media="screen">
      * {
        margin: 0px;
        padding: 0px;
        font-family: helvetica, arial, sans-serif;
      }
      /*.bg-primary{
        background-color: #336699;
        width: 100%;
        height: 250px;
        padding-top: 50px;
      }
      .bg-secondary{
        background-color: #f4f4f4;
        width: 100%;
        height: 600px;
      }
      .card{
        background: #fff;
        width: 100%;
        max-width: 600px;
        margin: 0px auto;
      }*/
      p{
        /*color: #666;*/
        font-size: 18px;
        margin: 10px 0px;
      }
      h2{
        padding-bottom: 10px;
      }
      /*.pa{
        padding: 20px 25px;
      }
      .img-pa{
        padding: 20px 25px 0px 25px;
      }*/
    </style>
  </head>
  <body>
    <div class="bg-secondary">
      <div class="bg-primary">
        <div class="card">
          <div class="pa">
            <p class="fontZiseOld text-spacing-3 header-title-1 text-center text-uppercase text-bold">
                RESUMEN DE LA ORDEN
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
                <b class="fontZiseOld">FECHA: </b>
                {{ DateTime::createFromFormat("Y-m-d H:i:s",$order->created_at)->format("Y/m/d H:i:s") }}
              </span><br />
              <hr />
              <span class="fontZiseOld text-uppercase">
                RUT: {{ $order->order->client->rut }}
              </span><br />
              <span class="fontZiseOld text-uppercase">
                NOMBRE: {{ $order->order->client->name }} {{ $order->order->client->lastname }}
              </span><br />
              @if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono'))
                <span class="fontZiseOld text-uppercase">
                  TELEFONO: {{ $order->contact_phone }}
                </span><br />
              @endif
              <span class="fontZiseOld text-uppercase">
                CORREO: {{ $order->contact_email }}
              </span><br />
              <hr />
              <span class="fontZiseOld text-uppercase">
                IMEI: {{ $order->device_imei }}
              </span><br />
              <span class="fontZiseOld text-uppercase">
                Falla: {{ $order->device_failure }}
              </span><br />
              <span class="fontZiseOld text-uppercase">
                ESTADO DEL EQUIPO:
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
                PRESUPUESTO: ${{ formatoChilenoSubCustomEmail($order->budget, 2) }}
              </span><br />
              @if ($order->technician_id && $order->technician)
                <span class="fontZiseOld text-uppercase">
                  TECNICO: {{ $order->technician->fullname }}
                </span><br />
              @endif
            </div>
            <hr />
            <p>
              <br />
              Emitido por Easy ERP, Software de gestion basado en Cloud.
            </p>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
