<?php

use App\Helpers\CurrentApp;
use Illuminate\Support\Facades\Log;

//$currentApp = CurrentApp::Current();


$env = (env('APP_ENV', 'local') === 'production') ? 'prod' : 'dev' ;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/*Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});*/

Broadcast::channel($env.'-app.{appSerial}.cafeteria.boards', function ($user, $appSerial) {
    Log::info('CHANNEL cafeteria.boards ATTEMPT');
    if ((string) CurrentApp::Serial() !== (string) $appSerial) return false;
    //return (int) $user->id === (int) $id;
    return CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.ajustes.pusher_caffeteria');
});

Broadcast::channel($env.'-app.{appSerial}.cafeteria.orders', function ($user, $appSerial) {
    Log::info('CHANNEL cafeteria.orders ATTEMPT');
    if ((string) CurrentApp::Serial() !== (string) $appSerial) return false;
    //return (int) $user->id === (int) $id;
    return CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.ajustes.pusher_caffeteria');
});
