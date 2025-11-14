<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use App\models_local\Order;

class Waiter extends Model
{
  protected $connection = 'mysql_local';
  protected $table = 'waiters';

  protected $fillable = [
      'name',
      'user_id',
      'trash'
  ];

  static public function getWaitersOfBoards($boards){
    foreach ($boards as $board){
      if($board->order) $board->waiter = Waiter::find($board->order->waiter_id);
      else $board->waiter = null;
    }
    return $boards;
  }

  static public function getWaiterOfBoard($board){
    if($board->order) $board->waiter = Waiter::find($board->order->waiter_id);
    else $board->waiter = null;
    return $board;
  }

  static public function getStatesWaiters($waiters){
    foreach ($waiters as $waiter) {
      $order = Order::where('waiter_id', $waiter->id)->where('state', 'en espera')->first();
      if($order) $waiter->state = 'ocupado';
      else $waiter->state = 'libre';
    }
    return $waiters;
  }
}
