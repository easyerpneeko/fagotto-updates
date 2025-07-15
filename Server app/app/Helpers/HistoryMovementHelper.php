<?php namespace App\Helpers;

use App\models_local\HistoryMovement;
use App\Helpers\CurrentApp;
use Illuminate\Support\Facades\Log;

class HistoryMovementHelper
{
    public static function create($event, $title, $info = [], $data = [], $type = null) {
      Log::info('HistoryMovementHelper::create');
      if (CurrentApp::ConfStr('modulos.historial_movimi')) {
        Log::info('HistoryMovementHelper::create#2');
        HistoryMovement::create([
          'event' => $event,
          'title' => $title,
          'history_type' => null,
          'entry_info' => json_encode($info),
          'entry_data' => json_encode($data)
        ]);
      }
    }
}
