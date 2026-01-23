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
            $products = DB::connection('easyerp')
                ->table('merchise_items')
                ->join('merchise_sections', 'merchise_items.section_id', '=', 'merchise_sections.id')
                ->select(
                    'merchise_items.*',
                    'merchise_sections.name as section_name'
                )
                ->orderBy('merchise_items.orden')
                ->get();

            $result = $products->map(function($product) {
                return [
                    'id' => $product->sku,
                    'name' => $product->name,
                    'price' => (float)$product->price,
                    'description' => $product->description ?? '',
                    'section_id' => $product->section_id,
                    'section_name' => $product->section_name,
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
            $item = DB::connection('easyerp')
                ->table('merchise_items')
                ->where('sku', $productSku)
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ], 200);
            }

            // Buscar modifiers a través de la tabla de relación
            $modifiers = DB::connection('easyerp')
                ->table('merchise_modifiers')
                ->join('merchise_item_modifiers', 'merchise_modifiers.id', '=', 'merchise_item_modifiers.modifier_id')
                ->where('merchise_item_modifiers.item_id', $item->id)
                ->select('merchise_modifiers.*')
                ->get();

            if ($modifiers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ], 200);
            }

            // Obtener opciones de cada modifier a través de la tabla de relación
            $result = [];
            foreach ($modifiers as $modifier) {
                $options = DB::connection('easyerp')
                    ->table('merchise_options')
                    ->join('merchise_modifier_options', 'merchise_options.id', '=', 'merchise_modifier_options.option_id')
                    ->where('merchise_modifier_options.modifier_id', $modifier->id)
                    ->orderBy('merchise_modifier_options.orden')
                    ->select('merchise_options.*')
                    ->get();

                $result[] = [
                    'id' => $modifier->id,
                    'name' => $modifier->name,
                    'sku' => $modifier->sku,
                    'required' => (bool)$modifier->required,
                    'min_selections' => (int)$modifier->min_selections,
                    'max_selections' => (int)$modifier->max_selections,
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
