<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;

use App\models_local\OperationCategory;

use Illuminate\Http\Request;
use Auth;

class OperationsCategoriesController extends Controller
{
    public function index()
    {
        $categories = OperationCategory::all();
        return response()->json($categories);
    }

    public function show(OperationCategory $category)
    {
        return response()->json($category);
    }

    public function store(Request $request)
    {   

        $validatedData = $request->validate([
            'name' => 'required|max:50',
            // 'description' => 'nullable|max:250',
            // 'user' => 'required|exists:users,id',
            // 'status' => 'nullable|boolean',
        ]);
        $validatedData['user']=Auth::user()->id;
        
        $category = OperationCategory::create($validatedData);

        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {   
        $category = OperationCategory::findOrFail($id);
        
        $validatedData = $request->validate([
            'name' => 'required|max:50',
        ]);

        $category->name = $validatedData['name'];

        $category->save();

        return response()->json($category, 200);
    }

    public function remove($id)
    {
        $OperationCategory = OperationCategory::findOrFail($id);

        $OperationCategory->delete();
    
        return response()->json(['Categoria eliminado exitosamente'],200);
    }
}