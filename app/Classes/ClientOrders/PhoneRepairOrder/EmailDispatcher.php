<?php namespace App\Classes\ClientOrders\PhoneRepairOrder;

use Mail;
use App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\CurrentApp;
use App\Helpers\EmailSender;

class EmailDispatcher
{

  public function dispatchCreatedOrder(
    $order
  ) {
    return EmailSender::emailSend(
      'client_orders/repair_phone_email',
      ['order' => $this->getData($order)],
      $order->contact_email,
      $order->order->client->name.' '.$order->order->client->lastname,
      'Pedido creado'
    );
  }

  private function getData($order) {
    $order->envs = $this->getEverioments();
    return $order;
  }

  private function getEverioments() {
    return json_decode(CurrentApp::App()->environment_vars);
  }

}
