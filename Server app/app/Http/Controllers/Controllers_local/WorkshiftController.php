<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\models_local\Workshift;
use App\Http\Controllers\Controllers_local\ReportsController;

class WorkshiftController extends Controller
{
    protected function newWorkshift(Request $request){
        // dd($request->all());
        $validatedData = $request->validate([
            'start_workshift' => 'required|date',
            'end_workshift' => 'required|date',
            'init_money' => 'required|numeric',
            'final_money' => 'required|numeric',
            // 'user_id' => 'required|exists:users,id'
            'user_id' => 'required'
        ]);

        $newRequest = new Request([
            'startDate' => $validatedData['start_workshift'],
            'endDate' => $validatedData['end_workshift'],
        ]);

        $report_controller = new ReportsController();

        $response = $report_controller->getCounters($newRequest);

        $response = $response->getContent();
        $response_array = json_decode($response, true);
        $total_to_expenses = $response_array['counters']['totalToExpenses'];
        
        $validatedData['final_money'] = $total_to_expenses;

        $workshift = Workshift::create($validatedData);
        
        return response()->json(['workshift' => $workshift], 200);
    }   
}
