<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use App\models_local\Operation;
use App\models_local\OperationCategory;
use App\models_local\OperationSubcategory;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

//Helpers
use App\Helpers\MPage;

use DateTime;
use Auth;

class OperationsController extends Controller
{
    public function index(Request $request)
    {
        $nameOfOperation = $request->input('nameOfOperation');
        $categoryOfProduct = $request->input('categoryOfProduct');
        $subcategoryOfProduct = $request->input('subcategoryOfProduct');

        $operations = Operation::with(['subcategories', 'categories']);

        if ($nameOfOperation) {
            $operations->orWhere('name', 'LIKE', "%{$nameOfOperation}%");
            $operations->orWhere('rut', 'LIKE', "%{$nameOfOperation}%");
            $operations->orWhere('receptor', 'LIKE', "%{$nameOfOperation}%");
            $operations->orWhere('factura', 'LIKE', "%{$nameOfOperation}%");
            $operations->orWhere('company_name', 'LIKE', "%{$nameOfOperation}%");
        }

        if ($categoryOfProduct && $subcategoryOfProduct) {
            $operations->where('operations_categories_id', '=', $categoryOfProduct)
                ->where('operations_subcategories_id', '=', $subcategoryOfProduct);
        } elseif ($categoryOfProduct) {
            $operations->where('operations_categories_id', '=', $categoryOfProduct);
        }

        //Rango de fechas
        if ($request->input('todayOperations')) {
            $today = new DateTime(now());
            $today->setTime(00, 00, 00);
            $operations->where('created_at', '>=', $today);
        }
        if ($request->input('startDate')) {
            $operations->where('created_at', '>=', $request->input('startDate'));
        }
        if ($request->input('endDate')) {
            $operations->where('created_at', '<=', $request->input('endDate'));
        }

        // $operations = $operations->get();
        // $ordersAndFilters = ['created_at','operations_categories_id','operations_subcategories_id'];

        //Procesamiento individual de items
        $Paginated = MPage::paginate($operations, $request, 10, '', 'operations');

        return response()->json($Paginated);
    }
    public function getBalances(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
    
        $balances = OperationCategory::whereHas('operations', function ($query) use ($startDate, $endDate) {
                if ($startDate) {
                    $query->where('operations.created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('operations.created_at', '<=', $endDate);
                }
            })
            ->with(['operations' => function ($query) use ($startDate, $endDate) {
                $query->select('operations_categories_id')
                    ->selectRaw('COUNT(*) as operations_count')
                    ->selectRaw('SUM(total) as operations_sum_total')
                    ->groupBy('operations_categories_id');
    
                if ($startDate) {
                    $query->where('operations.created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('operations.created_at', '<=', $endDate);
                }
            }])
            ->with(['subcategories' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('operations', function ($query) use ($startDate, $endDate) {
                    if ($startDate) {
                        $query->where('operations.created_at', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query->where('operations.created_at', '<=', $endDate);
                    }
                })
                ->with(['operations' => function ($query) use ($startDate, $endDate) {
                    $query->select('operations_subcategories_id')
                        ->selectRaw('COUNT(*) as operations_count')
                        ->selectRaw('SUM(total) as operations_sum_total')
                        ->groupBy('operations_subcategories_id');
    
                    if ($startDate) {
                        $query->where('operations.created_at', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query->where('operations.created_at', '<=', $endDate);
                    }
                }]);
            }])
            ->get();
    
        return response()->json($balances);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:50',
            'rut' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:50',
            'receptor' => 'nullable|string|max:50',
            'observation' => 'nullable|string',
            'fecha' => 'nullable',
            'factura' => 'nullable|string|max:50',
            'total' => 'nullable|numeric',
            'operations_subcategories_id' => 'required|numeric',
            'operations_categories_id' => 'required|numeric',
        ]);
        $validatedData['user'] = Auth::user()->id;

        $operation = Operation::create($validatedData);

        return response()->json($operation, 201);
    }

    public function update(Request $request, $id)
    {
        $operation = Operation::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:50',
            'rut' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:50',
            'receptor' => 'nullable|string|max:50',
            'observation' => 'nullable|string',
            'fecha' => 'nullable',
            'factura' => 'nullable|string|max:50',
            'total' => 'nullable|numeric',
            'operations_subcategories_id' => 'required|numeric',
            'operations_categories_id' => 'required|numeric',
        ]);

        $operation->update($validatedData);

        return response()->json($operation, 200);
    }

    public function remove($id)
    {
        $Operation = Operation::findOrFail($id);

        $Operation->delete();

        return response()->json(['Operacion eliminado exitosamente'], 200);
    }
}
