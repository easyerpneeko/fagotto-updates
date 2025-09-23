<?php

  function formatoChilenoReporte1($numero, $decimales = null) {

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
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <p class="text-spacing-3 header-title text-center text-uppercase business-name">{{$data['envs']->stgg_header->value}}</p>
    <p class="text-spacing-3 header-subtitle text-center text-uppercase">Reporte de Ventas</p>
    <hr class="hr-style-mb mt-2-5">
    <div>
      <p class="text-uppercase">Fecha Inicial: {{DateTime::createFromFormat("Y-m-d H:i:s",$data['dates']['startDate'])->format("d/m/Y H:i:s")}}</p>
      <p class="text-uppercase">Fecha Final:  {{DateTime::createFromFormat("Y-m-d H:i:s",$data['dates']['endDate'])->format("d/m/Y H:i:s")}}</p>
    </div>

    <hr class="hr-style">

    <!-- tabla general -->
    <table>
      <!-- titulo de tabla -->
      <tr>
        <th>ID</th>
        <th>MONTO</th>
      </tr>
      <!-- titulo de tabla -->
      <?php if(isset($data['counters']['init_money'])) { ?>
        <tr>
          <td>Monto inicial</td>
          <td>${{ formatoChilenoReporte1($data['counters']['init_money']) }}</td>
        </tr>
      <?php } ?>
      <tr>
        <td>Total</td>
        <td>${{ formatoChilenoReporte1($data['counters']['balanceTotal']) }}</td>
      </tr>
      <tr>
        <td>Ganancia total</td>
        <td>${{ formatoChilenoReporte1($data['counters']['gananciaTotal']) }}</td>
      </tr>
      <?php if(isset($data['counters']['expenses_day'])) { ?>
        <tr>
          <td>Gastos del día</td>
          <td>${{ formatoChilenoReporte1($data['counters']['expenses_day']) }}</td>
        </tr>
        <tr>
          <td>Total - Gastos</td>
          <td>${{ formatoChilenoReporte1($data['counters']['totalToExpenses']) }}</td>
        </tr>
      <?php } ?>
    </table>
    <!-- tabla general -->

    <hr class="hr-style">

    <!-- datos de contadores -->
    <div class="text-center">
      <p>Contadores</p>
    </div>
    <table>
      <!-- titulo de tabla -->
      <tr>
        <th>Contador</th>
        <th>Valor</th>
      </tr>
      <!-- titulo de tabla -->
      <?php if(isset($data['counters']['orders'])) { ?>
        <tr>
          <td>N* Clientes Atendidos</td>
          <td>{{$data['counters']['orders']}}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['quantityTotal'])) { ?>
        <tr>
          <td>Total Productos Vendidos</td>
          <td>{{$data['counters']['quantityTotal']}}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['typeProducts'])) { ?>
        <tr>
          <td>Tipos de productos</td>
          <td>{{$data['counters']['typeProducts']}}</td>
        </tr>
      <?php } ?>
      <!-- 1. Efectivo -->
      <tr>
        <td>Efectivo</td>
        <td>${{ formatoChilenoReporte1($data['counters']['boleta']) }}</td>
      </tr>
      <!-- 2. Débito -->
      <?php if(isset($data['counters']['debito'])) { ?>
        <tr>
          <td>Débito</td>
          <td>${{ formatoChilenoReporte1($data['counters']['debito']) }}</td>
        </tr>
      <?php } ?>
      <!-- 3. Crédito -->
      <?php if(isset($data['counters']['credito'])) { ?>
        <tr>
          <td>Crédito</td>
          <td>${{ formatoChilenoReporte1($data['counters']['credito']) }}</td>
        </tr>
      <?php } ?>
      <!-- 4. Transferencias -->
      <?php if(isset($data['counters']['transferencia'])) { ?>
        <tr>
          <td>Transferencias</td>
          <td>${{ formatoChilenoReporte1($data['counters']['transferencia']) }}</td>
        </tr>
      <?php } ?>
      <!-- 5. Pluxee -->
      <?php if(isset($data['counters']['pluxee'])) { ?>
        <tr>
          <td>Pluxee</td>
          <td>${{ formatoChilenoReporte1($data['counters']['pluxee']) }}</td>
        </tr>
      <?php } ?>
      <!-- 6. Junaeb -->
      <?php if(isset($data['counters']['junaeb'])) { ?>
        <tr>
          <td>Junaeb</td>
          <td>${{ formatoChilenoReporte1($data['counters']['junaeb']) }}</td>
        </tr>
      <?php } ?>
      <!-- 7. Edenred -->
      <?php if(isset($data['counters']['edenred'])) { ?>
        <tr>
          <td>Edenred</td>
          <td>${{ formatoChilenoReporte1($data['counters']['edenred']) }}</td>
        </tr>
      <?php } ?>
      <!-- 8. Amipass -->
      <?php if(isset($data['counters']['amipass'])) { ?>
        <tr>
          <td>Amipass</td>
          <td>${{ formatoChilenoReporte1($data['counters']['amipass']) }}</td>
        </tr>
      <?php } ?>
      <!-- 9. Banco Chile 20% -->
      <?php if(isset($data['counters']['banco_chile_20'])) { ?>
        <tr>
          <td>Banco Chile 20%</td>
          <td>${{ formatoChilenoReporte1($data['counters']['banco_chile_20']) }}</td>
        </tr>
      <?php } ?>
      <!-- 10. Uber Eats -->
      <?php if(isset($data['counters']['uber'])) { ?>
        <tr>
          <td>Uber Eats</td>
          <td>${{ formatoChilenoReporte1($data['counters']['uber']) }}</td>
        </tr>
      <?php } ?>
      <!-- 11. Pedidos Ya -->
      <?php if(isset($data['counters']['pedidos_ya'])) { ?>
        <tr>
          <td>Pedidos Ya</td>
          <td>${{ formatoChilenoReporte1($data['counters']['pedidos_ya']) }}</td>
        </tr>
      <?php } ?>
      <!-- 12. Rappi -->
      <?php if(isset($data['counters']['rappi'])) { ?>
        <tr>
          <td>Rappi</td>
          <td>${{ formatoChilenoReporte1($data['counters']['rappi']) }}</td>
        </tr>
      <?php } ?>
      <!-- Convenio Empresa (único método adicional) -->
      <?php if(isset($data['counters']['convenio_empresa'])) { ?>
        <tr>
          <td>Convenio Empresa</td>
          <td>${{ formatoChilenoReporte1($data['counters']['convenio_empresa']) }}</td>
        </tr>
      <?php } ?>
    </table>
    <!-- datos de contadores -->
    <hr class="hr-style">

    <!-- Gastos del dia  -->
    @if($data['expenses'])
      <div class="text-center">
        <p>Gastos del día</p>
      </div>
      <table>
        <!-- titulo de tabla -->
        <tr>
          <th>Nombre</th>
          <th>Total.</th>
        </tr>
        <!-- titulo de tabla -->
        <?php foreach ($data['expenses'] as $expense) {
          echo
          "<tr>
            <td>".$expense->name."</td>
            <td>$".formatoChilenoReporte1($expense->balance)."</td>
          </tr>";
        } ?>
      </table>
    @endif
    <!-- Gastos del dia  -->
    <hr class="hr-style">

    <hr class="hr-style mt-1">
    <p class="text-spacing-3 header-title text-center text-uppercase ml-2 mr-2 mt-1">
      Fagotto SPA
    </p>
    <p class="text-spacing-3 header-title text-center text-uppercase ml-2 mr-2">Derechos Reservados © 2025</p>
  </body>
</html>
<style media="screen">
  *{
    margin: 0;
    margin: 6px 2px;
    padding: 0px;
    word-wrap: break-word !important;
    word-break: break-all !important;
    font-size: 12px !important;
    font-weight: bold !important;
    letter-spacing: 0.8px !important;
    font-family: monospace;
  }
  p{
    margin: 2px !important;
  }
  .box-fhater{
    margin: 0px !important;
    padding: 0px !important;
  }
  /* styles table */
  table {
    border-collapse: collapse;
    width: 100%;
  }
  th, td {
    text-align: left;
    border-bottom: 0.8px solid #c4c4c4;
  }
  td {
    padding: 3px;
  }
  th {
    padding: 5px;
    padding-top: 3px;
  }
  /* font styles */
  .text-center{
    text-align: center !important;
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
  
  /* Estilos elegantes para el header */
  .business-name {
    font-size: 16px !important;
    font-weight: 900 !important;
    letter-spacing: 2px !important;
    margin-bottom: 5px !important;
    text-decoration: underline;
  }
  
  .header-subtitle {
    font-size: 14px !important;
    font-weight: 600 !important;
    letter-spacing: 1.5px !important;
    margin-bottom: 8px !important;
    color: #333;
  }
  /* hr style */
  .hr-style-mb{
    border: 0.8px solid #000 !important;
    margin-bottom: 7px;
  }
  .hr-style{
    border: 0.8px solid #000 !important;
    margin: 0px !important;
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

  .hr-style-mb-table{
    border: 0.8px solid #000 !important;
    border-top: 0px;
    border-left: 0px;
    border-right: 0px;
    border-bottom: 0.8px;
    margin-bottom: 1px;
  }
</style>
