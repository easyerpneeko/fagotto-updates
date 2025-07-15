<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\models_local\Category;
use App\models_local\Product;
use App\Helpers\CurrentApp;


class ProductsImport implements WithHeadingRow,ToCollection
{
    public function __construct(){

    }

    public function collection(Collection $products){
      foreach ($products as $product) {
        //Reviso si tienen instalado categorias y si mandaron categorias
        if (CurrentApp::ConfStr('modulos.productos.submodulos.categorias') && isset($product["categoria"]) && $product["categoria"]){
          $category = Category::where('name', $product["categoria"])->first();
          if (!$category) {
            $category = Category::create([
              'name' => $product["categoria"],
              'user' => 1,
            ]);
            $product["categoria"] = $category->id;
          }else{
            $product["categoria"] = $category->id;
          }
        }else{
          $product["categoria"] = null;
        }
        //Reviso si tienen instalado codigo de barras y si lo mandaron
        if (!(CurrentApp::ConfStr('modulos.productos.ajustes.permitir_barcode') && isset($product["codigo"]) && $product["codigo"])){
          $product["codigo"] = null;
        }
        //Reviso si tienen instalado ganancia y si lo mandaron
        if (!(CurrentApp::ConfStr('modulos.ventas.ajustes.permitir_ganancia') && isset($product["ganancia"]) && $product["ganancia"])){
          $product["ganancia"] = null;
        }
        //Reviso si tienen instalado cantidad minima y si lo mandaron
        if (!(CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cantidad_minima') && isset($product["minimo"]) && $product["minimo"])){
          $product["minimo"] = null;
        }
        //Reviso si tienen instalado stock y si lo mandaron
        if (!(CurrentApp::ConfStr('modulos.productos.ajustes.permitir_stock') && isset($product["stock"]) && $product["stock"])){
          $product["stock"] = null;
        }
        //Reviso si tienen instalado cecina y si lo mandaron
        if (!(CurrentApp::ConfStr('modulos.productos.ajustes.permitir_cecina') && isset($product["cecina"]) && $product["cecina"])){
          $product["cecina"] = false;
        }

        //Reviso si tienen instalado promo y si lo mandaron
        if (!(true && isset($product["promo_active"]) && $product["promo_active"])){
          $product["promo_active"] = false;
        }
        //Reviso si tienen instalado promo y si lo mandaron
        if (!(true && isset($product["promo_price"]) && $product["promo_price"])){
          $product["promo_price"] = false;
        }
        //Reviso si tienen instalado promo y si lo mandaron
        if (!(true && isset($product["product_variable_category"]) && $product["product_variable_category"])){
          $product["product_variable_category"] = false;
        }

        $response = ProductsImport::verifyProducts($product);
      }
      return $products;
    }

    static function verifyProducts($row){
      $product = Product::where('name', $row['nombre'])->orWhere('barcode', $row['codigo'])->first();
      if($product){
        return $product->update([
          'name'        => $row["nombre"],
          'price'       => $row["precio"],
          'category'    => $row["categoria"],
          'barcode'     => $row["codigo"],
          'ganancia'    => $row["ganancia"],
          'min_quantity'=> $row["minimo"],
          'stock'       => $row["stock"],
          'cecina'      => $row["cecina"],
          'promo_active'=> $row["promo_active"],
          'promo_price'=> $row["promo_price"],
          'product_variable_category'=> $row["product_variable_category"],
          'image'       => "productDefault",
        ]);
      }else{
        return Product::create([
          'name'        => $row["nombre"],
          'price'       => $row["precio"],
          'category'    => $row["categoria"],
          'barcode'     => $row["codigo"],
          'ganancia'    => $row["ganancia"],
          'min_quantity'=> $row["minimo"],
          'stock'       => $row["stock"],
          'cecina'      => $row["cecina"],
          'promo_active'=> $row["promo_active"],
          'promo_price'=> $row["promo_price"],
          'product_variable_category'=> $row["product_variable_category"],
          'image'       => "productDefault",
        ]);
      }
    }

}
