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

class Setting_submodules_modules_apps extends Model
{
  protected $connection = 'mysql';
  public static function getSettingsOfSubModulesAppByRel($RelID, $slim = false) {
    //Relacion RelSettings-Modules ($SettingsModules)
    $selectArray = ['settings_submodules.keyname as SettingKey',
    'setting_submodules_modules_apps.id as RelSettingID',
    'setting_submodules_modules_apps.active as SettingActive'];

    if (!$slim) {
      $selectArray[] = 'settings_submodules.id as SettingID';
      $selectArray[] = 'settings_submodules.name as SettingName';
    }

    return Setting_submodules_modules_apps::select($selectArray)
                  ->join('settings_submodules','settings_submodules.id','setting_submodules_modules_apps.setting_submodule_id')
                  ->where('submodule_module_apps_id',$RelID)->get();
  }

  public static function getSettingsSubModules($RelID, $slim = false) {
    $SettingsSubModules = Setting_submodules_modules_apps::getSettingsOfSubModulesAppByRel($RelID, $slim);
    $SettingsSubModulesReturned = [];
    foreach ($SettingsSubModules as $RelSetting) {

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

      $SettingsSubModulesReturned[] = $toArray;
    }

    return $SettingsSubModulesReturned;
  }

  public static function isInstalled($submoduleApp,$Setting) {
    return Self::where('submodule_module_apps_id',$submoduleApp->id)
    ->where('setting_submodule_id',$Setting->id)->first();
  }

  public static function createNew($submoduleApp,$Setting) {
    $ins = new Self;
    $ins->submodule_module_apps_id = $submoduleApp->id;
    $ins->setting_submodule_id = $Setting->id;
    $ins->active = false;
    return $ins->save();
  }

  public function setValue($value) {
    $this->active = $value;
    return $this->save();
  }

  public function emptySetting() {
    return $this->delete();
  }

}
