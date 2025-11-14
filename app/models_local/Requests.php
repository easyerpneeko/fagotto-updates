<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    protected $connection = 'mysql_local';

    protected $fillable = [
        'contact_name',
        'contact_phone',
        'paymode',
        'payment_method',
        'invoice_type',
        'voucher',
        'status',
        'price',
        'subtotal',
        'iva',
        'emergency',
        'despacho',
        'products',
        'comment',
        'app_id'
    ];

    public function payment()
    {
        return $this->hasOne(Payment::class, 'request_id');
    }
  
}