<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\models_local\UserApp;

class Operation extends Model
{
    use SoftDeletes;
    
    protected $connection = 'mysql_local';

    protected $table = 'operations';

    protected $fillable = [
        'name',
        'rut',
        'factura',
        'company_name',
        'receptor',
        'observation',
        'fecha',
        'total',
        'user',
        'operations_categories_id',
        'operations_subcategories_id',
    ];

    public function user()
    {
        return $this->belongsTo(UserApp::class);
    }
    public function subcategories()
    {
        return $this->belongsTo(OperationSubcategory::class, 'operations_subcategories_id');
    }
    

    public function categories()
    {
        return $this->belongsTo(OperationCategory::class, 'operations_categories_id');
    }
}