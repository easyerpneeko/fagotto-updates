<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CurrentApp;

class PaymentReposteria extends Model
{
  protected $connection = 'mysql_local';
  protected $table = 'payments_reposteria';

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
      return $this->belongsTo(RequestsReposteria::class, 'request_id');
  }

}