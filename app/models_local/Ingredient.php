<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Ingredient extends Model
{   
    use SoftDeletes;
    protected $connection = 'mysql_local';
    protected $table = 'ingredients';

    protected $fillable = [
        'name',
        'image',
        'stock_quantity',
        'quantity_per_unit',
        'unit_of_measurement',
        'min_quantity',
        'category_id',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients', 'ingredient_id', 'product_id');
    }

}
