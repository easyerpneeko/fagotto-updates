<?php namespace App\Http\Controllers\Controllers_local\ClientOrders\PhoneRepairOrder;

// Helpers
use App\Helpers\MPage;
use App\Helpers\CurrentApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\models_local\ClientOrder\PhoneRepairOrder;
use DateTime;

class Paginator
{

  protected $request;

  public function __construct(Request $request) {
    $this->request = $request;
  }

  // paginate
  public function paginate($config = null) {
    $query = $this->getQuery();
    $config = $this->getConfig($config);
    $query = $this->getDateParameters($query);
    $query = $this->getSearchParameters($query);

    $paginate = MPage::paginate(
      $query,
      $this->request,
      15,
      '',
      'phone_repair_orders',
      $config['ordenamients'],
      $config['filters'],
      false,
      function($item) {
        return $item->populate();
      }
    );

    return $paginate;
  }

  private function getQuery() {
    $query = PhoneRepairOrder::select(
              'phone_repair_orders.*'
            )
            ->with('order')
            ->leftJoin('client_orders', 'client_orders.orderable_id', 'phone_repair_orders.id')
            ->leftJoin('clients', 'clients.id', 'client_orders.client_id');
    return $query;
  }

  private function getConfig($config = null) {
    if (!$config)
      $config = [
        'ordenamients' => ['id', 'created_at'],
        'filters' => [],
      ];
    return $config;
  }

  private function getDateParameters($query) {
    if ($this->request->input('todayOrders')) {
      $today = new DateTime(now());
      $today->setTime(00,00,00);
      $query->where('phone_repair_orders.created_at','>=', $today);
    }

    if ($this->request->input('startDate')) {
      $query->where('phone_repair_orders.created_at','>=', $this->request->input('startDate'));
    }
    if ($this->request->input('endDate')) {
      $query->where('phone_repair_orders.created_at','<=', $this->request->input('endDate'));
    }
    if ($this->request->has('orderBy_date')) {
      $query->orderBy('phone_repair_orders.created_at', $this->request->input('orderBy_date'));
    }
    return $query;
  }

  private function getSearchParameters($query) {
    if ($this->request->input('searchInOrder')) {
      $search_string = $this->request->input('searchInOrder');
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')){
        $query->where(function($query) use($search_string) {
          $query->where('clients.name', 'like', "%$search_string%")
                ->orWhere('clients.lastname', 'like', "%$search_string%")
                ->orWhere('clients.rut', 'like', "%$search_string%")
                ->orWhere('phone_repair_orders.id', $search_string);
        });
      }else{
        $query->where('phone_repair_orders.id',$search_string);
      }
    }
    return $query;
  }

}
