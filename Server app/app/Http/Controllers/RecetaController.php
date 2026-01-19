<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\RecetaGlobal;
use App\RecetaIngredienteGlobal;
use App\Helpers\CurrentApp;

/**
 * RecetaController
 * 
 * Controlador para gestionar las recetas globales (BD Maestra)
 * y las asociaciones con productos (BD Local)
 */
class RecetaController extends Controller
{
    /**
     * Listar todas las recetas globales disponibles
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = RecetaGlobal::with('ingredientes')
                ->orderBy('nombre', 'asc');

            // Filtro: solo activas
            if ($request->has('activas')) {
                $query->activas();
            }

            // Filtro: búsqueda por nombre
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where('nombre', 'LIKE', "%{$search}%");
            }

            $recetas = $query->get();

            // Agregar información adicional
            $recetas->each(function ($receta) {
                // Contar cuántos productos locales usan esta receta
                $receta->productos_asociados_count = $this->contarProductosAsociados($receta->id);
            });

            return response()->json([
                'success' => true,
                'data' => $recetas,
                'total' => $recetas->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener recetas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una receta específica con sus ingredientes
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $receta = RecetaGlobal::with('ingredientes')->find($id);

            if (!$receta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receta no encontrada'
                ], 404);
            }

            // Agregar productos locales que usan esta receta
            $receta->productos_asociados = $this->obtenerProductosAsociados($id);

            return response()->json([
                'success' => true,
                'data' => $receta
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener receta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva receta global
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'codigo' => 'nullable|string|max:50|unique:recetas_global,codigo',
                'ingredientes' => 'required|array|min:1',
                'ingredientes.*.nombre' => 'required|string',
                'ingredientes.*.cantidad' => 'required|numeric|min:0',
                'ingredientes.*.unidad' => 'required|string',
            ]);

            DB::beginTransaction();

            // Crear la receta
            $receta = RecetaGlobal::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'codigo' => $request->codigo,
                'observaciones' => $request->observaciones,
                'activa' => $request->activa ?? 1,
            ]);

            // Crear los ingredientes
            foreach ($request->ingredientes as $index => $ing) {
                RecetaIngredienteGlobal::create([
                    'receta_global_id' => $receta->id,
                    'ingrediente_nombre' => $ing['nombre'],
                    'cantidad' => $ing['cantidad'],
                    'unidad' => $ing['unidad'],
                    'orden' => $index + 1,
                    'opcional' => $ing['opcional'] ?? 0,
                    'notas' => $ing['notas'] ?? null,
                ]);
            }

            DB::commit();

            // Recargar con ingredientes
            $receta->load('ingredientes');

            return response()->json([
                'success' => true,
                'message' => 'Receta creada exitosamente',
                'data' => $receta
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear receta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una receta global
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $receta = RecetaGlobal::find($id);

            if (!$receta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receta no encontrada'
                ], 404);
            }

            $request->validate([
                'nombre' => 'required|string|max:255',
                'codigo' => 'nullable|string|max:50|unique:recetas_global,codigo,' . $id,
            ]);

            DB::beginTransaction();

            // Actualizar receta
            $receta->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'codigo' => $request->codigo,
                'observaciones' => $request->observaciones,
                'activa' => $request->activa ?? $receta->activa,
            ]);

            // Si se envían ingredientes, actualizar
            if ($request->has('ingredientes')) {
                // Eliminar ingredientes anteriores
                RecetaIngredienteGlobal::where('receta_global_id', $id)->delete();

                // Crear nuevos ingredientes
                foreach ($request->ingredientes as $index => $ing) {
                    RecetaIngredienteGlobal::create([
                        'receta_global_id' => $receta->id,
                        'ingrediente_nombre' => $ing['nombre'],
                        'cantidad' => $ing['cantidad'],
                        'unidad' => $ing['unidad'],
                        'orden' => $index + 1,
                        'opcional' => $ing['opcional'] ?? 0,
                        'notas' => $ing['notas'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $receta->load('ingredientes');

            return response()->json([
                'success' => true,
                'message' => 'Receta actualizada exitosamente',
                'data' => $receta
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar receta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una receta global
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $receta = RecetaGlobal::find($id);

            if (!$receta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receta no encontrada'
                ], 404);
            }

            // Verificar si hay productos asociados
            $productosAsociados = $this->contarProductosAsociados($id);

            if ($productosAsociados > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar. Hay {$productosAsociados} producto(s) asociado(s) a esta receta."
                ], 400);
            }

            DB::beginTransaction();

            // Eliminar ingredientes
            RecetaIngredienteGlobal::where('receta_global_id', $id)->delete();

            // Eliminar receta
            $receta->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Receta eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar receta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Contar cuántos productos locales usan esta receta
     * 
     * @param int $recetaGlobalId
     * @return int
     */
    private function contarProductosAsociados($recetaGlobalId)
    {
        try {
            $database = env('DB_DATABASE_LOCAL'); // BD Local del negocio

            if (!$database) {
                return 0;
            }

            $count = DB::connection('mysql_local')
                ->table('product_recetas')
                ->where('receta_global_id', $recetaGlobalId)
                ->where('activo', 1)
                ->count();

            return $count;

        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtener lista de productos locales que usan esta receta
     * 
     * @param int $recetaGlobalId
     * @return array
     */
    private function obtenerProductosAsociados($recetaGlobalId)
    {
        try {
            $database = env('DB_DATABASE_LOCAL');

            if (!$database) {
                return [];
            }

            $productos = DB::connection('mysql_local')
                ->table('product_recetas as pr')
                ->join('products as p', 'pr.product_id', '=', 'p.id')
                ->where('pr.receta_global_id', $recetaGlobalId)
                ->where('pr.activo', 1)
                ->select('p.id', 'p.name as nombre', 'pr.multiplicador', 'pr.created_at as asociado_el')
                ->get();

            return $productos;

        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Asociar una receta global a un producto local
     * 
     * @param Request $request
     * @param int $recetaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function asociarProducto(Request $request, $recetaId)
    {
        try {
            $app = CurrentApp::App();
            
            $request->validate([
                'product_id' => 'required|integer',
                'multiplicador' => 'nullable|numeric|min:0',
            ]);
            
            // Verificar que la receta existe
            $receta = RecetaGlobal::find($recetaId);
            if (!$receta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receta no encontrada'
                ], 404);
            }
            
            // Verificar que no exista ya la asociación
            $existe = DB::connection('mysql')
                ->table('product_recetas')
                ->where('app_id', $app->Id)
                ->where('receta_global_id', $recetaId)
                ->where('product_id', $request->product_id)
                ->where('activo', 1)
                ->exists();
                
            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este producto ya tiene esta receta asociada'
                ], 400);
            }
            
            // Crear la asociación
            DB::connection('mysql')
                ->table('product_recetas')
                ->insert([
                    'app_id' => $app->Id,
                    'receta_global_id' => $recetaId,
                    'product_id' => $request->product_id,
                    'multiplicador' => $request->multiplicador ?? 1.0,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Producto asociado exitosamente a la receta'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asociar producto: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Desasociar una receta de un producto
     * 
     * @param int $recetaId
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function desasociarProducto($recetaId, $productId)
    {
        try {
            $app = CurrentApp::App();
            
            $deleted = DB::connection('mysql')
                ->table('product_recetas')
                ->where('app_id', $app->Id)
                ->where('receta_global_id', $recetaId)
                ->where('product_id', $productId)
                ->update([
                    'activo' => 0,
                    'updated_at' => now()
                ]);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto desasociado exitosamente'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la asociación'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desasociar producto: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener la receta asociada a un producto
     * 
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecetaDelProducto($productId)
    {
        try {
            $app = CurrentApp::App();
            
            $asociacion = DB::connection('mysql')
                ->table('product_recetas')
                ->where('app_id', $app->Id)
                ->where('product_id', $productId)
                ->where('activo', 1)
                ->first();
            
            if (!$asociacion) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }
            
            $receta = RecetaGlobal::with('ingredientes')->find($asociacion->receta_global_id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'receta' => $receta,
                    'multiplicador' => $asociacion->multiplicador
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener receta del producto: ' . $e->getMessage()
            ], 500);
        }
    }
}
