<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;

class HistoryMovement extends Model
{
    protected $connection = 'mysql_local';

    protected $fillable = [
      'event',
      'title',
      'history_type',
      'entry_data',
      'entry_info',
    ];

    protected $casts = [
      'entry_data' => 'json', //free
      'entry_info' => 'array', // [title, content]
    ];

    protected $attributes = [
      'entry_data' => '[]',
      'entry_info' => '[]'
    ];
}
