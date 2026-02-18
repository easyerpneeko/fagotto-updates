<?php

namespace App\Exports;

use App\Helpers\CurrentApp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\MPage;
use Config;

// Models
use App\models_local\ProductSell;
use App\models_local\Product;
use App\models_local\UserApp;
use App\models_local\Client;
use App\models_local\Folio;
use App\models_local\Order;
use App\models_local\Sell;
use App\models_local\Waiter;

class SellsExport implements FromCollection,WithHeadings
{   
  public function __construct(Request $request)
  {
      $this->request = $request;
  }

    public function headings(): array
    {
      $headings = [
        'Usuario ',
        'Fecha de emisión ',
        'Total ',
        'Folio ',
        'Tipo de venta ',



      ];

      return $headings;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

      $database2 = Config::get('database.connections.mysql_local.database');
      $pquery = DB::table($database2 . '.sells')
        ->whereIn('sells.trash', [0, 1])
        ->leftJoin($database2 . '.users', 'users.id', 'sells.user')
        ->select('sells.*', 'users.fullname', 'users.avatar');
      if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
        $pquery->leftJoin($database2 . '.clients', 'clients.id', 'sells.client');
      }
      if (!$pquery) return response()->json('Error del servidor', 500);
  
      //Ordenamientos
      $orders = ['id', 'total', 'created_at', 'user'];
      // //Filtrados
      $filters = [];
  
  
      if ($this->request->input('todaySells')) {
        $today = new DateTime(now());
        $today->setTime(00, 00, 00);
        $pquery->where('sells.created_at', '>=', $today);
      }
      if ($this->request->input('startDate')) {
        $pquery->where('sells.created_at', '>=', $this->request->input('startDate'));
      }
      if ($this->request->input('endDate')) {
        $pquery->where('sells.created_at', '<=', $this->request->input('endDate'));
      }
      if ($this->request->has('orderBy_date')) {
        $pquery->orderBy('sells.created_at', $this->request->input('orderBy_date'));
      }
      if ($this->request->input('searchInSell')) {
        $name = $this->request->input('searchInSell');
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
          $sellId = DB::table($database2 . '.sells')->where('id', $name)->where('sells.trash', 0)->first();
          if (!$sellId) {
            $pquery->whereRaw("(clients.name like '%$name%' OR clients.lastname like '%$name%' OR clients.rut like '%$name%')");
          } else {
            $pquery->where('sells.id', $name);
          }
        } else {
          $pquery->where('sells.id', $name);
        }
      }
  
      $validOtherTypes = ['factura', 'boleta', 'nota_de_credito', 'other', 'guia_de_despacho', 'debito', 'transferencia', 'cheque', 'banco', 'amipass', 'multicaja', 'edenred', 'convenio_empresa', 'sodexo', 'efectivo', 'credito', 'guia_despacho', 'rappi', 'uber','junaeb',];
  
      foreach ($validOtherTypes as $type) {
        if ($this->request->input($type)) {
          $typeFilters[] = $type;
        }
      }
  
      if (!empty($typeFilters)) {
        $pquery->whereIn('sells.paymode', $typeFilters);
      }
      //Procesamiento individual de items
      $Paginated = MPage::paginate($pquery, $this->request, 999999, '', 'sells', $orders, $filters, false, null);
      foreach ($Paginated['items'] as $sell) {
        $user = DB::table($database2 . '.users')->where('id', $sell->user_trash)->first();
        // $sell->user_trash = $sell->user_trash;
        if (!empty($user)) {
          // $sell->user_trash = $user->fullname;
        }
        //Añadiendo productos individuales
        $products = [];
        $productSells = DB::table($database2 . '.products_sells')
          ->where('products_sells.sell', $sell->id)
          ->leftJoin($database2 . '.products', 'products.id', 'products_sells.product')
          ->select('products_sells.id', 'products_sells.price as totalPrice', 'products_sells.quantity', 'products_sells.unitary_price', 'products.name', 'products_sells.description_sii')
          ->get();
        foreach ($productSells as $productSell) {
          // ✅ FIX MERCHISE/CHEAF/COLACIÓN: Usar description_sii si el nombre del producto no existe
          if (empty($productSell->name) && !empty($productSell->description_sii)) {
            $productSell->name = $productSell->description_sii;
          }
          $products[] = $productSell;
        }
        $sell->products = $products;
  
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes')) {
          $client = DB::table($database2 . '.clients')->where('id', $sell->client)->first();
          if ($client) {
            // $sell->client = $client;
          }
        }
  
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
          //Definiendo si ya fue procesado como factura o boleta o guia de despacho
          $folio = Folio::where('sell_id', $sell->id)->first();
  
          if ($sell->other_type != null) {
            if ($sell->other_type == 'banco') $sell->type = 'Trasnbank';
            else $sell->type = $sell->other_type;
          } else if ($folio) {
            $sell->type = $folio->type;
            // $sell->glosa_sii = $folio->glosa_sii;
          } else {
            $sell->type = null;
            // $sell->glosa_sii = null;
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local')) {
              $sell->type = 'Boleta Local';
            }
          }
        }
      }

      $filteredCollection = $Paginated['items']->map(function ($item) {
        return [
            'user' => $item->user,
            'created_at' => $item->created_at,
            'total' => $item->total,
            'sell_folio' => $item->sell_folio,
            'type' => $item->type,
        ];
      });
      return $filteredCollection;
    }
}
