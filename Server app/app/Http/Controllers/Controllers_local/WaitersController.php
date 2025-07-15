<?php

namespace App\Http\Controllers\Controllers_local;

// Laravel
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
// Helpers
use App\Helpers\MPage;
use App\Helpers\CurrentApp;
// Models
use App\models_local\Waiter;
use App\models_local\Board;
use App\models_local\Addition_Waiter;
// Log
use Illuminate\Support\Facades\Log;

class WaitersController extends Controller
{
  protected function store(Request $request){
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|unique:mysql_local.waiters|max:24',
      // 'user_id' => 'required|unique:mysql_local.waiters'
    ],
    [
      "name.unique" => "El nombre de usuario esta en uso",
      // "user_id.unique" => "El usuario elegido ya cuenta con una perfil de mesero"
    ]);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
    $_request = $request->all();

    // Creando columna
    $waiter = Waiter::create($_request);
    // Verificando respuesta
    if(!$waiter) return response()->json("Error del servidor",500);
    // Ok
    return response()->json(['Mesero creado exitosamente'],200);
  }

  protected function update(Request $request, $id){
      $validator = Validator::make($request->all(), [
        'name' => 'string|min:2|max:24',
        // 'user_id' => 'required'
      ]);
      if($validator->fails()) return response()->json($validator->errors(), 400);
      // Validando nombre unico
      $_request = $request->all();

      $unique = Waiter::where('name', $_request['name'])->where('id', "<>", $id)->first();
      if($unique) return response()->json("El nombre de usuario esta en uso", 409);

      // Editando mesero
      $waiter = Waiter::find($id);
      // if($waiter->user_id != $_request['user_id']){
      //   $unique = Waiter::where('user_id', $_request['user_id'])->first();
      //   if($unique) return response()->json("El usuario elegido ya cuenta con una perfil de mesero", 409);
      // }
      $waiter->name = $_request['name'];
      // $waiter->user_id = $_request['user_id'];
      if(!$waiter->save()) return response()->json('Error del servidor',500);
      // Ok
      return response()->json(['Mesero editado exitosamente'],200);
  }

  protected function index(Request $request){
    // Obtener meseros
    $pquery = Waiter::where('trash', 0);
    //Ordenamientos
    $orders = ['id','name'];
    //Filtrados
    $filters = ['name'];
    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,10,'','waiters',$orders,$filters, false, null);
    // Obtener estado de mesa
    $Paginated['items'] = Waiter::getStatesWaiters($Paginated['items']);
    // Ok
    return response()->json($Paginated);
  }

  protected function remove($id){
    $waiter = Waiter::find($id);
    if(!$waiter) return response()->json('Mesero no encontrado',404);

    if($waiter->trash == 1) return response()->json('El usuario ya se encuentra eliminado',400);
    // Editando mesero
    $waiter->trash = 1;
    // $waiter->user_id = null;
    $waiter->name = sha1(uniqid());
    if(!$waiter->save()) return response()->json('Error del servidor',500);

    return response()->json(['Mesero eliminado exitosamente'],200);
  }

  // Adiciones de los meseros
  protected function newAddtion(Request $request){
    $validator = Validator::make($request->all(), [
      'name' => 'string|max:180',
      'quantity' => 'required',
      'waiter' => 'required',
    ]);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
    $_request = $request->all();

    $app = CurrentApp::App();
    $envs = json_decode($app->environment_vars);
    $_request['balance'] = $envs->default_value_addtion->value;

    // Verificando exitencia del mesero
    $waiter = Waiter::find($_request['waiter']);
    if(!$waiter) return response()->json('Mesero no encontrado',404);

    // Creando columna
    $waiterJob = Addition_Waiter::create($_request);
    if(!$waiterJob) return response()->json("Error del servidor",500);

    $waiterJob->waiter = $waiter;
    $b64Doc = $this->printPDF($waiterJob, 'addtion_boleta', $app);
    // Ok
    return response()->json($b64Doc,200);
  }

  public function printPDF($data, $type, $app){
    $size = array(0,0,227,600);
    $data['app_name'] = $app->name;
    $data['balanceTotal'] = (float) $data['balance'] * (float) $data['quantity'];
    $data['envs'] = json_decode($app->environment_vars);
    // Creando recibo
    $pdf = \PDF::loadView($type,compact('data'))->setPaper($size);
    $name = $type.'_'.uniqid().'.pdf';
    // Guardando archivo
    Storage::put('public/pdf/'.$name, $pdf->output());
    // Transformando archivo
    $b64Doc = chunk_split(base64_encode($pdf->output()));
    return $b64Doc;
  }
}
