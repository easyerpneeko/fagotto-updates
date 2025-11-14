<?php namespace App\Http\Controllers\Controllers_local;

// Models
use App\models_local\UserApp;
use App\models_local\Client;

// Helpers
use App\Helpers\CurrentApp;
use Config;
use App\Helpers\MPage;

// Laravel
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Dompdf\Dompdf;
use DateTime;
use Auth;
//
use App\Classes\ServicesSII;

class ClientsController extends Controller
{

  public function editClient(Request $request, $id){
    $_request = $request->all();
    $validaciones = [
      'name' => 'required|string|max:32|min:2',
      'lastname' => 'required|string|max:32|min:2',
      'email' => (isset($_request['email']) && $_request['email']) ? 'nullable|email' : 'nullable',
      'phone' => 'nullable',
      'rut' => 'required|string|max:12',
      'city' => 'required|string|max:20',
      'comuna' => 'string|max:20',
      'razon_social' => 'string|max:100'
    ];
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
      $validaciones['direction'] =  'string|max:70';
      $validaciones['giro'] =  'string|max:80';
    }
    if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono')){
      $validaciones['phone'] =  'max:16';
    }
    $validator = Validator::make($_request, $validaciones);
    if($validator->fails()) return response()->json($validator->errors(), 400);

    /*$clientNow = Client::where('rut', $_request['rut'])->first();*/
    $clientNow = Client::where('id', $id)->first();
    if(!$clientNow){
      $client = Client::createClient($_request);
      if(!$client) return response()->json("Error del servidor",500);
    }else{
      $client = Client::editClient($_request, $clientNow->id);
      if(!$client) return response()->json("Error del servidor",500);
    }
    return response()->json('Cliente editado exitosamente',200);
  }

  public function retrieveClient($rut) {
    $client = Client::where('rut', $rut)->first();

    if (!$client) {
      $response = ServicesSII::getPerson($rut);
      if ($response->ok)
        $client = $response->content;
    }

    if (!$client)
      return response()->json('Usuario no encontrado',404);

    return response()->json($client);
  }

  protected function getClients(Request $request) {
    
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.clients');
    if (!$pquery) return response()->json('Error del servidor',500);
    //Ordenamientos
    $orders = ['id', 'rut', 'razon_social'];

    //Filtrados
    $filters = [];

    if ($request->input('searchOfClient')) {
      $search_string = $request->input('searchOfClient');
      $pquery->whereRaw("
          clients.rut like '%$search_string%'          OR
          clients.name like '%$search_string%'         OR
          clients.lastname like '%$search_string%'     OR
          clients.city like '%$search_string%'         OR
          clients.comuna like '%$search_string%'       OR
          clients.razon_social like '%$search_string%' OR
          clients.direction like '%$search_string%'    OR
          clients.giro like '%$search_string%'         OR
          clients.email like '%$search_string%'
      ");
    } 
    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,12,'','clients',$orders,$filters, false, null);
    return response()->json($Paginated);
  }

  protected function editProduct(Request $request, $id){
    $data = $request->all();

    $validaciones = [
      'name' => 'string|min:3|max:48',
      'email' => 'email',
    ];

    return Product::editClient($data, $id);
  }

}
