<?php

namespace App\models_local\ClientOrder;

use Illuminate\Database\Eloquent\Model;

use App\models_local\UserApp as User;
use App\models_local\ClientOrder\ClientOrder;

//client_orders_mobile_devices
//client_orders

/*ENVS:*/
//client_orders_device_models
//client_orders_device_conditions
class PhoneRepairOrder extends Model
{
  protected $connection = 'mysql_local';
  protected $fillable = [
    'device_model',
    'device_condition',
    'device_failure',
    'device_imei',
    'device_password',
    'observations',
    'technician_id',
    'budget',
    'contact_email',
    'contact_phone'
  ];

  protected $casts = [
    'device_condition' => 'array',
  ];

  protected $attributes = [
    'device_condition' => '[]'
  ];

  // Get the ClientOrder Parent
  public function order()
  {
      return $this->morphOne(ClientOrder::class, 'orderable');
  }
  // Get client
  public function getClientAttribute()
  {
      if ($this->order)
        return $this->order->client;
      return null;
  }
  // Get technician
  public function technician()
  {
    return $this->belongsTo(User::class, 'technician_id');
  }
  // Model Populator
  public function populate() {
    $this->order = $this->order;
    $this->client = $this->client;
    $this->technician = $this->technician;
    return $this;
  }
}
