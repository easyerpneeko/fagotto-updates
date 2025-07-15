<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubModules extends Model
{
    protected $table = 'submodules';
    protected $connection = 'mysql';
    public function getDependencies() {
      return json_decode(str_replace("'",'"',$this->dependencies),1);
    }
    public function getMigrations() {
      return json_decode(str_replace("'",'"',$this->migrations),1);
    }
    public static function FindKey($key) {
      return SubModules::where('keyname',$key)->first();
    }
}
