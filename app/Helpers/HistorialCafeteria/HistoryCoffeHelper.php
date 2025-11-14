<?php namespace App\Helpers\HistorialCafeteria;

use App\Helpers\HistoryMovementHelper;
use App\models_local\Order;
use App\models_local\Waiter;
use Auth;
use Illuminate\Support\Facades\Log;

class HistoryCoffeHelper
{
    public static function create($event, $title, Order $order, $info = []) {
      Log::info('HistoryCoffeHelper::create');
      if ($order->waiter_id) $waiter = Waiter::find($order->waiter_id);
      else $waiter = null;

      $data = [
        'product_snapshot' => $order->products,
        'gananciaTotal' => $order->gananciaTotal,
        'total' => $order->total,
        'waiter_id' => $order->waiter_id,
        'board_id' => $order->board_id,
        'user_id' => (Auth::user()) ? Auth::user()->id : null,
      ];

      $info[] = ['Total de orden', $order->total.' $'];
      $info[] = ['Ganancia de orden', $order->gananciaTotal.' $'];
      $info[] = ['Mesa de orden', $order->board_id];
      $info[] = ['Cajero/a de orden', ($waiter) ? $waiter->name : '(Ninguno)'];
      $info[] = ['Usuario', (Auth::user()) ? Auth::user()->fullname : '(Sin identificar)'];

      HistoryMovementHelper::create($event, $title, $info, $data);
      //$products = $order->products;
    }
}
