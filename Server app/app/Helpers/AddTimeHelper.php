<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Aplication;
use Carbon\Carbon;

class AddTimeHelper
{

  public $time = null;
  public $app = null;
  public $type = null;

  function __construct($data, $id) {
    $this->time = $data['expiration'];
    $this->type = $data['type'];
    $this->app = Aplication::find($id);
  }

  //Verificando cual opcion escogio el cliente
  public function addTime(){

    $changeReason = '-';

    if($this->type && $this->type == 1)
      $changeReason = '+';

    $changeStr = $changeReason.$this->time;

    $actualTime = strtotime($this->app->expiration);
    $timeDay = strtotime(Carbon::now());


    if($actualTime < $timeDay){
      $actualTime = null;
    }

    if( $actualTime == null ){
      $newTime = Date("Y-m-d H:i:s", strtotime($changeStr, $timeDay));
    }else{
      $newTime = Date("Y-m-d H:i:s", strtotime($changeStr, $actualTime));
    }



    if(strtotime($newTime) < strtotime(Carbon::now())){
      $this->app->expiration = null;
      return $this->app->save();
    }

    $this->app->expiration = $newTime;



    return $this->app->save();
  }

}
