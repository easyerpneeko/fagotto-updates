<?php
  function formatoChilenoReporte($numero, $decimales = null) {

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
    <p class="text-spacing-3 header-title text-center text-uppercase">{{$data['envs']->stgg_header->value}}</p>
    <p class="text-spacing-3 header-title text-center text-uppercase">Reporte</p>
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
          <td>${{ formatoChilenoReporte($data['counters']['init_money']) }}</td>
        </tr>
      <?php } ?>
      <tr>
        <td>Total</td>
        <td>${{ formatoChilenoReporte($data['counters']['balanceTotal']) }}</td>
      </tr>
      <tr>
        <td>Ganancia total</td>
        <td>${{ formatoChilenoReporte($data['counters']['gananciaTotal']) }}</td>
      </tr>
      <?php if(isset($data['counters']['expenses_day'])) { ?>
        <tr>
          <td>Gastos del día</td>
          <td>${{ formatoChilenoReporte($data['counters']['expenses_day']) }}</td>
        </tr>
        <tr>
          <td>Total - Gastos</td>
          <td>${{ formatoChilenoReporte($data['counters']['totalToExpenses']) }}</td>
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
          <td>Ordenes totales</td>
          <td>{{$data['counters']['orders']}}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['quantityTotal'])) { ?>
        <tr>
          <td>Unidades totales</td>
          <td>{{$data['counters']['quantityTotal']}}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['typeProducts'])) { ?>
        <tr>
          <td>Tipos de productos</td>
          <td>{{$data['counters']['typeProducts']}}</td>
        </tr>
      <?php } ?>
      <tr>
        <td>Boletas (Efectivo)</td>
        <td>${{ formatoChilenoReporte($data['counters']['boleta']) }}</td>
      </tr>
      <tr>
        <td>No Sii</td>
        <td>${{ formatoChilenoReporte($data['counters']['noSii']) }}</td>
      </tr>
      <?php if(isset($data['counters']['debito'])) { ?>
        <tr>
          <td>Debito</td>
          <td>${{ formatoChilenoReporte($data['counters']['debito']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['credito'])) { ?>
        <tr>
          <td>Credito</td>
          <td>${{ formatoChilenoReporte($data['counters']['credito']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['transferencia'])) { ?>
        <tr>
          <td>Transferencia</td>
          <td>${{ formatoChilenoReporte($data['counters']['transferencia']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['banco'])) { ?>
        <tr>
          <td>Trasnbank</td>
          <td>${{ formatoChilenoReporte($data['counters']['banco']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['amipass'])) { ?>
        <tr>
          <td>Amipass</td>
          <td>${{ formatoChilenoReporte($data['counters']['amipass']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['junaeb'])) { ?>
        <tr>
          <td>Junaeb</td>
          <td>${{ formatoChilenoReporte($data['counters']['junaeb']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['uber'])) { ?>
        <tr>
          <td>Uber Eats - Boleta SII</td>
          <td>${{ formatoChilenoReporte($data['counters']['uber']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['rappi'])) { ?>
        <tr>
          <td>Rappi</td>
          <td>${{ formatoChilenoReporte($data['counters']['rappi']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['edenred'])) { ?>
        <tr>
          <td>Edenred</td>
          <td>${{ formatoChilenoReporte($data['counters']['edenred']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['convenio_empresa'])) { ?>
        <tr>
          <td>Convenio Empresa</td>
          <td>${{ formatoChilenoReporte($data['counters']['convenio_empresa']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['sodexo'])) { ?>
        <tr>
          <td>Sodexo</td>
          <td>${{ formatoChilenoReporte($data['counters']['sodexo']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['pedidos_ya'])) { ?>
        <tr>
          <td>Pedidos Ya</td>
          <td>${{ formatoChilenoReporte($data['counters']['pedidos_ya']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['pluxee'])) { ?>
        <tr>
          <td>Pluxee</td>
          <td>${{ formatoChilenoReporte($data['counters']['pluxee']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['banco_chile_20'])) { ?>
        <tr>
          <td>Banco De Chile 20%</td>
          <td>${{ formatoChilenoReporte($data['counters']['banco_chile_20']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['nota_de_credito'])) { ?>
        <tr>
          <td>Nota de Crédito</td>
          <td>${{ formatoChilenoReporte($data['counters']['nota_de_credito']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['guia_despacho'])) { ?>
        <tr>
          <td>Guía Despacho</td>
          <td>${{ formatoChilenoReporte($data['counters']['guia_despacho']) }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['transferencia'])) { ?>
        <tr>
          <td>Transferencia</td>
          <td>${{ $data['counters']['transferencia'] }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['cheque'])) { ?>
        <tr>
          <td>Cheque</td>
          <td>${{ $data['counters']['cheque'] }}</td>
        </tr>
      <?php } ?>
      <?php if(isset($data['counters']['banco'])) { ?>
        <tr>
          <td>Trasnbank</td>
          <td>${{ $data['counters']['banco'] }}</td>
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
            <td>$".formatoChilenoReporte($expense->balance)."</td>
          </tr>";
        } ?>
      </table>
    @endif
    <!-- Gastos del dia  -->
    <hr class="hr-style">

    <!-- estadisticas de cantidad de productos -->
    <div class="text-center">
      <p>Estadisticas de cantidad de productos</p>
    </div>
    <table>
      <!-- titulo de tabla -->
      <tr>
        <th>Producto</th>
        <th>Canti.</th>
      </tr>
      <!-- titulo de tabla -->
      <?php foreach ($data['products'] as $product) {
        echo
        "<tr>
          <td>".$product['name']."</td>
          <td>".$product['quantity']."</td>
        </tr>";
      } ?>
    </table>
    <!-- estadisticas de cantidad de productos -->

    <!-- estadisticas de valor de producto  -->
    <div class="text-center">
      <p>Estadisticas de valor de producto</p>
    </div>
    <table>
      <!-- titulo de tabla -->
      <tr>
        <th>Producto</th>
        <th>Valor.</th>
      </tr>
      <!-- titulo de tabla -->
      <?php foreach ($data['products'] as $product) {
        echo
        "<tr>
        <td>".$product['name']."</td>
        <td>$".formatoChilenoReporte($product['price'])."</td>
        </tr>";
      } ?>
    </table>
    <!-- estadisticas de valor de producto  -->

    <!-- estadisticas de ganancia de prodcutos  -->
    <div class="text-center">
      <p>Estadisticas de ganancia de productos</p>
    </div>
    <table>
      <!-- titulo de tabla -->
      <tr>
        <th>Producto</th>
        <th>Ganan.</th>
      </tr>
      <!-- titulo de tabla -->
      <?php foreach ($data['products'] as $product) {
        echo
        "<tr>
          <td>".$product['name']."</td>
          <td>$".formatoChilenoReporte($product['totalProfit'])."</td>
        </tr>";
      } ?>
    </table>
    <!-- estadisticas de ganancia de prodcutos  -->
    <!-- datos de los productos -->
    <hr class="hr-style">

    @component('components.report-meseros', ['data' => $data]);
    @endcomponent

    <hr class="hr-style mt-1">
    <p class="text-spacing-3 header-title text-center text-uppercase ml-2 mr-2 mt-1">
      REPORTE PARA COMERCIALIZADORA
      ARRIAGADA&ARRIAGADA SPA
    </p>
    <p class="text-spacing-3 header-title text-center text-uppercase ml-2 mr-2">* SOFTWARE POS MADURO SG *</p>
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
