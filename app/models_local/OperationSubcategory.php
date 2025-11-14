<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationSubcategory extends Model
{
  use SoftDeletes;

  protected $connection = 'mysql_local';

  protected $table = 'operations_subcategories';

  protected $fillable = [
    'name',
    'description',
    'operations_categories_id',
    'status',
    'user',
  ];

  public function category()
  {
    return $this->belongsTo(OperationCategory::class, 'operations_categories_id');
  }

  public function user()
  {
    return $this->belongsTo(UserApp::class);
  }

  public function operations()
  {
      return $this->hasMany(Operation::class, 'operations_subcategories_id');
  }
}
