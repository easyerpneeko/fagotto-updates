<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings_modules extends Model
{
    protected $connection = 'mysql';
    public static function getOfModuleID($moduleID){
      $settings = Self::where('module_id',$moduleID)->get();
      return $settings;
    }
}
