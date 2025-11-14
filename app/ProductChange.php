<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductChange extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'field_name',
        'old_value',
        'new_value',
    ];
}