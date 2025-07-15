<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Model;

//Main
use App\Aplication;
use App\Modules;
use App\SubModules;

//Settings
use App\Settings_modules;
use App\Settings_submodules;

//Relaciones
use App\Relations\Modules_apps;
use App\Relations\Setting_submodules_modules_apps;
use App\Relations\Settings_modules_apps;
use App\Relations\Submodules_modules_apps;

//
use App\Helpers\ConectionDB;


class Submodules_modules_apps extends Model
{
  protected $connection = 'mysql';
  public static function getSubModules($RelID,$slim = false) {

    //Relacion RelModuleApps-Submodulos ($SubModulesApps)
    $selectArray = ['submodules.keyname as SubModuleKey',
    'submodules_modules_apps.id as RelSubID',
    'submodules_modules_apps.version as SubModuleVersion'];

    if (!$slim) {
      $selectArray[] = 'submodules.id as SubModuleID';
      $selectArray[] = 'submodules.name as SubModuleName';
      $selectArray[] = 'submodules.description as SubModuleDescription';
      $selectArray[] = 'submodules.permissions as Permisos';
    }
    return Submodules_modules_apps::select($selectArray)
                  ->join('submodules','submodules.id','submodules_modules_apps.submodule')
                  ->where('module_app_id',$RelID)->get();

  }

  public static function getSubModulesProcess($RelID,$slim = false) {
    $SubModulesApps = Submodules_modules_apps::getSubModules($RelID,$slim);
    $SubModulesReturned = [];
    foreach ($SubModulesApps as $Rel) {

      //Estableciendo todo en el submodule actual
      $toArray = [
                    'key'         => $Rel['SubModuleKey'],
                    'version'     => $Rel['SubModuleVersion'],
                    'relid'       => $Rel['RelSubID'],
                    'settings'    => Setting_submodules_modules_apps::getSettingsSubModules($Rel['RelSubID'],$slim)
                  ];

      if (!$slim) {
        $toArray['id']          = $Rel['SubModuleID'];
        $toArray['name']        = $Rel['SubModuleName'];
        $toArray['description'] = $Rel['SubModuleDescription'];
        $toArray['permisos']    = json_decode($Rel['Permisos'], 1);
      }

      $SubModulesReturned[] = $toArray;

    }

    return $SubModulesReturned;
  }

  public static function newInstall($app,$appRel,$submodule,$executeMigration = false){

    $newRelation = new Self;
    $newRelation->module_app_id = $appRel->id;
    $newRelation->submodule = $submodule->id;
    $newRelation->version = $submodule->version;
    if ($executeMigration) {
      ConectionDB::ChangeDBToApp($app);
      ConectionDB::Migrations_database($submodule->getMigrations());
    }

    $SubModuleApp = $newRelation->save();
    if (!$SubModuleApp) return $SubModuleApp;

    $newRelation->updateSubModule();
    return $newRelation;

  }

  public function executateMigration() {

    //Obtengo el submodulo
    $submodule = SubModules::find($this->submodule);
    //Obtengo la relacion moduleApp
    $moduleApp = Modules_apps::find($this->module_app_id);
    //Obtengo App
    $app = Aplication::find($moduleApp->app_id);
    //Me conecto a dicha BD
    ConectionDB::ChangeDBToApp($app);
    //Ejecuto las migraciones que obtengo del submodulo
    ConectionDB::Migrations_database($submodule->getMigrations());
    return $this;

  }

  public function updateSubModule() {

    $submodule = SubModules::find($this->submodule);

    $this->updateSettingList();
    $this->updateEnvs();

    $this->version = $submodule->version;

    return $this->save();

  }

  public function updateEnvs() {

    $submodule = SubModules::find($this->submodule);
    $app = Aplication::find(Modules_apps::find($this->module_app_id)->app_id);

    if (!$submodule->env_vars) return $this;

    $envs = json_decode($submodule->env_vars, 1);
    foreach ($envs as $envKey => $env) {
      if (!$app->hasEnv($envKey)) {
        $app->createEnv($envKey,$env);
      }
    }

    return true;

  }

  public function updateSettingList() {
    $settings = Settings_submodules::getOfSubModuleID($this->submodule);
    foreach ($settings as $setting) {
      if (!$this->haveSetting($setting)) {
        Setting_submodules_modules_apps::createNew($this,$setting);
      }
    }
    return $this;
  }

  public function haveSetting($setting) {
    return Setting_submodules_modules_apps::isInstalled($this,$setting);
  }

  public function uninstall($force = 0) {

    if (!$force) {
      $submodulesinApp = Submodules_modules_apps::where('module_app_id',$this->module_app_id)->get();
      foreach ($submodulesinApp as $submoduleInApp) {
        if (in_array($this->keyname,$submoduleInApp->getDependencies())) {
          return [false,"El modulo '$submoduleInApp->name' depende del modulo el cual desea desinstalar, debe desinstalar '$submoduleInApp->name' primero",400];
        }
      }
    }

    $settings = Setting_submodules_modules_apps::where('submodule_module_apps_id',$this->id)->get();
    foreach ($settings as $setting) {
      $setting->emptySetting();
    }

    if (!$this->delete()) return [false,'Error de base de datos',500];
    return [true,'Desinstalacion exitosa',200];

  }

  public function getDependencies() {
    $submodule = SubModules::find($this->submodule);
    return json_decode(str_replace("'",'"',$submodule->dependencies),1);
  }
  public function getMigrations() {
    $submodule = SubModules::find($this->submodule);
    return json_decode(str_replace("'",'"',$submodule->migrations),1);
  }
  public function getKeynameAttribute() {
    $submodule = SubModules::find($this->submodule);
    return $submodule->keyname;
  }
  public function getNameAttribute() {
    $submodule = SubModules::find($this->submodule);
    return $submodule->name;
  }

}
