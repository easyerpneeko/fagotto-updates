<?php namespace App\Classes\ClientOrders\PhoneRepairOrder;

// Illuminate
use Illuminate\Support\Facades\Log;
// Modelos
use App\models_local\ClientOrder\PhoneRepairOrder;
use App\models_local\ClientOrder\ClientOrder;
use App\models_local\Client;
// Helpers
use App\Helpers\CurrentApp;
// Laravel
use Illuminate\Support\Facades\Storage;
// Others
use Dompdf\Dompdf;
use PDF;

class Printer {

  public function generate64(PhoneRepairOrder $order){
    $pdf = PDF::loadView('client_orders/repair_phone', [
      'order' => $this->getData($order),
    ])->setPaper($this->getSize());
    $this->storePDFDocument($pdf);
    return $this->getBase64($pdf);
  }

  private function getData($order) {
    $order->envs = $this->getEverioments();
    return $order;
  }

  private function getSize() {
    $size = array(0,0,227,600);
    return $size;
  }

  private function getEverioments() {
    return json_decode(CurrentApp::App()->environment_vars);
  }

  private function getBase64($pdf) {
    $b64Doc = chunk_split(base64_encode($pdf->output()));
    return $b64Doc;
  }

  private function storePDFDocument($pdf) {
    Storage::put($this->generateDocumentName(), $pdf->output());
  }

  private function generateDocumentName() {
    return 'orden_phonerepair_'.uniqid().'.pdf';
  }

}
