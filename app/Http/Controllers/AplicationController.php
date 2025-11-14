<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\AddTimeHelper;
use Illuminate\Http\Request;
use App\Helpers\ConectionDB;
use App\Helpers\CurrentApp;
use App\Helpers\MPage;
use App\Aplication;
use App\DataBase;
use App\Client;
use DateTime;
use Artisan;
use Auth;
use App\Http\Controllers\Controllers_local\ReportsController;
use App\Http\Controllers\Controllers_local\RequestsController;

//Main
use App\Modules;
use App\SubModules;

//Settings
use App\Settings_modules;
use App\Settings_submodules;

use Carbon\Carbon;

//
use Illuminate\Support\Facades\Log;

class AplicationController extends Controller {

  public function getEnvs(Request $request) {
    $app = CurrentApp::App();
    return response()->json(json_decode($app['environment_vars']), 200);
  }

  public function getApp(Request $request) {
    $app = CurrentApp::App();
    return response()->json($app->getApp($request->has('slim')));
  }

  public function getApps() {
    $currentApp = CurrentApp::App();
    $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
    return response()->json($apps);
  }

  public function getAppsPedidosNew(Request $request, RequestsController $RequestsController) {
    $currentApp = CurrentApp::App();
    $user = Auth::user();
    $appKey = $request->header('App-Key');

    // SEGURIDAD: Llaves especiales pueden ver todas las aplicaciones sin autenticación de usuario
    $specialKeys = ['1455-93EC-02ED-41A8-735D', '13E3-F7FB-35EB-E7CE-3541']; // super y admin
    
    // Si es llave especial, no requiere usuario autenticado
    if (in_array($appKey, $specialKeys)) {
        $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
    } 
    // Si no es llave especial, verificar autenticación de usuario
    else {
        // Si no hay usuario autenticado, denegar acceso
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }
        
        // Si es usuario maestro, puede ver todas las aplicaciones
        if ($user->username === 'master') {
            $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
        } else {
            $apps = collect([$currentApp]); // Solo la aplicación actual para usuarios normales
        }
    }

    foreach ($apps as $app) {
        $connection = new ConectionDB($app);
        $connection->set_database($app->database);
        $connection->ChangeDBToApp($app, $reconect = true);
        
        $cantPedidosNew = $RequestsController->getCantPedidosNew();
        
        $app->pedidosNew = $cantPedidosNew;
    }

    return response()->json($apps);
  }

  public function getAppCounters(Request $request, ReportsController $reportsController)  {
      $currentApp = CurrentApp::App();
      $user = Auth::user();
      $appKey = $request->header('App-Key');

      // SEGURIDAD: Llaves especiales pueden ver todas las aplicaciones sin autenticación de usuario
      $specialKeys = ['1455-93EC-02ED-41A8-735D', '13E3-F7FB-35EB-E7CE-3541']; // super y admin
      
      // Si es llave especial, no requiere usuario autenticado
      if (in_array($appKey, $specialKeys)) {
          $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
      } 
      // Si no es llave especial, verificar autenticación de usuario
      else {
          // Si no hay usuario autenticado, denegar acceso
          if (!$user) {
              return response()->json(['error' => 'Usuario no autenticado'], 401);
          }
          
          // Si es usuario maestro, puede ver todas las aplicaciones
          if ($user->username === 'master') {
              $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
          } else {
              $apps = collect([$currentApp]); // Solo la aplicación actual para usuarios normales
          }
      }
      
      $appCounters = [];

      foreach ($apps as $app) {
          $connection = new ConectionDB($app);
          $connection->set_database($app->database);
          $connection->ChangeDBToApp($app, $reconect = true);
          
          $counters = $reportsController->getCounters($request);
          
          $appCounters[$app->id.','.$app->name] = $counters;
      }
  
      return response()->json($appCounters);
  }

  public function getAppRequests(Request $request, RequestsController $RequestsController)  {
    $currentApp = CurrentApp::App();
    $user = Auth::user();
    $appKey = $request->header('App-Key');

    // SEGURIDAD: Llaves especiales pueden ver todas las aplicaciones sin autenticación de usuario
    $specialKeys = ['1455-93EC-02ED-41A8-735D', '13E3-F7FB-35EB-E7CE-3541']; // super y admin
    
    // Si es llave especial, no requiere usuario autenticado
    if (in_array($appKey, $specialKeys)) {
        $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
    } 
    // Si no es llave especial, verificar autenticación de usuario
    else {
        // Si no hay usuario autenticado, denegar acceso
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }
        
        // Si es usuario maestro, puede ver todas las aplicaciones
        if ($user->username === 'master') {
            $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
        } else {
            $apps = collect([$currentApp]); // Solo la aplicación actual para usuarios normales
        }
    }
    
    $appRequests = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app);
        $connection->ChangeDBToApp($app, $reconect = true);
        
        $requests = $RequestsController->index($request);
        
        $appRequests[$app->name] = $requests;
    }

    return response()->json($appRequests);
  }
  

  public function getAppRequestsApproved(Request $request, RequestsController $eequestsController)  {
    $currentApp = CurrentApp::App();
   $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
    
    $appRequests = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos
        
        $requests = $eequestsController->getApproved($request); // Llamar al método getApproved del controlador ReportsController pasando la request como parámetro
        
        $appRequests[$app->name] = $requests; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appRequests);
  }

  public function getAppTopSells(Request $request, ReportsController $reportsController)  {
    $currentApp = CurrentApp::App();
   $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
    
    $appTopSells = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->set_database($app->database); // Reconectar a la base de datos

        $topSells = $reportsController->getTopSells($request); // Llamar al método getTopSells del controlador ReportsController pasando la aplicación como parámetro
        
        $appTopSells[$app->name] = $topSells; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appTopSells);
}

  public function getAppById(Request $request, $id){
    $app = Aplication::find($id);
    $config = $app->getApp($request->has('slim'));
    return response()->json($config);
  }

  public function newAplication(Request $request){

      $_request = $request->all();
      $_request['tomorrow'] = new DateTime('tomorrow');
      $_request['tomorrow']->format('Y-m-d H:i:s');
      $_request['nextYear'] = new DateTime('tomorrow');
      $_request['nextYear']->modify('+365 day');
      $_request['nextYear']->format('Y-m-d H:i:s');

      $messages = [
        'expiration.after_or_equal' => 'La fecha de expiracion debe ser superior a mañana',
        'expiration.before_or_equal' => 'La fecha de expiracion debe ser inferior a un año',
      ];
      if(!preg_match('/^[a-zA-Z0-9\040]+$/', $_request['name'])) {
        return response()->json('El nombre de la aplicacion solo puede contener letras, numeros y espacios.',400);
      }

      $validator = Validator::make($_request, [
          'name' => 'required|string|max:32|min:4',
          'username' => 'required|string|max:32|min:4',
          'email' => 'required|string|max:128',
          'rut' => 'required|string',
          'sexo' => 'required',
          'expiration' => 'required|after_or_equal:tomorrow|before_or_equal:nextYear',
          'direction' => 'string',
          'description' => 'string',
      ], $messages);

      if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);


      // Verificando que el cliente no exista
      $client = DB::table('clients')->where('rut', $_request['rut'])->first();
      if(!$client){
        $create_client = Client::createClient($_request);
        $_request['client'] = $create_client->id;
      }else{
        $edit_client = Client::editClient($_request, $client->id);
        $_request['client'] = $client->id;
        $create_client = false;
      }

      // Creando aplicacion
      $aplication = Aplication::createAplication($_request);
      if(!$aplication) return response()->json("Error indefinido SQL al crear aplicacion",500);

      $DBname = ConectionDB::createDataBaseByAppName($aplication->name);

      // Creando columna en la tabla de database
      $dataBD = ["name" => $DBname, "username" => env('DB_USERNAME', 'root'), "password" => env('DB_PASSWORD', 'U9T9sR8XDCHMeE')];//<- pienso deberia estar dentro del modelo... pero de momento dejemoslo asi

      $database = DataBase::createDataBase($dataBD);
      if(!$database) return response()->json("Error indefinido SQL al crear base de datos",500);

      $aplication->database_app = $database->id;
      $aplication->save();
      $aplication->installKernel();

      return response()->json('Aplicacion creada exitosamente',200);

  }

  public function addTime(Request $request, $id){
    $validator = Validator::make($request->all(), [
        'expiration' => 'required',
    ]);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
    $_request = $request->all();

    $conexion = new AddTimeHelper($_request, $id);
    $conexion->addTime();

    return response()->json('Fecha modificada exitosamente',200);
  }

  public function cutService($id){
    $app = Aplication::find($id)->where('id', $id)->first();
    if(!$app) return response()->json('Aplicacion no encontrada',404);

    if($app->active == 0){
      return response()->json('La aplicacion se encuentra bloqueada',400);
    }

    $app->active = 0;
    $app->save();

    return response()->json('Servicio cortado exitosamente',200);
  }

  public function activeService($id){
    $app = Aplication::find($id)->where('id', $id)->first();
    if(!$app) return response()->json('Aplicacion no encontrada',404);

    if($app->active == 1){
      return response()->json('La aplicacion se encuentra activada',400);
    }
    $app->active = 1;
    $app->save();

    return response()->json('Servicio activado exitosamente',200);
  }

  public function getAplications(Request $request){
    $pquery = DB::table('aplications')
    ->leftJoin('clients', 'clients.id', 'aplications.client')
    ->select('aplications.*', 'clients.id as client_id','clients.username','clients.rut');

    if (!$pquery) return response()->json('Error del servidor',500);

    //Ordenamientos
    $orders = ['id','name','client','active'];
    //Filtrados
    $filters = ['name','rut'];

    if ($request->input('rutOfClient')) {
      $pquery->where('clients.rut', 'like', '%'.$request->input('rutOfClient').'%');
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,5,'','aplications',$orders,$filters, null);

    return response()->json($Paginated);
  }

  public function getAllAplications(){
    $pquery = DB::table('aplications')->get();
    return  response()->json($pquery, 200);
  }

  public function addModule(Request $request, $id) {

    if (!$id) return response()->json('Debe especificar una aplicacion', 400);
    if (!$request->input('module')) return response()->json('Debe especificar modulo', 400);

    $application = Aplication::find($id);
    if (!$application) return response()->json('Aplicacion no encontrada', 404);

    $module = Modules::find($request->input('module'));
    if (!$module) return response()->json('Modulo no encontrado', 404);

    return $application->installModule($module->id);

  }

  public function addSubModule(Request $request, $id) {

    if (!$id) return response()->json('Debe especificar una aplicacion', 400);
    if (!$request->input('submodule')) return response()->json('Debe especificar submodulo', 400);

    $application = Aplication::find($id);
    if (!$application) return response()->json('Aplicacion no encontrada', 404);

    $submodule = SubModules::find($request->input('submodule'));
    if (!$submodule) return response()->json('SubModulo no encontrado', 404);

    return $application->installSubModule($submodule->id);

  }

  public function getDashboard() {
    $aplications = Aplication::count();
    $clients = Client::count();
    $databases = DataBase::count();
    $modules = Modules::count();
    return response()->json([
      'aplications' => $aplications,
      'clients' => $clients,
      'databases' => $databases,
      'modules' => $modules
    ],200);
  }

  public function modifyEnvs(Request $request, $id) {
    $app = Aplication::find($id);
    $envs = $app->getEnvs();
    foreach ($envs as $envKey => $envData) {
        //return response()->json($request->all(), 400);
      if ($request->has($envKey)) {
        //Veo que el type sea el por defecto o bien sea text||string
        if (!isset($envData['type']) || $envData['type'] == 'text' || $envData['type'] == 'string'){
          //Veo si existen validaciones
          $max = null; if (isset($envData['max'])) $max = $envData['max'];
          $min = null; if (isset($envData['min'])) $min = $envData['min'];
          //Obtengo el valor nuevo a cambiar
          $value = $request->input($envKey);
          //Obtengo el label
          $label = $envData['label'];
          //Verifico si el valor no es nulo y si es asi aplico las validaciones (si existen)
          if ($value) {
            $value = (string) json_decode($value)->value;
            if ($max && strlen($value) > $max) { return response()->json("La variable $label tiene un maximo de $max caracteres",400); }
            if ($min && strlen($value) < $min) { return response()->json("La variable $label tiene un minimo de $min caracteres",400); }
          }
          //Actualizo el valor
          $envs[$envKey]['value'] = $value;
        }
        //Type number || check
        if (isset($envData['type']) && ($envData['type'] == 'check' || $envData['type'] == 'ckeckbox')){
          //Obtengo el valor nuevo a cambiar
          $value = $request->input($envKey);
          $value = json_decode($value)->value;
          //Actualizo el valor
          $envs[$envKey]['value'] = $value;
        }
        //Type number || numeric || integer || float
        if (isset($envData['type']) && ($envData['type'] == 'number' || $envData['type'] == 'numeric' || $envData['type'] == 'integer' || $envData['type'] == 'float')){
          //Veo si existen validaciones
          $max = null; if (isset($envData['max'])) $max = $envData['max'];
          $min = null; if (isset($envData['min'])) $min = $envData['min'];
          //Obtengo el valor nuevo a cambiar
          $value = $request->input($envKey);
          //Obtengo el label
          $label = $envData['label'];
          //Verifico si el valor no es nulo y si es asi aplico las validaciones (si existen)
          if ($value) {
            if (json_decode($value)->value && !is_numeric(json_decode($value)->value)) {
              return response()->json("La variable $label tiene que ser un valor numerico valido",400);
            }
            $value = json_decode($value)->value;
            if ($max && $value > $max) { return response()->json("La variable $label tiene un maximo de $max",400); }
            if ($min && $value < $min) { return response()->json("La variable $label tiene un minimo de $min",400); }
          }
          //Actualizo el valor
          $envs[$envKey]['value'] = $value;
        }
        //Type image
        if (isset($envData['type']) && $envData['type'] == 'image'){/**/

          //Obtengo el valor nuevo a cambiar
          // $value = $request->input($envKey);
          $value = $request->all();
          $value = (isset($value['image_'.$envKey]))?$value['image_'.$envKey]:null;
          //Obtengo el label
          $label = $envData['label'];
          //Verifico si el valor no es nulo y si es asi aplico las validaciones (si existen)
          if ($value){
            //Log::info($value);
            // $toValidate = json_decode($value);
            // $toValidate = (array) $toValidate;
            $validator = Validator::make(['value' => $value], [
              'value' =>  'image'
            ]);
            if($validator->fails()) return response()->json("La variable $label debe ser una imagen", 400);
            $uploadedFile = $value;
            $filename = time().$uploadedFile->getClientOriginalName();

            $result = Storage::disk('local')->putFileAs(
              'uploads/images',
              $uploadedFile,
              $filename
            );
            //Actualizo el valor
            $envs[$envKey]['value'] = $result;
          }
        }
      }
    }
    if (!$app->saveEnvs($envs)) return response()->json("Error al modificar envs",500);
    return response()->json('Variables de entorno modificadas con exito',200);
  }

  //# Functions of init money
    public static function verifyExpiredInitMoney($date_expiration) {
      $date = Carbon::now();
      $date = $date->subDay()->toDateTimeString();
      if($date_expiration == null || $date_expiration <= $date) return true;
      else return false;
    }

    public function getInitMoney(Request $request) {
      $app = CurrentApp::App();
      $myData = false;

      if($app['init_money'] == null || Self::verifyExpiredInitMoney($app['init_money_expiration'])){
        $myData = [
          'init_money'            => $app['init_money'],
          'init_money_expiration' => $app['init_money_expiration']
        ];

      }
      return response()->json($myData,200);
    }

    public function setInitMoney(Request $request) {
      $app = CurrentApp::App();
      $_request = $request->all();

      if($app['init_money'] == null || Self::verifyExpiredInitMoney($app['init_money_expiration'])){
        $app = Aplication::find($app['id']);
        if(!$app) return response()->json("Aplicacion no encontrada",404);

        $app->init_money = $_request['init_money'];
        $app->init_money_expiration = Carbon::now()->toDateTimeString();
        if(!$app->save()) return response()->json("Error del servidor",500);
      }

      return response()->json("Monto inicial modificado con exito",200);
    }
  //# Functions of init money

  // REPOSTERIA
  public function getAppsPedidosNewReposteria(Request $request, RequestsController $RequestsController) {
    $currentApp = CurrentApp::App();
    $user = Auth::user();
    $appKey = $request->header('App-Key');

    // SEGURIDAD: Llaves especiales pueden ver todas las aplicaciones sin autenticación de usuario
    $specialKeys = ['1455-93EC-02ED-41A8-735D', '13E3-F7FB-35EB-E7CE-3541']; // super y admin
    
    // Si es llave especial, no requiere usuario autenticado
    if (in_array($appKey, $specialKeys)) {
        $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
    } 
    // Si no es llave especial, verificar autenticación de usuario
    else {
        // Si no hay usuario autenticado, denegar acceso
        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }
        
        // Si es usuario maestro, puede ver todas las aplicaciones
        if ($user->username === 'master') {
            $apps = Aplication::where('client',$currentApp->client)->where('active',1)->with('database')->get();
        } else {
            $apps = collect([$currentApp]); // Solo la aplicación actual para usuarios normales
        }
    }

    foreach ($apps as $app) {
        $connection = new ConectionDB($app);
        $connection->set_database($app->database);
        $connection->ChangeDBToApp($app, $reconect = true);
        
        $cantPedidosNew = $RequestsController->getCantPedidosNewReposteria();
        
        $app->pedidosNew = $cantPedidosNew;
    }

    return response()->json($apps);
  }

}