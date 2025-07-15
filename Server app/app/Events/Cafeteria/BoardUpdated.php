<?php

namespace App\Events\Cafeteria;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Models
use App\models_local\Board;
// Helpers
use App\Helpers\CurrentApp;

class BoardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $board;
    private $currentApp;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Board $board)
    {
        $this->dontBroadcastToCurrentUser();
        $this->board = $board;
        $this->currentApp = CurrentApp::Current();
    }
    
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $env = (env('APP_ENV', 'local') === 'production') ? 'prod' : 'dev' ;
        return new PrivateChannel($env.'-app.'.$this->currentApp->code.'.cafeteria.boards');
    }

    // on an event class...
    public function broadcastWhen(){
        // check for whatever here
        return CurrentApp::ConfStr('modulos.cafeteria') && CurrentApp::ConfStr('modulos.cafeteria.ajustes.pusher_caffeteria');
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['board' => $this->board];
    }

    public function broadcastAs()
    {
        return 'board-updated';
    }
}
