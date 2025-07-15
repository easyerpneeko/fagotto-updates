<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class HistoryProduct extends Model
{
    protected $connection = 'mysql_local';
    
    protected $fillable = [
        'transaction',
        'type', 
        'product_id',
        'user_id'
    ];
}
