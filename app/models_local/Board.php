<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use App\models_local\Order;

class Board extends Model
{
  protected $connection = 'mysql_local';
  protected $table = 'boards';

  protected $fillable = [
      'name',
      'trash',
      'color' //change_board_color
  ];
  // Obtener estado de varias mesas
  static public function getStatesBoards($boards){
    foreach ($boards as $board) {
      $order = Order::where('board_id', $board->id)->where('state', 'en espera')->first();
      if($order) {
        $board->state = 'ocupada';
        $board->order = $order;
      }else {
        $board->state = 'libre';
        $board->order = null;
      }
    }

    return $boards;
  }

  // Obtener solo las mesas ocupadas
  static public function getBoardsWaiting($boards){
    $boardsWaiting = [];
    foreach ($boards as $board) {
      $order = Order::where('board_id', $board->id)->where('state', 'en espera')->first();
      if($order) {
        $board->state = 'ocupada';
        $board->order = $order;
        $boardsWaiting[] = $board;
      }
    }
    return $boardsWaiting;
  }

  static public function getBoardWaiting($board){
    $order = Order::where('board_id', $board->id)->where('state', 'en espera')->first();
    if($order) {
      $board->state = 'ocupada';
      $board->order = $order;
    }
    return $board;
  }
}
