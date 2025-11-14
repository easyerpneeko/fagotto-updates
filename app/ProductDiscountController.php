<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use App\ProductDiscountByBranch;
use App\Aplication;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductDiscountController extends Controller
{
    /**
     * Obtener todos los descuentos (con filtros opcionales)
     */
    public function index(Request $request)
    {
        try {
            $query = ProductDiscountByBranch::with(['application', 'product']);

            // Filtrar por sucursal
            if ($request->has('application_id')) {
                $query->where('application_id', $request->application_id);
            }

            // Filtrar solo activos
            if ($request->has('active_only') && $request->active_only) {
                $query->where('active', 1)
                      ->where(function ($q) {
                          $q->whereNull('start_date')
                            ->orWhere('start_date', '<=', now());
                      })
                      ->where(function ($q) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                      });
            }

            $discounts = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'discounts' => $discounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener descuentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo descuento
     */
    public function store(Request $request)
    {
        try {
            // Obtener datos del request (funciona con JSON y form-urlencoded)
            $data = $request->input();
            
            // Si no hay datos en input(), intentar con all()
            if (empty($data)) {
                $data = $request->all();
            }
            
            // Limpiar solo campos que sean string vacío
            foreach ($data as $key => $value) {
                if ($value === '' || $value === null) {
                    unset($data[$key]);
                }
            }

            // Convertir application_id a integer si viene como string
            if (isset($data['application_id'])) {
                $data['application_id'] = (int) $data['application_id'];
            }

            // Convertir active a integer si viene
            if (isset($data['active'])) {
                $data['active'] = (int) $data['active'];
            } else {
                $data['active'] = 1; // Default
            }

            // Log para debugging
            \Log::info('Discount Store Request', [
                'request_all' => $request->all(),
                'request_input' => $request->input(),
                'final_data' => $data,
            ]);

            $validator = Validator::make($data, [
                'application_id' => 'required|integer|exists:aplications,id',
                'product_id' => 'nullable|integer|exists:products,id',
                'product_category' => 'nullable|string|max:255',
                'product_name_pattern' => 'nullable|string|max:255',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'discount_amount' => 'nullable|numeric|min:0',
                'active' => 'nullable|integer|in:0,1',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation Failed', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Validar que haya al menos un criterio de selección
            if (!isset($data['product_id']) && !isset($data['product_category']) && !isset($data['product_name_pattern'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes especificar al menos un criterio: product_id, product_category o product_name_pattern'
                ], 400);
            }

            // Validar que haya al menos un tipo de descuento
            if (!isset($data['discount_percentage']) && !isset($data['discount_amount'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes especificar discount_percentage o discount_amount'
                ], 400);
            }

            // Asegurarse de que active tenga un valor por defecto
            if (!isset($data['active'])) {
                $data['active'] = 1;
            }

            $discount = ProductDiscountByBranch::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Descuento creado exitosamente',
                'discount' => $discount
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Discount Store Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el descuento',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Actualizar un descuento existente
     */
    public function update(Request $request, $id)
    {
        $discount = ProductDiscountByBranch::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Descuento no encontrado'
            ], 404);
        }

        // Obtener todos los datos y limpiar solo strings vacíos
        $data = $request->all();
        
        foreach ($data as $key => $value) {
            if ($value === '') {
                unset($data[$key]);
            }
        }

        // Convertir tipos
        if (isset($data['application_id'])) {
            $data['application_id'] = (int) $data['application_id'];
        }
        if (isset($data['active'])) {
            $data['active'] = (int) $data['active'];
        }

        $validator = Validator::make($data, [
            'application_id' => 'sometimes|required|integer|exists:aplications,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'product_category' => 'nullable|string|max:255',
            'product_name_pattern' => 'nullable|string|max:255',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'active' => 'nullable|integer|in:0,1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $discount->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Descuento actualizado exitosamente',
            'discount' => $discount
        ]);
    }

    /**
     * Eliminar un descuento
     */
    public function destroy($id)
    {
        $discount = ProductDiscountByBranch::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Descuento no encontrado'
            ], 404);
        }

        $discount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Descuento eliminado exitosamente'
        ]);
    }

    /**
     * Activar/Desactivar descuento
     */
    public function toggle($id)
    {
        $discount = ProductDiscountByBranch::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Descuento no encontrado'
            ], 404);
        }

        $discount->active = !$discount->active;
        $discount->save();

        return response()->json([
            'success' => true,
            'message' => $discount->active ? 'Descuento activado' : 'Descuento desactivado',
            'discount' => $discount
        ]);
    }

    /**
     * Obtener descuentos activos para una sucursal específica
     * ESTA ES LA API QUE USARÁ EL POS
     */
    public function getActiveDiscountsByBranch($applicationId)
    {
        $discounts = ProductDiscountByBranch::getActiveDiscountsForBranch($applicationId);

        return response()->json([
            'success' => true,
            'application_id' => $applicationId,
            'discounts' => $discounts
        ]);
    }

    /**
     * Verificar si un producto tiene descuento en una sucursal
     * ESTA API LA USARÁ EL POS AL ESCANEAR UN PRODUCTO
     */
    public function checkProductDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|integer',
            'product_id' => 'required|integer',
            'product_name' => 'nullable|string',
            'product_category' => 'nullable|string',
            'product_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $discounts = ProductDiscountByBranch::getActiveDiscountsForBranch($request->application_id);

        $applicableDiscount = null;
        $maxDiscountAmount = 0;

        foreach ($discounts as $discount) {
            if ($discount->appliesToProduct(
                $request->product_id,
                $request->product_name,
                $request->product_category
            )) {
                $discountAmount = $discount->calculateDiscount(
                    $request->product_price,
                    $request->product_name
                );

                // Quedarse con el descuento mayor
                if ($discountAmount > $maxDiscountAmount) {
                    $maxDiscountAmount = $discountAmount;
                    $applicableDiscount = $discount;
                }
            }
        }

        if ($applicableDiscount) {
            $finalPrice = $request->product_price - $maxDiscountAmount;

            return response()->json([
                'success' => true,
                'has_discount' => true,
                'discount' => [
                    'id' => $applicableDiscount->id,
                    'description' => $applicableDiscount->description,
                    'discount_percentage' => $applicableDiscount->discount_percentage,
                    'discount_amount' => $maxDiscountAmount,
                    'original_price' => $request->product_price,
                    'final_price' => $finalPrice
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'has_discount' => false,
            'original_price' => $request->product_price,
            'final_price' => $request->product_price
        ]);
    }

    /**
     * Obtener todas las sucursales para el selector
     */
    public function getBranches()
    {
        $branches = Aplication::select('id', 'name', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'branches' => $branches
        ]);
    }

    /**
     * Clonar descuento a múltiples sucursales
     */
    public function cloneToMultipleBranches(Request $request, $id)
    {
        $originalDiscount = ProductDiscountByBranch::find($id);

        if (!$originalDiscount) {
            return response()->json([
                'success' => false,
                'message' => 'Descuento no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'required|exists:aplications,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $created = [];

        foreach ($request->application_ids as $appId) {
            // Evitar duplicar en la sucursal original
            if ($appId == $originalDiscount->application_id) {
                continue;
            }

            $newDiscount = $originalDiscount->replicate();
            $newDiscount->application_id = $appId;
            $newDiscount->save();

            $created[] = $newDiscount;
        }

        return response()->json([
            'success' => true,
            'message' => 'Descuento clonado a ' . count($created) . ' sucursales',
            'created_discounts' => $created
        ]);
    }
}
