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
use App\models_local\Order;
use App\models_local\Sell;
// Helpers
use App\Helpers\CurrentApp;

class OrderProccesed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $order;
    private $sell;
    private $currentApp;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order, Sell $sell)
    {
        $this->order = $order;
        $this->sell = $sell;
        $this->currentApp = CurrentApp::Current();
        $this->dontBroadcastToCurrentUser();
    }
    
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $env = (env('APP_ENV', 'local') === 'production') ? 'prod' : 'dev' ;
        return new PrivateChannel($env.'-app.'.$this->currentApp->code.'.cafeteria.orders');
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
        return ['order' => $this->order, 'sell' => $this->sell];
    }

    public function broadcastAs()
    {
        return 'order-proccesed';
    }
}
