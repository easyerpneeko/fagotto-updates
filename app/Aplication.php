<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Helpers\ConectionDB;
use Illuminate\Support\Facades\DB;
use Config;

//Main
use App\Modules;
use App\SubModules;
use App\Client;
use App\DataBase;

//Settings
use App\Settings_modules;
use App\Settings_submodules;

//Relaciones
use App\Relations\Modules_apps;
use App\Relations\Setting_submodules_modules_apps;
use App\Relations\Settings_modules_apps;
use App\Relations\Submodules_modules_apps;

use App\models_local\TypeUser;
use App\models_local\UserApp;


use Keygen;

define('enverioment_default',[
  'enverioments_version' => [
     'value' => '0.1',
     'label' => 'Version de las variables de ambito',
     'disabled' => true,
     'hidden' => true,
     'type' => 'version'
  ]
]);

define('permissions_default',[
  'tipos_usuarios_gestion'  => 'Gestionar tipos de usuarios', //Mas peligrosa
  'usuarios_gestion'        => 'Gestionar usuarios',
  'usuarios_obtener'        => 'Obtener usuarios',
  'mi_privilegio_global'    => 'Mi privilegio disponible globalmente',
]);

class Aplication extends Model
{
  protected $connection = 'mysql';
  protected $fillable = [
    'name',
    'name_public',
    'serial',
    'database_app',
    'client',
    'expiration',
    'environment_vars',
    'init_money',
    'init_money_expiration'
  ];

  public function database()
  {
      return $this->belongsTo('App\DataBase', 'database_app');
  }

  public function getEnvs($arg = 1){
    return json_decode($this->environment_vars,$arg);
  }

  public function saveEnvs($envs){
    $this->environment_vars = json_encode($envs);
    return $this->save();
  }

  //Verificar que existen variables de entorno en base a su nombre llave
  public function hasEnv($envKey) {

    //var_dump($this->environment_vars);

    if (!$this->environment_vars)
      $this->environment_vars = '[]';

    //var_dump($this->environment_vars);exit();

    $vars = json_decode($this->environment_vars,1);
    foreach ($vars as $varKey => $var) {
      if ($varKey == $envKey) return true;
    }

    return false;

  }

  //Crear nueva variable de entorno en base al nombre llave y los datos de la misma
  public function createEnv($envKey,$envData) {

    //Decodifico como arreglo las variables que ya hay
    $vars = json_decode($this->environment_vars,1);

    //Transformo los datos de la ENV a Arreglo en caso de no estarlo
    $envData = json_decode(json_encode($envData),1);

    //Inserto la nueva Env con su EnvKey y su EnvData
    $vars[$envKey] = $envData;

    //Si hay un valor por defecto en el envData pongo dicho valor, si no, es nulo
    $vars[$envKey]['value'] = (isset($envData['default'])) ? $envData['default'] : null;

    //Codifico el arreglo de variables
    $varsJson = json_encode($vars);

    //Lo guardo
    $this->environment_vars = $varsJson;
    return $this->save();

  }

  public function installModule($moduleID) {

    $module = Modules::find($moduleID);

    //Comprueba si tengo las dependencias necesarias para instalar el modulo
    $eIND = $this->errorIfNotDependencies($module);
    //Si no es asi, retorna [false,"Debe instalar antes... <modulos>"]
    if (!$eIND[0]) return response()->json($eIND[1],400);

    //Comprueba si el modulo ya esta instalado
    if ($this->haveModule($module->keyname))
      return response()->json("El modulo '$module->name' ya se encuentra instalado.",400);

    $newInstall = Modules_apps::newInstall($this,$module,true);

    if (!$newInstall) return response()->json("El modulo '$module->name' no se pudo instalar.",400);

    return response()->json("El modulo '$module->name' se instalo con exito.",200);

  }

  public function installSubModule($smoduleID) {

    $submodule = SubModules::find($smoduleID);
    $module = Modules::find($submodule->module_id);

    //Comprueba si no se tiene el modulo del submodulo instalado
    $moduleApp = $this->haveModuleId($submodule->module_id);
    if (!$moduleApp)
      return response()->json("El submodulo '$submodule->name', requiere al submodulo '$module->name' para ser instalado.",400);

    //Comprueba si tengo las dependencias necesarias para instalar el modulo
    $eIND = $this->errorIfNotSubDependencies($submodule);
    //Si no es asi, retorna [false,"Debe instalar antes... <modulos>"]
    if (!$eIND[0]) return response()->json($eIND[1],400);

    //Comprueba si el modulo ya esta instalado
    if ($this->haveSubModule($submodule->keyname,$moduleApp->id))
      return response()->json("El submodulo '$submodule->name' ya se encuentra instalado.",400);

    // Comprueba si es el submodulo es venta rapida para ejecutar seeder de prodcuto para venta rapida
    if($submodule->keyname === 'sell_fast'){
      ConectionDB::ChangeDBToApp($this);
      //Ejecuto las seeders para los productos
      ConectionDB::Seeder_database('ProductsFastSell');
    }

    $newInstall = Submodules_modules_apps::newInstall($this,$moduleApp,$submodule,true);

    if (!$newInstall) return response()->json("El submodulo '$submodule->name' no se pudo instalar.",400);

    return response()->json("El submodulo '$submodule->name' se instalo con exito.",200);

  }

  private function errorIfNotDependencies($module) {
    $dependencies = $this->comprobeDependencies($module);
    if (sizeof($dependencies)) {
      $strError = 'Se deben instalar antes los modulos: ';
      for ($i=0; $i < sizeof($dependencies); $i++) {
        $strError .= "'".Modules::FindKey($dependencies[$i])->name."'";
        if ($i > 0) $strError .= ',';
      }
      return [false,$strError];
    }

    return [true,'Se cumplen todas las dependencias'];
  }

  private function errorIfNotSubDependencies($submodule) {
    $dependencies = $this->comprobeSubDependencies($submodule);
    if (sizeof($dependencies)) {
      $strError = 'Se deben instalar antes los submodulos: ';
      for ($i=0; $i < sizeof($dependencies); $i++) {
        $strError .= "'".SubModules::FindKey($dependencies[$i])->name."'";
        if ($i > 0) $strError .= ',';
      }
      return [false,$strError];
    }

    return [true,'Se cumplen todas las dependencias'];
  }

  public function comprobeDependencies($module) {
    $needDependencies = [];
    $dependencies = $module->getDependencies();
    foreach ($dependencies as $dpKey) {
      if (!$this->haveModule($dpKey)) {
        $needDependencies[] = $dpKey;
      }
    }
    return $needDependencies;
  }

  public function comprobeSubDependencies($submodule) {
    $needDependencies = [];
    $moduleApp = $this->haveModuleId($submodule->module_id);
    $dependencies = $submodule->getDependencies();
    foreach ($dependencies as $dpKey) {
      if (!$this->haveSubModule($dpKey, $moduleApp->id)) {
        $needDependencies[] = $dpKey;
      }
    }
    return $needDependencies;
  }

  public function haveModule($key) {
    $module = Modules::FindKey($key);
    return $this->haveModuleId($module->id);
  }

  public function haveModuleId($id) {
    return Modules_apps::where('app_id',$this->id)->where('module_id',$id)->first();
  }

  public function haveSubModule($key,$relID) {
    $submodule = SubModules::FindKey($key);
    return $this->haveSubModuleId($submodule->id,$relID);
  }

  public function haveSubModuleId($id,$relID) {
    return Submodules_modules_apps::where('module_app_id',$relID)->where('submodule',$id)->first();
  }

  public function getApp($slim = false) {

    $Config = [];
    $Config['Id'] = $this->id;

    $Config['name_public'] = $this->name_public;
    $Config['Name'] = $this->name;

    if (!$slim) {
      $Config['Active'] = $this->active;
      $Config['Serial'] = $this->serial;
      $Config['Client'] = Client::where('id', $this->client)->first();
      $Config['Expiration'] = $this->expiration;
      $Config['SizeDB'] = ConectionDB::SizeIfExistsDB($this->database->name);
    }

    $Config['Entorno'] = json_decode($this->environment_vars,1);
    $Config['Modules'] = Modules_apps::getModulesByAppIdProcess($this->id,$slim);

    $Config['TypeUsers'] = TypeUser::getTypeUsersOfApp($this,$slim);

    if (!$slim) {
      $Config['Permisos'] = permissions_default;
      foreach ($Config['Modules'] as $module) {
        $Config['Permisos'] = array_merge($Config['Permisos'],$module['permisos']);
        if (isset($module['sub']) && $module['sub']) {
          foreach ($module['sub'] as $submodule) {
            $Config['Permisos'] = array_merge($Config['Permisos'],$submodule['permisos']);
          }
        }
      }
      //$Config['PermisosJSON'] = [];
    }

    return $Config;

  }

  public static function createAplication($request){

    $keysAllow = [
      'name',
      'name_public',
      'database_app',
      'client',
      'expiration'
    ];

    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }

    //Codigo unico de aplicacion
    $itemToSave['serial'] = Aplication::generateCode();

    //Inicializa el JSON de variables de entorno, ¿que que es una variable de entorno?
    /*
      Las variables de entorno son algo que no contemple pero que creo solo seran utiles
      al finaaaaaaaaal del proyecto, tranquilos no es la gran cosa.
    */
    $itemToSave['environment_vars'] = json_encode(enverioment_default);

    return Aplication::create($itemToSave);

  }

  //Esta logica debe ir dentro del modelo del crear aplicacion, no dentro del controlador
  public static function generateCode(){
    return Keygen::bytes()->generate(
      function($key) {
        // Generate a random numeric key
        $random = Keygen::numeric()->generate();

        // Manipulate the random bytes with the numeric key
        return substr(md5($key . $random . strrev($key)), mt_rand(0,8), 20);
      },
      function($key) {
        // Add a (-) after every fourth character in the key
        return join('-', str_split($key, 4));
      },
      'strtoupper'
    );
  }

  public function getDatabaseAttribute(){
    $database = DataBase::find($this->database_app);
    return $database;
  }

  public function getClienteAttribute() {
    return Client::find($this->client);
  }

  public function installKernel() {
    ConectionDB::ChangeDBToApp($this);
    /*Execute migrations of kernel*/
    $migrations = [
      'core/2020_02_03_152931_type_users.php',
      'core/2020_02_03_152933_users.php'
    ];
    ConectionDB::Migrations_database($migrations);
    $role = TypeUser::createRole('Administrador','admin');
    return UserApp::createUser($this->cliente->username,'admin',$this->cliente->email,$this->cliente->rut,$role->id);
  }

}
