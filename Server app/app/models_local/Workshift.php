<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use App\models_local\UserApp;

class Workshift extends Model
{
    protected $connection = 'mysql_local';

    protected $fillable = [
        'start_workshift',
        'end_workshift',
        'init_money',
        'final_money',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(UserApp::class);
    }
}