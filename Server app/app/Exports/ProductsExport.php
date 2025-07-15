<?php

namespace App\Exports;

// use App\models_local\Product;
use App\Helpers\CurrentApp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Config;

class ProductsExport implements FromCollection,WithHeadings
{
    public function headings(): array
    {
      $headings = [
        'ID',
        'Nombre',
      ];

      // ¿Codigo de barras activado?
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_barcode')){
        $headings[] = 'Codigo de barra';
      }

      // ¿Stock activado?
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')){
        $headings[] = 'Stock';
      }

      // ¿Cantidad minima activada?
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')){
        $headings[] = 'Cantidad minima';
      }

      // ¿Cecina activada?
      if (CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cecina')){
        $headings[] = 'Cecina';
      }

      if (CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
        $headings[] = 'Promocon';
      }

      // Precio
      $headings[] = 'Precio';

      // ¿Activado?
      $headings[] = '¿Activado?';

      // ¿Ganancia activada?
      if (CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')){
        $headings[] = 'Ganancia';
      }

      // Fechas
      $headings[] = 'Fecha de creación';
      $headings[] = 'Fecha de edición';

      // ¿Categorias activada?
      if (CurrentApp::ConfStr('modulos.productos.submodulos.categorias')){
        $headings[] = 'Categoria';
      }
      
      return $headings;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $database2 = Config::get('database.connections.mysql_local.database');
        $allProducts = DB::table($database2.'.products')->leftjoin($database2.'.categories', 'categories.id', '=', 'products.category')
                                            ->select('products.*', 'categories.name as category')
                                            ->get();

        foreach ($allProducts as $key => $product) {
          // Eliminado campos sobrantes
          unset($product->image);
          unset($product->key_system);
          unset($product->trash);
          unset($product->user);
          unset($product->prices);

          // Verificando activacion
          if($product->active) $product->active = 'Activado';
          else $product->active = 'Desactivado';

          // ¿Codigo de barras activado?
          if (!CurrentApp::ConfStr('modulos.productos.ajustes.permitir_barcode')){
            unset($product->barcode);
          }
          // ¿Stock activado?
          if (!CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock')){
            unset($product->stock);
          }
          // ¿Cantidad minima activada?
          if (!CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima')){
            unset($product->min_quantity);
          }
          // ¿Cecina activada?
          if (!CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cecina')){
            unset($product->cecina);
          }
          if (!CurrentApp::ConfStr('modulos.productos.submodulos.precio_promo')) {
            unset($product->promo_active);
          }
          // ¿Ganancia activada?
          if (!CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia')){
            unset($product->ganancia);
          }
          // ¿Categorias activada?
          if (!CurrentApp::ConfStr('modulos.productos.submodulos.categorias')){
            unset($product->category);
          }
        }

        return $allProducts;
    }
}
