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
        'user_id',
        'category_id',
        'price_venta_old',
        'price_venta_new',
        'price_compra_old',
        'price_compra_new',
        'ganancia_old',
        'ganancia_new',
        'stock_old',
        'stock_new',
        'name_old',
        'name_new',
        'field_afected',
        'old_value',
        'new_value'
    ];
}
