<?php

namespace App\models_local;

use App\models_local\Product;
use App\models_local\Sell;
use App\models_local\UserApp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devolution extends Model{
    
    use SoftDeletes;
    
    protected $connection = 'mysql_local';

    protected $table = 'devolutions';
    protected $fillable = ['reason', 'product', 'stock', 'sell', 'user'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function sell()
    {
        return $this->belongsTo(Sell::class);
    }
    
    public function user()
    {
        return $this->belongsTo(UserApp::class);
    }
}