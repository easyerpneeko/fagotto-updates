<?php

function formatoChilenoReporteMeseros($numero, $decimales = null) {

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

@if(count($data['waiters']) > 0)
  <!-- Cantidad de ordenes completadas por los meseros  -->
  <div class="text-center">
    <p>Cantidad de mesas ateditas por los meseros</p>
  </div>
  <table>
    <!-- titulo de tabla -->
    <tr>
      <th>Mesero</th>
      <th>Canti.</th>
    </tr>
    <!-- titulo de tabla -->
    <?php foreach ($data['waiters'] as $waiter) {
      echo
      "<tr>
        <td>".$waiter['waiter']."</td>
        <td>".$waiter['orders']."</td>
      </tr>";
    } ?>
  </table>
  <!-- Cantidad de ordenes completadas por los meseros  -->
  <!-- Total de ganancia generada por los meseros  -->
  <div class="text-center">
    <p>Total de dinero generado por mesero</p>
  </div>
  <table>
    <!-- titulo de tabla -->
    <tr>
      <th>Mesero</th>
      <th>Total</th>
    </tr>
    <!-- titulo de tabla -->
    <?php foreach ($data['waiters'] as $waiter) {
      echo
      "<tr>
        <td>".$waiter['waiter']."</td>
        <td>$".formatoChilenoReporteMeseros($waiter['total'])."</td>
      </tr>";
    } ?>
  </table>
  <!-- Total de ganancia generada por los meseros  -->
  <!-- Total de ventas de mesero por mesa -->
  @if (\App\Helpers\CurrentApp::ConfStr('modulos.cafeteria.ajustes.perforance_mesero_mesa_report'))
  <div class="text-center">
    <p>Perforance de mesero segun mesa</p>
  </div>
  <table>
    <!-- titulo de tabla -->
    <?php /*\Illuminate\Support\Facades\Log::info($data);*/ ?>
    <?php foreach ($data['waiters'] as $waiter): ?>

      <?php if (true): ?>

        <tr >
          <th colspan="2" class="hr-style-mb-table text-center"><b><?= $waiter['waiter'] ?></b></th>
        </tr>

        <?php if (isset($waiter['boards_detailed']) && $waiter['boards_detailed']): ?>

          <tr>
            <th>Mesa</th>
            <th>Productos</th>
          </tr>

          <?php foreach ($waiter['boards_detailed'] as $board): ?>

            <tr>
              <td><?= $board['name']?></td>
              <td><?= $board['cantidad_productos']?></td>
            </tr>

          <?php endforeach; ?>
        <?php endif; ?>

        <?php if (isset($waiter['total_products_selled']) && ($waiter['total_products_selled'] || $waiter['total_products_selled'] === 0)): ?>
          <tr>
            <td>Pro. Totales</td>
            <td><?= $waiter['total_products_selled']?></td>
          </tr>
        <?php endif; ?>

      <?php endif; ?>

    <?php endforeach; ?>
    </table>
  @endif
  <!-- -->
@endif
