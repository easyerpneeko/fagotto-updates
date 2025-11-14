<?php

namespace App\Http\Controllers\Controllers_local;

use App\models_local\Devolution;
use App\models_local\Product;
use App\models_local\Sell;
use App\models_local\ProductSell;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//Helpers
use App\Helpers\MPage;

use DateTime;
use Auth;

class DevolutionsController extends Controller
{

    public function index(Request $request)
    {

        $ReasonName = $request->input('ReasonName');
        $ProductName = $request->input('ProductName');

        $devolutions = Devolution::with('product', 'sell', 'user');

        if ($ReasonName) {
            $devolutions->where('reason', 'LIKE', "%{$ReasonName}%");
        }

        if ($ProductName) {
            $devolutions->whereHas('product', function ($query) use ($ProductName) {
                $query->where('name', 'LIKE', "%{$ProductName}%");
            });
        }
        //Rango de fechas
        if ($request->input('todayDevolutions')) {
            $today = new DateTime(now());
            $today->setTime(00, 00, 00);
            $devolutions->where('created_at', '>=', $today);
        }
        if ($request->input('startDate')) {
            $devolutions->where('created_at', '>=', $request->input('startDate'));
        }
        if ($request->input('endDate')) {
            $devolutions->where('created_at', '<=', $request->input('endDate'));
        }

        // $devolutions = $devolutions->get();

        //Procesamiento individual de items
        $Paginated = MPage::paginate($devolutions, $request, 10, '', 'devolutions');

        return response()->json($Paginated, 200);
    }

    public function devolutionProduct(Request $request, $product_sell_id)
    {
        $_REQUEST = $request->all();

        //Producto de la venta
        $product_sell = ProductSell::find($product_sell_id);

        if (!$product_sell) {
            return response()->json("Producto de la venta no encontrado", 404);
        }

        //Producto 
        $product = Product::find($product_sell->product);

        if (!$product) {
            return response()->json("Producto no encontrado", 404);
        }

        //Calcular ganancia del producto en la venta
        $productGanancia = $product_sell->gananciaTotal / $product_sell->quantity;

        //Restar montos a la venta
        $sell = Sell::find($product_sell->sell);
        $sell->total -= $_REQUEST['quantity'] * $product_sell->price;
        $sell->gananciaTotal -= $_REQUEST['quantity'] * $productGanancia;
        $sell->save();

        //Crear devolucion
        $devolution = new Devolution();
        $devolution->name = $product->name;
        $devolution->price = $product_sell->price;
        $devolution->ganancia = $productGanancia;
        $devolution->reason = $_REQUEST['reason'];
        $devolution->product_id = $product_sell->product;
        $devolution->stock = $_REQUEST['quantity']; //La cantidad debe venir del request
        $devolution->sell_id = $product_sell->sell;
        $devolution->user_id = Auth::user()->id;

        $product->stock += $_REQUEST['quantity']; //La cantidad debe venir del request

        //Si es menor que la cantidad total vendida solo se resta de lo contrario se elmiina 
        if ($_REQUEST['quantity'] < $product_sell->quantity) {
            $product_sell->quantity -= $_REQUEST['quantity'];
            $product_sell->save();
        } else {

            $product_sell->delete();
        }

        if (!$devolution->save()) {
            return response()->json("Error al guardar Devolucion", 400);
        }

        return response()->json($product_sell, 200);
    }
}
