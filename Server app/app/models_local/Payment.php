<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CurrentApp;

class Payment extends Model
{
  protected $connection = 'mysql_local';
  protected $table = 'payments';

  protected $fillable = [
    'amount',
    'description',
    'currency',
    'contact',
    'extra_data',
    'transfers',
    'request_id',
  ];

  public function request()
    {
        return $this->belongsTo(Requests::class, 'request_id');
    }

}