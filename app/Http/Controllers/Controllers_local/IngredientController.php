<?php

namespace App\Http\Controllers\Controllers_local;
use App\Http\Controllers\Controller;

use App\models_local\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// Helpers
use App\Helpers\CurrentApp;
use App\Helpers\MPage;
use Config;

// Laravel
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use DateTime;
use Auth;

class IngredientController extends Controller
{
    /**
     * Display a listing of the ingredients.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $ingredients = Ingredient::all();
        return response()->json($ingredients);
    }

    /**
     * Update the stock of a specific ingredient.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStock(Request $request, $ingredientId)  // Cambiado a ingredientId
    {
        $validator = Validator::make($request->all(), [
            'new_stock' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $ingredient = Ingredient::find($ingredientId); // Usar find en lugar de where
        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        $ingredient->stock_quantity = $request->input('new_stock');
        $ingredient->save();

        return response()->json(['message' => 'Ingredient stock updated successfully']);
    }

    protected function getIngredients()
    {
        $database = Config::get('database.connections.mysql.database');
  
        // Obtener todos los ingredientes con su información de stock
        $ingredients = Ingredient::all();
  
        // Calcular la cantidad de envases que se pueden hacer por ingrediente
        $ingredients = $ingredients->map(function ($ingredient) use ($database) {
            $ingredientData = $ingredient->toArray();
            $totalVasos = 0;
  
            // Obtener el quantity_grams del producto que usa este ingrediente.
            $productIngredient = DB::table($database . '.global_ingredients')
                ->where('ingredient_id', $ingredientData['id'])
                ->first();
  
            if ($productIngredient) {
                $cantidad_por_porcion_gramos = $productIngredient->quantity_grams;
                $stock_kg = $ingredientData['stock_quantity'];
                $stock_gramos = $stock_kg * 1000;  // Convertir kg a gramos
                $totalVasos = floor($stock_gramos / $cantidad_por_porcion_gramos);
            }
  
            $ingredientData['vasos'] = $totalVasos;
            return $ingredientData;
        });
  
        if (!$ingredients) {
            return response()->json('Error del servidor', 500);
        }
  
        return response()->json($ingredients, 200);
    }
}
