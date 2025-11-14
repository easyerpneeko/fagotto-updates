<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Model;

//Main
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

class Settings_modules_apps extends Model
{
  protected $connection = 'mysql';
  public static function getSettingsOfModulesAppByRel($RelID,$slim = false) {
    //Relacion RelSettings-Modules ($SettingsModules)
    $selectArray = ['settings_modules.keyname as SettingKey',
    'settings_modules_apps.id as RelSettingID',
    'settings_modules_apps.active as SettingActive'];

    if (!$slim) {
      $selectArray[] = 'settings_modules.id as SettingID';
      $selectArray[] = 'settings_modules.name as SettingName';
    }

    return Settings_modules_apps::select($selectArray)
                  ->join('settings_modules','settings_modules.id','settings_modules_apps.settings_module_id')
                  ->where('modules_apps_id',$RelID)->get();
  }

  public static function getSettingsModules($RelID,$slim = false) {
    $SettingsModules = Settings_modules_apps::getSettingsOfModulesAppByRel($RelID,$slim);
    $SettingsModulesReturned = [];
    foreach ($SettingsModules as $RelSetting) {

      //Estableciendo todo el setting
      $toArray = [
                    'key'       => $RelSetting['SettingKey'],
                    'active'    => $RelSetting['SettingActive'],
                    'relid'     => $RelSetting['RelSettingID']
                  ];

      if (!$slim) {
        $toArray['id']          = $RelSetting['SettingID'];
        $toArray['name']        = $RelSetting['SettingName'];
      }

      $SettingsModulesReturned[] = $toArray;
    }

    return $SettingsModulesReturned;
  }

  //Unused
  public static function getInstalledsInModuleApp($moduleApp) {
    $settingsInstalled = Settings_modules_apps::where('modules_apps_id',$moduleApp->id)->get();
    return $settingsInstalled;
  }

  public static function isInstalled($moduleApp,$Setting) {
    return Self::where('modules_apps_id',$moduleApp->id)
    ->where('settings_module_id',$Setting->id)->first();
  }

  public static function createNew($moduleApp,$Setting) {
    $ins = new Self;
    $ins->modules_apps_id = $moduleApp->id;
    $ins->settings_module_id = $Setting->id;
    $ins->active = false;
    return $ins->save();
  }

  //Te lo dije v:
  public function setValue($value) {
    $this->active = $value;
    return $this->save();
  }

  public function emptySetting() {
    return $this->delete();
  }

}
