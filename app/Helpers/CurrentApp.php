<?php

namespace App\Helpers;

use App\DataBase;
use Artisan;
use Illuminate\Support\Facades\DB;
use Auth;
use App\models_local\TypeUser;

class CurrentApp
{

  public $app = null;
  public $code = null;
  public $config = null;

  function __construct() {
    $this->app = CurrentApp::App();
    $this->code = CurrentApp::Serial();
    CurrentApp::ConfigRenew();
    $this->config = CurrentApp::Config();
  }

  public static function Serial() {
    $serial = session('app-serial', null);
    if (!$serial) return response()->json('Codigo no encontrada, ¿Middleware "AppSecurity" no funcionando?',404);
    return $serial;
  }

  public static function App() {
    $app = session('app-current', null);
    if (!$app) return response()->json('Aplicacion no encontrado, ¿Middleware "AppSecurity" no funcionando?',401);
    return $app;
  }

  public static function Current() {
    return new CurrentApp();
  }

  public static function Config($notError = false) {
    $config = session('app-config', null);
    if (!$config) {
      if (!$notError)
        return response()->json('Config no cacheado, ¿Middleware "AppSecurity" no funcionando?',401);
      return null;
    }
    return $config;
  }

  public static function ConfigRenew() {
    session(['app-config' => CurrentApp::App()->getApp()]);
    return true;
  }

  public static function ConfEnv($key) {

    $appConfig = CurrentApp::Config();
    $envs = $appConfig['Entorno'];

    if (!isset($envs[$key])) return null;
    if (!isset($envs[$key]['value'])) return null;

    return $envs[$key]['value'];

  }

  public static function ConfModule($keyname,$appConfig = null) {

    if (!$appConfig)
      $appConfig = CurrentApp::Config();

    $modules = $appConfig['Modules'];

    $module = Self::arrfind($modules, function($item, $key) use ($keyname) {
      return ($item['key'] == $keyname);
    });

    return $module;

  }

  public static function ConfSubmodule($module, $keyname) {

    $submodules = $module['sub'];

    $submodule = Self::arrfind($submodules, function($item, $key) use ($keyname) {
      return ($item['key'] == $keyname);
    });

    return $submodule;

  }

  public static function ConfSetting($module, $keyname) {

    $settings = $module['settings'];

    $setting = Self::arrfind($settings, function($item, $key) use ($keyname) {
      return ($item['key'] == $keyname);
    });

    if (!$setting || !isset($setting['active'])) return $setting;
    return $setting['active'];

  }

  public static function ConfArrModule($strArray,$module) {

    $arrayLenght = sizeof($strArray);

    if ($strArray[0] == 'submodulo' || $strArray[0] == 'submodules' || $strArray[0] == 'sub' || $strArray[0] == 'modulo' || $strArray[0] == 'modules' || $strArray[0] == 'submodulos') $strArray[0] = 'submodule';
    if ($strArray[0] == 'ajuste' || $strArray[0] == 'ajustes' || $strArray[0] == 'setting') $strArray[0] = 'settings';

    switch ($strArray[0]) {
      case 'submodule':
        if ($arrayLenght <= 1) return $module['sub'];
        $submodule = Self::ConfSubmodule($module, $strArray[1]);
        if ($arrayLenght <= 2) return $submodule;
        return Self::ConfArrModule(Self::substr_array($strArray, 2),$submodule);
      break;
      case 'settings':
        if ($arrayLenght <= 1) return $module['settings'];
        if($module){
          $setting = Self::ConfSetting($module, $strArray[1]);
          if ($arrayLenght <= 2) return $setting;
          return $setting;
        }
        return false;
      break;
    }

    return false;

  }

  public static function ConfStr($str, $config = null) {

    $strArray = explode('.',$str);
    $arrayLenght = sizeof($strArray);

    if (!$config) $config = CurrentApp::Config(true);

    if (!$config) return null;


    if ($strArray[0] == 'modulo' || $strArray[0] == 'modules' || $strArray[0] == 'modulos') $strArray[0] = 'module';
    if ($strArray[0] == 'settings' || $strArray[0] == 'enverioments') $strArray[0] = 'envs';

    switch ($strArray[0]) {
      case 'module':
        if ($arrayLenght <= 1) return false;
        $module = Self::ConfModule($strArray[1],$config);
        if ($arrayLenght <= 2) return $module;
        return Self::ConfArrModule(Self::substr_array($strArray, 2),$module);
      break;
      case 'envs':
        if ($arrayLenght <= 1) return false;
        $env = Self::ConfEnv($strArray[1]);
        if ($arrayLenght <= 2) return $env;
        return $env;
      break;
    }

    return false;

  }

  public static function substr_array($array, $count = 1)
  {
      for ($i=0; $i < $count; $i++)
        if (isset($array[0])) $array = array_slice($array,1);
      return $array;
  }

  public static function arrfind($array, $callback, $initial=null)
  {
    foreach ($array as $key => $value) {
      if ($callback($value,$key)) $initial = $value;
    }
    return $initial;
  }

  public static function haveRolePermission($role,$permission, $config = null) {

    //Obtener configuracion
    if (!$config) $config = CurrentApp::Config(true);
    if (!$config) return null;

    //Si por alguna razon no existe 'TypeUsers' en configuracion, retornar falso
    if (!isset($config['TypeUsers'])) return null;

    //Buscar coincidencia
    $typeUser = null;
    foreach ($config['TypeUsers'] as $item)
      if ($item['keyname'] == $role) $typeUser = $item;

    //Si no se encontro ninguno, retornar falso
    if (!$typeUser) return null;

    //Si es nulo quiere decir que tiene todos los permisos, retornar true, automaticamente.
    if ($typeUser['permission'] === null || $typeUser['permission'] === 'null') return true;

    //Si el tipo de dato es un string, transformarlo a json
    if (gettype($typeUser['permission']) == 'string')
      $typeUser['permission'] = json_decode($typeUser['permission']);

    //Buscar que exista en el arreglo el permission que se esta comprobando.
    return (in_array($permission, $typeUser['permission']));

  }

  public static function havePermission($permission, $config = null, $user = null) {

    //Obtener configuracion
    if (!$config) $config = CurrentApp::Config(true);
    if (!$config) return null;

    if (!$user) $user = Auth::user();
    if (!$user) return false;

    $role = TypeUser::find($user->role)->keyname;

    return CurrentApp::haveRolePermission($role, $permission, $config);

  }

}
