<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class RequestsReposteria extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'requests_reposteria';

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
        return $this->hasOne(PaymentReposteria::class, 'request_id');
    }
  
}