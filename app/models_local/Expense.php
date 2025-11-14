<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $connection = 'mysql_local';
    protected $table = "expenses";
    protected $fillable = [
      'name',
      'balance',
    ];

}
