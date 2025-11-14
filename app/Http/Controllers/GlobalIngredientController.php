<?php

namespace App\Http\Controllers;

// Laravel
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Helpers
use App\Helpers\ConectionDB;
use App\Helpers\CurrentApp;
use Config;

// Models
use App\Aplication;
use App\ProductSell;
use App\models_local\Product;
use App\GlobalIngredient;

// Laravel
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use DateTime;
use Auth;

class GlobalIngredientController extends Controller
{
    /**
     * Display a listing of the ingredients.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $database = Config::get('database.connections.mysql.database');

        $ingredients = DB::table($database . '.global_ingredients')->get();

        return response()->json($ingredients);
    }

    public function update(Request $request, $ingredientId)  // Cambiado a ingredientId
    {   
        $ingredient = GlobalIngredient::find($ingredientId);
        
        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity_grams' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $ingredient->quantity_grams = $request->input('quantity_grams');
        $ingredient->save();

        return response()->json(['message' => 'Ingredient gramaje updated successfully']);
    }


}
