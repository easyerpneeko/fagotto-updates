<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Broadcast::routes(['middleware' => ['AppSecurity', 'JwtMiddleware']]);
        Broadcast::routes(['prefix' => 'api', 'middleware' => ['api', 'AppSecurity', 'JwtMiddleware']]);

        require base_path('routes/channels.php');
    }
}
