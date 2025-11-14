<?php

namespace App\Http\Controllers\Controllers_local;


use App\Http\Controllers\Controller;
use App\models_local\OperationSubcategory;
use App\models_local\OperationCategory;
use Illuminate\Http\Request;
use Auth;

class OperationsSubcategoriesController extends Controller
{
    public function index()
    {
        $subcategories = OperationSubcategory::all();
        return response()->json($subcategories);
    }

    public function show(OperationSubcategory $subcategory)
    {
        return response()->json($subcategory);
    }

    public function store(Request $request)
    {   

        $validatedData = $request->validate([
            'name' => 'required|max:50',
            'operations_categories_id'=>'required'
            // 'description' => 'nullable|max:250',
            // 'user' => 'required|exists:users,id',
            // 'status' => 'nullable|boolean',
        ]);
        $validatedData['user'] = Auth::user()->id;
        
        $category = OperationSubcategory::create($validatedData);

        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {   
        $subcategory = OperationSubcategory::findOrFail($id);
        
        $validatedData = $request->validate([
            'name' => 'required|max:50',
        ]);

        $subcategory->name = $validatedData['name'];

        $subcategory->save();

        return response()->json($subcategory, 200);
    }

    public function remove($id)
    {
        $subcategory = OperationSubcategory::findOrFail($id);

        $subcategory->delete();
    
        return response()->json(['Subcategoria eliminado exitosamente'],200);
    }

    public function getSubcategoriesByCategory($categoryId)
    {
        $category = OperationCategory::findOrFail($categoryId);

        $subcategories = $category->subcategories;

        return response()->json($subcategories);
    }
}