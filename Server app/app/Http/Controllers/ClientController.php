<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\MPage;
use SoapClient;

class ClientController extends Controller
{
  public function searchUser($rut){
    $pquery = DB::table('clients')->where('rut', $rut)->first();
    if(!$pquery){
      //Si no lo encontro vamos a buscarlo en el sistema del tipo del SII
      //Desahabilitar cache
      ini_set("soap.wsdl_cache_enabled", "0");
      //Establecer parametros de envío Ejemplo:
      $parametros = array("RUTRECEPTOR" => $rut/*"76689863-7"*/,"TOKEN" => "www.loss.cl");
      //Dirección donde se encuentra el servicio
      $client = new SoapClient("http://www.appoctava.cl/wsdatosreceptor/WebService.php?wsdl");
      try{
        //iniciar cliente soap
        $resultado = $client->__SoapCall("DatosReceptor", $parametros);

        //Parametros de salida
        $IdResultado=$resultado[0]->IdResultado;
        $DescripcionResultado=$resultado[0]->DescripcionResultado;

        if ((!$IdResultado && $IdResultado !== '0') || $IdResultado == '1') {
          return response()->json('Cliente no encontrado', 404);
        }

        if ($IdResultado == '0' && isset($resultado[0]->Rut)) {
          $data = [
            'rut'  => $resultado[0]->Rut,
            'razsoc'    => $resultado[0]->RazonSocial,
            'girrec'    => $resultado[0]->Giro,
            'direction'    => $resultado[0]->Direccion,
            'comrec'    => $resultado[0]->Comuna,
            'ciurec'    => $resultado[0]->Ciudad,
          ];
          return response()->json($data);
        }
        return response()->json('Cliente no encontrado', 404);

      }catch (SoapFault $e){
        //Si hay algún problema intermedio deberia ser atrapado aquí.
        return response()->json('Cliente no encontrado', 404);
      }
      return response()->json('Cliente no encontrado', 404);
    }
    return response()->json($pquery);
  }

  public function getClientAplications($id){
    $pquery = DB::table('aplications')->where('client', $id)->get();
    if (!$pquery) return response()->json('Error del servidor',500);

    return response()->json($pquery);
  }

  public function getClients(Request $request){
    $pquery = DB::table('clients')
              ->select('clients.*',DB::raw("count(aplications.id) as numAplications"),'clients.rut as RutClient')
              ->groupBy('clients.id')
              ->leftjoin('aplications', 'aplications.client', '=', 'clients.id');

    if (!$pquery) return response()->json('Error del servidor',500);

    //Ordenamientos
    $orders = ['id','name','rut'];
    //Filtrados
    $filters = ['rut'];
    if ($request->input('rutOfClient')) {
      $rut = $request->input('rutOfClient');
      $pquery->whereRaw("clients.rut like '%$rut%'");
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,10,'','clients',$orders,$filters, true, null);

    return response()->json($Paginated);
  }
}
