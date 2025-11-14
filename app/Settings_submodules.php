<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings_submodules extends Model
{
  protected $connection = 'mysql';
  public static function getOfSubModuleID($subModuleID){
    $settings = Self::where('submodule_id',$subModuleID)->get();
    return $settings;
  }
}
