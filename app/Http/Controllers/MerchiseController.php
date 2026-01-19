<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchiseController extends Controller
{
    /**
     * Obtener todos los productos merchise
     * GET /api/merchise/products
     */
    public function getProducts()
    {
        try {
            $products = DB::connection('easyerp_master')
                ->table('merchise_items')
                ->orderBy('orden')
                ->get();

            $result = $products->map(function($product) {
                return [
                    'id' => $product->sku,
                    'name' => $product->name,
                    'price' => (float)$product->price,
                    'description' => $product->description ?? '',
                    'section_id' => $product->section_id,
                    'image' => $product->image ?? ''
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener modifiers de un producto
     * GET /api/merchise/products/{sku}/modifiers
     */
    public function getModifiers($productSku)
    {
        try {
            // Buscar item por SKU
            $item = DB::connection('easyerp_master')
                ->table('merchise_items')
                ->where('sku', $productSku)
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ], 200);
            }

            // Buscar modifiers
            $modifiers = DB::connection('easyerp_master')
                ->table('merchise_modifiers')
                ->where('item_id', $item->id)
                ->orderBy('orden')
                ->get();

            if ($modifiers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ], 200);
            }

            // Obtener opciones de cada modifier
            $result = [];
            foreach ($modifiers as $modifier) {
                $options = DB::connection('easyerp_master')
                    ->table('merchise_options')
                    ->where('modifier_id', $modifier->id)
                    ->orderBy('orden')
                    ->get();

                $result[] = [
                    'id' => $modifier->id,
                    'name' => $modifier->name,
                    'sku' => $modifier->sku,
                    'required' => (bool)$modifier->required,
                    'min_selections' => (int)$modifier->min,
                    'max_selections' => (int)$modifier->max,
                    'options' => $options->map(function($option) {
                        return [
                            'id' => $option->id,
                            'name' => $option->name,
                            'sku' => $option->sku,
                            'price' => (float)$option->price
                        ];
                    })->toArray()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
