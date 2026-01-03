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

$imgLogo = null;

if (isset($order['envs']) && isset($order['envs']->sii_logo) && isset($order['envs']->sii_logo->value)) {
  if ($order['envs']->sii_logo->value)
    $imgLogo = '../storage/app/'.$order['envs']->sii_logo->value;
}





?>

<style>
      .order-id {
        font-size: 500px; /* Tamaño de la fuente */
        font-weight: bold; /* Negrita */
        text-align: center; /* Centrado */
        line-height: 300px; /* Altura de línea para centrar verticalmente */
        height: 300px; /* Altura del fondo negro */
        background-color: #000; /* Fondo negro */
        color: #fff; /* Texto blanco */
        margin-bottom: 100px; /* Margen inferior */
      }
    </style>
  </head>


<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>ticket</title>
    <?php if($imgLogo): ?>
      <div style="text-align: center;">
        <img src="<?php echo $imgLogo; ?>" alt="Logo" style="max-width: 100px; margin-bottom: 20px;">
      </div>
    <?php endif; ?>
  </head>
  <body class="body-ticket">
    @if($order['lower_case'])
      <p class="fontZiseOld text-spacing-3 header-title-1 text-center text-uppercase text-bold m-auto">
          ORDEN N° {{$order['id']}}<br>          
          <p class="text-spacing-3 header-title text-center text-uppercase mb-1 fs12 text-bold">     
          </p>

<table class="body-texts-table">
    




</table>





<div>
        <p class="text-uppercase">
          {{ DateTime::createFromFormat("Y-m-d H:i:s",$order['created_at'])->format("Y/m/d H:i:s") }}
        </p>
        @if($order['waiter'] && $order['board'])
          <p class="text-uppercase">
            Mesero: {{ $order['waiter']->name }}
          </p>
          <p class="text-uppercase">
            Mesa: {{ $order['board']->name  }}
          </p>
          @if(isset($order['client_ticket']) && $order['client_ticket'])
          <p class="text-uppercase mb-2">
            Nombre cliente: {{ $order['client_ticket'] }}
          </p>
          @else
          <p class="mb-2"></p>
          @endif
        @endif
      </div>
      <table>
        <!-- titulo de tabla -->
        <!-- <tr> -->
          <!-- <td class="text-uppercase text-height-titles">NOMBRE</td> -->
          <!-- <td class="text-uppercase text-height-titles">PRECIO</td> -->
          <!-- <td class="text-uppercase text-height-titles text-right">CANT.</td> -->
          <!-- <td class="text-uppercase text-height-titles text-right">SUBTOTAL</td> -->
        <!-- </tr> -->
        <BR><BR><BR>
        <?php foreach ($order['products'] as $product) { ?>
          <tr>
            <td class="fontZiseOld text-uppercase">{{$product->name}}</td>
            <td class="fontZiseOld text-right">{{ $product->quantity }}</td>
            @if(!$order['view_subtotal'])
              <td class="fontZiseOld text-right">{{ formatoChileno($product->subtotal, 2) }}</td>
            @endif
          </tr>
        <?php } ?>
      </table>

      @if(!$order['view_total'])
        <p class="fontZiseOld text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 text-bold">
          TOTAL: <span class="ml-3">${{ formatoChileno($order['total'], 2) }}</span>
        </p>
      @endif
      <p class="text-spacing-3 text-uppercase mb-1 mt-1 description-padding">
          <label>Descripcion</label> <br />
          <span class="fontZiseSmall">{{ $order['description'] }}</span>
        </p>
      @if(!$order['no_code_bar'])
        <p class="fontZiseOld text-spacing-3 header-title text-center text-uppercase mb-1 mt-1">
          <?php
          echo '<img class="barcode" src="data:image/png;base64,' . base64_encode(
            $generator->getBarcode($order['barcode'], $generator::TYPE_UPC_A)
            ) . '">';
            ?>
        </p>
      @endif
    @endif
    



    
    @if(!$order['lower_case'])
    <p class="fontZiseOld text-spacing-3 text-center text-uppercase text-bold text-light bg-dark">
        <p class="text-uppercase m-auto text-center text-light bg-dark fs20 bg-black text-white"> 
          ORDEN N° {{$order['id']}}<br>
        </p>     
        <p class="text-uppercase">
          </p>
      </p>
      <div>
        <p class="text-uppercase">
          {{ DateTime::createFromFormat("Y-m-d H:i:s",$order['created_at'])->format("Y/m/d H:i:s") }}
        </p>
        @if($order['waiter'] && $order['board'])
          <p class="text-uppercase">
            Mesero: {{ $order['waiter']->name }}
          </p>
          <p class="text-uppercase">
            Mesa: {{ $order['board']->name  }}
          </p>
          @if(!empty($order['client_ticket']))
            <p class="text-uppercase mb-2">
              Cliente: {{ $order['client_ticket']  }}
            </p>
          @endif
        @endif

        <!-- opcion 1 hasta aca -->



        <!-- opcion 2 -->


        
      </div>
      <p class="text-spacing-1 header-title text-center text-uppercase mb-1 fs12 text-bold">
        -----------------------------------------------------------------------<br>
     </p>
      <table class="table">
       
        <!-- titulo de tabla -->
    <tr> 
      <p class="text-spacing-1 header-title text-center text-uppercase mb-1 fs12 text-bold">
        -----------------------------------------------------------------------<br>

     </p>
      <td class="text-uppercase text-height-titles text-center" style="font-size: 0.4em">CANT</td> 
         <td class="text-uppercase text-height-titles" style="font-size: 0.6em" >NOMBRE</td> 
     <!--    <td class="text-uppercase text-right " style="font-size: 0.4em">PRECIO</td> -->
      </tr> 
        <?php foreach ($order['products'] as $product) { ?>
          <tr>
            <td class="text-center"style="font-size: 0.8em" > {{ $product->quantity }}</td>
            <td class="text-uppercase" style="font-size: 0.4em" >{{$product->name}}</td>
            @if(!$order['view_subtotal'])
              <td class="text-left" style="font-size: 0.6em">{{ ($product->subtotal) }}</td> 
            @endif
          </tr>
          @if(!empty($product->comment))
          <tr>
            <td></td>
            <td>
        <p style="font-size: 0.8.7em;">{{$product->comment}}</p>
            </td>
        </tr>
      
          @endif
        <?php } ?>
      </table>
      @if(!$order['view_total'])
        @if(isset($order['specialPayment']) && $order['specialPayment']['paymentMethod'] === 'banco_chile_20')
          <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 text-bold">
            SUBTOTAL: <span class="ml-3">${{ formatoChileno($order['specialPayment']['originalTotal']) }}</span>
          </p>
          <p class="text-spacing-3 header-title text-center text-uppercase mb-1 mt-1 text-bold">
            DESCUENTO BANCO CHILE 20%
          </p>
          <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 text-bold">
            DESCUENTO: <span class="ml-3">-${{ formatoChileno($order['specialPayment']['discountAmount']) }}</span>
          </p>
          <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 text-bold">
            TOTAL: <span class="ml-3">${{ formatoChileno($order['total']) }}</span>
          </p>
        @elseif(isset($order['specialPayment']) && $order['specialPayment']['paymentMethod'] === 'halloween_20')
          <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 text-bold">
            SUBTOTAL: <span class="ml-3">${{ formatoChileno($order['specialPayment']['originalTotal']) }}</span>
          </p>
          <p class="text-spacing-3 header-title text-center text-uppercase mb-1 mt-1 text-bold">
            🎃 DESCUENTO HALLOWEEN 20% 🎃
          </p>
          <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 text-bold">
            DESCUENTO: <span class="ml-3">-${{ formatoChileno($order['specialPayment']['discountAmount']) }}</span>
          </p>
          <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 text-bold">
            TOTAL: <span class="ml-3">${{ formatoChileno($order['total']) }}</span>
          </p>
        @else
          <p class="text-spacing-3 header-title text-right text-uppercase mb-1 mt-1 text-bold">
            TOTAL: <span class="ml-3">${{ formatoChileno($order['total']) }}</span>
          </p>
        @endif
      @endif
      
      @if($order['ticket_description'] != '')
        <p class="text-spacing-3 text-uppercase mb-1 mt-1 description-padding">
          <label>OBSERVACION</label> <br /> 
          <span class="fontZiseSmall">{{ $order['description'] }}</span>
        </p>
      @endif
      
      @if(!$order['no_code_bar'])
        <p class="text-spacing-3 header-title text-center text-uppercase mb-1 mt-1">
          <?php
          echo '<img class="barcode" src="data:image/png;base64,' . base64_encode(
            $generator->getBarcode($order['barcode'], $generator::TYPE_UPC_A)
            ) . '">';
            ?>
        </p>

      @endif
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

  .bg-black{
    background: black;
  }
  .text-white{
    color:white;
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

    .fs20{
        font-size: 20px !important;
    }
</style>
