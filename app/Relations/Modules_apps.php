<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Model;

//Main
use App\Aplication;
use App\Modules;
use App\SubModules;
use App\models_local\TypeUser;
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

class Modules_apps extends Model
{

    protected $connection = 'mysql';

    public static function getModulesByAppId($AppID,$slim = false) {

      //Relacion aplicaciones-modulos [RelModuleApps] ($ModulesApps)
      $selectArray = ['modules.keyname as ModuleKey',
      'modules_apps.id as RelID',
      'modules_apps.version as ModuleVersion'];

      if (!$slim) {
        $selectArray[] = 'modules.id as ModuleID';
        $selectArray[] = 'modules.name as ModuleName';
        $selectArray[] = 'modules.description as ModuleDescription';
        $selectArray[] = 'modules.permissions as Permisos';
      }

      return Modules_apps::select($selectArray)
                    ->join('modules','modules.id','modules_apps.module_id')
                    ->where('app_id',$AppID)->get();

    }

    public static function getModulesByAppIdProcess($AppID,$slim = false) {
      $ModulesApps = Modules_apps::getModulesByAppId($AppID,$slim);
      $ModulesReturned = [];
      foreach ($ModulesApps as $Rel) {

        //Estableciendo todo en el module actual
        $toArray = [
                      'key'         => $Rel['ModuleKey'],
                      'relid'       => $Rel['RelID'],
                      'version'     => $Rel['ModuleVersion'],
                      'sub'         => Submodules_modules_apps::getSubModulesProcess($Rel['RelID'],$slim),
                      'settings'    => Settings_modules_apps::getSettingsModules($Rel['RelID'],$slim)
                    ];

        if (!$slim) {
          $toArray['id']          = $Rel['ModuleID'];
          $toArray['name']        = $Rel['ModuleName'];
          $toArray['description'] = $Rel['ModuleDescription'];
          $toArray['permisos']    = json_decode($Rel['Permisos'], 1);
        }

        $ModulesReturned[] = $toArray;
      }

      return $ModulesReturned;
    }

    public static function newInstall($app,$module,$executeMigration = false){

      $newRelation = new Self;
      $newRelation->app_id = $app->id;
      $newRelation->module_id = $module->id;
      $newRelation->version = $module->version;

      if ($executeMigration) {
        ConectionDB::ChangeDBToApp($app);
        ConectionDB::Migrations_database($module->getMigrations());
      }

      ConectionDB::ChangeDBToApp($app);
      $roles = $module->getTypeUsers();
      foreach ($roles as $role) {
        $newRole = TypeUser::createRole($role['name'],$role['keyname'],json_encode($role['permisos']));
      }

      $moduleApp = $newRelation->save();
      if (!$moduleApp) return $moduleApp;

      $newRelation->updateModule();
      return $newRelation;

    }

    public function updateModule() {

      $module = Modules::find($this->module_id);

      $this->updateSettingList();
      $this->updateEnvs();

      $this->version = $module->version;

      return $this->save();

    }

    public function executateMigration() {

      //Obtengo el modulo
      $module = Modules::find($this->module_id);
      //Obtengo App
      $app = Aplication::find($this->app_id);
      //Me conecto a dicha BD
      ConectionDB::ChangeDBToApp($app);
      //Ejecuto las migraciones que obtengo del submodulo
      ConectionDB::Migrations_database($module->getMigrations());
      return $this;

    }

    public function updateSettingList() {
      //$newRelation->version = $module->version;
      $settings = Settings_modules::getOfModuleID($this->module_id);
      foreach ($settings as $setting) {
        if (!$this->haveSetting($setting)) {
          Settings_modules_apps::createNew($this,$setting);
        }
      }
      return $this;
    }

    public function updateEnvs() {

      $module = Modules::find($this->module_id);
      $app = Aplication::find($this->app_id);

      if (!$module->env_vars) return $this;

      $envs = json_decode($module->env_vars, 1);
      foreach ($envs as $envKey => $env) {
        if (!$app->hasEnv($envKey)) {
          $app->createEnv($envKey,$env);
        }
      }

      return true;

    }

    public function haveSetting($setting) {
      return Settings_modules_apps::isInstalled($this,$setting);
    }

    public function uninstall($force = 0) {

      if (!$force) {
        $modulesinApp = Modules_apps::where('app_id',$this->app_id)->get();
        foreach ($modulesinApp as $moduleInApp) {
          if (in_array($this->keyname,$moduleInApp->getDependencies())) {
            return [false,"El modulo '$moduleInApp->name' depende del modulo el cual desea desinstalar, debe desinstalar '$moduleInApp->name' primero",400];
          }
        }
      }

      $subModules = Submodules_modules_apps::where('module_app_id',$this->id)->get();
      $settings = Settings_modules_apps::where('modules_apps_id',$this->id)->get();

      foreach ($subModules as $subModule) {
        $subModule->uninstall(1);
      }
      foreach ($settings as $setting) {
        $setting->emptySetting();
      }

      if (!$this->delete()) return [false,'Error de base de datos',500];
      return [true,'Desinstalacion exitosa',200];

    }

    public function getDependencies() {
      $module = Modules::find($this->module_id);
      return json_decode(str_replace("'",'"',$module->dependencies),1);
    }

    public function getMigrations() {
      $module = Modules::find($this->module_id);
      return json_decode(str_replace("'",'"',$module->migrations),1);
    }

    public function getKeynameAttribute() {
      $module = Modules::find($this->module_id);
      return $module->keyname;
    }

    public function getNameAttribute() {
      $module = Modules::find($this->module_id);
      return $module->name;
    }

}
