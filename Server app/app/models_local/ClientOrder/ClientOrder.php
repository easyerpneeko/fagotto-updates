<?php

namespace App\models_local\ClientOrder;

use Illuminate\Database\Eloquent\Model;
use App\models_local\Client;

class ClientOrder extends Model
{
  protected $connection = 'mysql_local';
  protected $fillable = [
    'orderable_type',
    'orderable_id',
    'client_id'
  ];
  // Get the child orderable model (momently only PhoneRepairOrder).
  public function orderable()
  {
      return $this->morphTo(__FUNCTION__, 'orderable_type', 'orderable_id');
  }
  // Get client
  public function client()
  {
      return $this->belongsTo(Client::class, 'client_id');
  }

}
