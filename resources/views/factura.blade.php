<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <p class="text-spacing-3 header-title text-center text-uppercase">
        {{$sell['envs']->stgg_header->value}}
    </p>
    <p class="text-spacing-3 header-title text-center text-uppercase">recibo</p>
    <hr class="hr-style-mb mt-2-5">
    <div>
      <p class="text-uppercase">
        CASA MATRIZ: {{$sell['envs']->stgg_casa_matriz->value}}
      </p>
      <p class="text-uppercase">
        CONTACTO: {{$sell['envs']->stgg_contacto->value}}
      </p>
      <p class="text-uppercase">
        HORARIO: {{$sell['envs']->stgg_horario->value}}
      </p>
      <p class="text-uppercase">
        {{$sell['envs']->stgg_url->value}}
      </p>
    </div>
    <hr class="hr-style">
    <div>
      <p class="text-uppercase">Fecha: {{DateTime::createFromFormat("Y-m-d H:i:s",$sell['created_at'])->format("d/m/Y")}}</p>
      <p class="text-uppercase">Hora:  {{DateTime::createFromFormat("Y-m-d H:i:s",$sell['created_at'])->format("H:i:s")}}</p>
    </div>
    <hr class="hr-style">

    <!-- tabla general -->
    <table>
      <!-- titulo de tabla -->
      <tr>
        <th>CANT.</th>
        <th>PRODUCTO</th>
        <th>MONTO</th>
      </tr>
      <!-- titulo de tabla -->
      <?php foreach ($sell['products'] as $product) {
        echo
        "<tr>
          <td>".$product->quantity."</td>
          <td>".$product->name."</td>
          <td>$".$product->totalPrice."</td>
        </tr>";
      } ?>
    </table>
    <!-- tabla general -->

    <hr class="hr-style">
    <table>
      <tr>
        <th>TOTAL:</th>
        <th>${{$sell['total']}}</th>
      </tr>
      <tr>
        <th>MÉTODO DE PAGO:</th>
        <th>{{ $sell['paymode_formatted'] ?? $sell['paymode'] ?? 'No especificado' }}</th>
      </tr>
    </table>
    <hr class="hr-style mt-1">
    <p class="text-spacing-3 header-title text-center text-uppercase ml-2 mr-2 mt-1">
      {{$sell['envs']->stgg_mensaje_gracias->value}}
    </p>
    <!--<p class="text-spacing-3 header-title text-center text-uppercase ml-2 mr-2">
      * TE ESPERAMOS NUEVAMENTE *
    </p>-->
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
  font-family: monospace !important;
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
</style>
