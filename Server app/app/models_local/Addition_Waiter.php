<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class Addition_Waiter extends Model
{
  protected $connection = 'mysql_local';
  protected $table = 'additions_waiters';

  protected $fillable = [
      'name',
      'balance',
      'quantity',
      'waiter'
  ];
}
