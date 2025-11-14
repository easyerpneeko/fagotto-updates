<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Config;
use PDF;

// Helpers
use App\Helpers\MPage;
use App\Helpers\ConectionDB;
use App\Helpers\CurrentApp;
use Auth;

// Models
use App\Aplication;
use App\models_local\Cobro;




class CobrosController extends Controller
{
  public function newCobro(Request $request){
    $_request = $request->all();

    $denominaciones = json_decode($_request['denominaciones']);

    $total = 0;

    foreach ($denominaciones as $denominacion) {
      if($denominacion->percentage < 0){
        $total+= $denominacion->amount * ($denominacion->percentage/100);
      }else{
        $total+= $denominacion->amount;
      }
      
    }


    $subtotal = ceil($total / 1.19);
    $iva = ceil($subtotal * 0.19);
    //EL MONOTO DE LAS DENOMINACIONES YA TIENE IVA

    $pquery = DB::table('cobros')->insert(
      [
        'status'=> 'pendiente',
        'user' => 1,
        'rut_emisor' =>  $_request['rut_emisor'], 
        'rut_receptor' => $_request['rut_receptor'],
        'subtotal'=> $subtotal,
        'iva' => $iva,
        'total' => $total, 
        'denominaciones' => $_request['denominaciones'],
        'date_pago' => $_request['date_pago'],
        'date_vencimiento' => $_request['date_vencimiento'],
        'direccion'=> $_request['direccion'],
        'paymode'=> $_request['paymode'],
        'total_ventas'=> $_request['total_ventas'],
        'app_id' => $_request['appId'],
        'created_at' => now(), // Agregar la columna created_at con la fecha y hora actual
        'updated_at' => now() // Agregar la columna created_at con la fecha y hora actual
      ]
    );

    return response()->json($pquery,200);
  }

  public function newDenominacion(Request $request){
    $_request = $request->all();

    $pquery = DB::table('cobros_denominaciones');

    $pquery->insert(
      [
        'description' =>  $_request['description'], 
        'percentage' => $_request['percentage'],
        'amount' => $_request['amount']
        ]
    );

    return response()->json('Denonimacion creada exitosamente',200);
  }

  public function update(Request $request, $id){

  }

  public function getDenominaciones(Request $request){
        $_request = $request->all();

        $pquery = DB::table('cobros_denominaciones');

        // if (isset($_request['startDate']) && isset($_request['endDate'])){
        //     $pquery->where('created_at', '>=', $_request['startDate'])
        //     ->where('created_at', '<=', $_request['endDate']);
        // }

        $filters = ['description', 'percentage'];
        $orders = ['id','description'];

        // Procesamiento individual de items
        $Paginated = MPage::paginate($pquery, $request,15,'','cobros_denominaciones',$orders,$filters, false, null);
        
        return response()->json($Paginated);
  }

  public function getCobros(Request $request){
    $_request = $request->all();

    $pquery = DB::table('cobros');

    $filters = ['status', 'app_id'];
    $orders = ['date_pago','date_vencimiento'];

    // Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,15,'','cobros',$orders,$filters, false, null);
      
    return response()->json($Paginated);
  }

  public function removeDenominacion($id)
  {
      // Elimina la denominación con el ID especificado
      DB::table('cobros_denominaciones')->where('id', $id)->delete();
  
      return response()->json(['message' => 'Denominación eliminada correctamente']);
  }
  
  public function downloadPDF($id){

      $cobro = DB::table('cobros')->where('id', $id)->first();
      $denominaciones = json_decode($cobro->denominaciones);

      $app = DB::table('aplications')->where('id', $cobro->app_id)->first();
      // $app =compact('app');
      // dd($app);
      
      $pdf = \PDF::loadView('cobro', compact('cobro', 'denominaciones','app'));    
      $pdf->setPaper('letter');

      return $pdf->download('cobro_' . $cobro->id . '.pdf');

  }

}
