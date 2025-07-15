<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    protected $connection = 'mysql';
    public function getDependencies() {
      return json_decode(str_replace("'",'"',$this->dependencies),1);
    }
    public function getMigrations() {
      return json_decode(str_replace("'",'"',$this->migrations),1);
    }
    public function getTypeUsers() {
      return json_decode(str_replace("'",'"',$this->typeUsers),1);
    }
    public static function FindKey($key) {
      return Modules::where('keyname',$key)->first();
    }
}
