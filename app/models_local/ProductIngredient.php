<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

use App\models_local\Ingredient;
use App\models_local\ProductIngredient;

class ProductIngredient extends Model
{
    protected $connection = 'mysql_local';
    protected $table = 'product_ingredients';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'ingredient_id',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'ingredient_id' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }
}
