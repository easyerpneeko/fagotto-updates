<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminFeed extends Model
{
    protected $connection = 'mysql';
    protected $table = 'admin_feeds';
    
    protected $fillable = [
      'title',
      'desc',
      'app_id',
    ];
}
