<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use App\models_local\Order;

class Cobro extends Model
{
  protected $connection = 'mysql_local';
  protected $table = 'cobros';
  public $timestamps = true;

  protected $fillable = [
      'description',
      'percentage',
      'amount'
  ];
 
}
