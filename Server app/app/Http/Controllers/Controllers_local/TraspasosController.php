<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TraspasosController extends Controller
{
    /**
     * Listar todos los traspasos
     * GET /api/local/traspasos-productos
     */
    public function index(Request $request)
    {
        try {
            $query = DB::connection('easyerp_master')
                ->table('traspasos_productos')
                ->orderBy('created_at', 'desc');

            // Filtros opcionales
            if ($request->has('local_origen_id')) {
                $query->where('local_origen_id', $request->local_origen_id);
            }

            if ($request->has('local_destino_id')) {
                $query->where('local_destino_id', $request->local_destino_id);
            }

            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            // Paginación opcional
            $limit = $request->input('limit', 50);
            $traspasos = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $traspasos
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error al obtener traspasos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener traspasos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos disponibles (de pedidofinal_precios)
     * GET /api/local/traspasos-productos/productos
     */
    public function getProductos(Request $request)
    {
        try {
            $productos = DB::connection('easyerp_master')
                ->table('pedidofinal_precios')
                ->select('id', 'producto as name', 'categoria as category', 'stock', 'unidad_medida', 'precio_por_unidad as precio')
                ->where('activo', 1)
                ->orderBy('categoria', 'asc')
                ->orderBy('producto', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $productos
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error al obtener productos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo traspaso
     * POST /api/local/traspasos-productos
     */
    public function store(Request $request)
    {
        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'local_origen_id' => 'required|integer',
                'local_destino_id' => 'required|integer|different:local_origen_id',
                'local_origen_nombre' => 'required|string|max:255',
                'local_destino_nombre' => 'required|string|max:255',
                'solicitante_nombre' => 'nullable|string|max:255',
                'solicitante_telefono' => 'nullable|string|max:50',
                'productos' => 'required|array|min:1',
                'productos.*.id' => 'required|integer',
                'productos.*.name' => 'required|string',
                'productos.*.cantidad' => 'required|numeric|min:0.01',
                'productos.*.unidad_medida' => 'nullable|string',
                'total_items' => 'required|integer|min:1',
                'comentarios' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Convertir productos a JSON
            $productosJson = json_encode($data['productos']);

            // Insertar en la base de datos
            $traspasoId = DB::connection('easyerp_master')
                ->table('traspasos_productos')
                ->insertGetId([
                    'local_origen_id' => $data['local_origen_id'],
                    'local_destino_id' => $data['local_destino_id'],
                    'local_origen_nombre' => $data['local_origen_nombre'],
                    'local_destino_nombre' => $data['local_destino_nombre'],
                    'solicitante_nombre' => $data['solicitante_nombre'] ?? null,
                    'solicitante_telefono' => $data['solicitante_telefono'] ?? null,
                    'productos' => $productosJson,
                    'total_items' => $data['total_items'],
                    'comentarios' => $data['comentarios'] ?? null,
                    'estado' => 'pendiente',
                    'fecha_envio' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            // Obtener el traspaso creado
            $traspaso = DB::connection('easyerp_master')
                ->table('traspasos_productos')
                ->where('id', $traspasoId)
                ->first();

            Log::info("✅ Traspaso #{$traspasoId} creado: {$data['local_origen_nombre']} → {$data['local_destino_nombre']}");

            return response()->json([
                'success' => true,
                'message' => 'Traspaso creado exitosamente',
                'data' => $traspaso
            ], 201);

        } catch (\Exception $e) {
            Log::error('❌ Error al crear traspaso: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear traspaso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de un traspaso
     * GET /api/local/traspasos-productos/{id}
     */
    public function show($id)
    {
        try {
            $traspaso = DB::connection('easyerp_master')
                ->table('traspasos_productos')
                ->where('id', $id)
                ->first();

            if (!$traspaso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Traspaso no encontrado'
                ], 404);
            }

            // Decodificar productos de JSON a array
            $traspaso->productos_detalle = json_decode($traspaso->productos);

            return response()->json([
                'success' => true,
                'data' => $traspaso
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error al obtener traspaso: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener traspaso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado de un traspaso
     * PUT /api/local/traspasos-productos/{id}/estado
     */
    public function updateEstado(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:pendiente,en_transito,recibido,rechazado',
                'recibido_por' => 'nullable|string|max:255',
                'motivo_rechazo' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            $updateData = [
                'estado' => $data['estado'],
                'updated_at' => now()
            ];

            // Agregar campos según el estado
            if ($data['estado'] === 'recibido') {
                $updateData['fecha_recepcion'] = now();
                $updateData['recibido_por'] = $data['recibido_por'] ?? null;
            }

            if ($data['estado'] === 'rechazado') {
                $updateData['motivo_rechazo'] = $data['motivo_rechazo'] ?? null;
            }

            DB::connection('easyerp_master')
                ->table('traspasos_productos')
                ->where('id', $id)
                ->update($updateData);

            $traspaso = DB::connection('easyerp_master')
                ->table('traspasos_productos')
                ->where('id', $id)
                ->first();

            Log::info("✅ Traspaso #{$id} actualizado a estado: {$data['estado']}");

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'data' => $traspaso
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
