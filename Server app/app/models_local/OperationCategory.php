<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationCategory extends Model
{
    use SoftDeletes;
    
    protected $connection = 'mysql_local';

    protected $table = 'operations_categories';

    protected $fillable = [
        'name',
        'description',
        'user',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(UserApp::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'operations_categories_id');
    }

    public function subcategories()
    {
        return $this->hasMany(OperationSubcategory::class, 'operations_categories_id');
    }
}