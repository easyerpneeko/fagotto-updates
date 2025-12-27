<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\MetaLocal;
use App\Aplication;
use App\Helpers\ConectionDB;
use App\models_local\Sell;
use Carbon\Carbon;

class MetaLocalController extends Controller
{
    /**
     * Listar todas las metas de un mes/año específico
     */
    public function index(Request $request)
    {
        try {
            $mes = $request->input('mes', date('n'));
            $anio = $request->input('anio', date('Y'));
            $dia = $request->input('dia'); // Filtro opcional por día

            // Usar directamente el nombre de la base de datos maestra
            $query = DB::table('easyerp.metas_locales')
                ->where('mes', $mes)
                ->where('anio', $anio);
            
            // Si se especifica día, filtrar por él
            if ($dia !== null) {
                $query->where('dia', $dia);
            }
            
            $metas = $query->orderBy('dia', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $metas
            ], 200);
        } catch (\Exception $e) {
            Log::error('MetaLocalController@index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar o actualizar meta de un mes/año
     */
    public function store(Request $request)
    {
        try {
            $aplicationId = $request->input('aplication_id');
            $mes = $request->input('mes');
            $anio = $request->input('anio');
            $dia = $request->input('dia'); // Obtener el día
            $metaDiaria = $request->input('meta_diaria');

            if (!$aplicationId || !$mes || !$anio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan campos requeridos'
                ], 400);
            }

            // Usar directamente el nombre de la base de datos maestra
            // Verificar si ya existe (incluyendo el día en la búsqueda)
            $query = DB::table('easyerp.metas_locales')
                ->where('aplication_id', $aplicationId)
                ->where('mes', $mes)
                ->where('anio', $anio);
            
            // Si se especifica día, incluirlo en la búsqueda
            if ($dia !== null) {
                $query->where('dia', $dia);
            } else {
                $query->whereNull('dia');
            }
            
            $metaExistente = $query->first();

            if ($metaExistente) {
                // Actualizar
                DB::table('easyerp.metas_locales')
                    ->where('id', $metaExistente->id)
                    ->update([
                        'meta_diaria' => $metaDiaria,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                $metaId = $metaExistente->id;
            } else {
                // Insertar (incluyendo el día si existe)
                $data = [
                    'aplication_id' => $aplicationId,
                    'mes' => $mes,
                    'anio' => $anio,
                    'dia' => $dia, // Guardar el día
                    'meta_diaria' => $metaDiaria,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $metaId = DB::table('easyerp.metas_locales')->insertGetId($data);
            }

            $meta = DB::table('easyerp.metas_locales')->where('id', $metaId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Meta guardada',
                'meta' => $meta
            ], 200);

        } catch (\Exception $e) {
            Log::error('MetaLocalController@store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener la meta diaria del local actual (solo la meta, no las ventas)
     */
    public function getCurrentLocalMeta(Request $request)
    {
        try {
            // Obtener el serial del header
            $serial = $request->header('App-Key');
            
            if (!$serial) {
                return response()->json([
                    'success' => false,
                    'message' => 'App-Key no encontrado'
                ], 400);
            }

            // Buscar la aplicación por serial
            $app = Aplication::where('serial', $serial)->first();
            
            if (!$app) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aplicación no encontrada'
                ], 404);
            }

            $mes = date('n');
            $anio = date('Y');

            // Obtener la meta del local actual para el mes/año actual
            $meta = DB::table('easyerp.metas_locales')
                ->where('aplication_id', $app->id)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->first();

            return response()->json([
                'success' => true,
                'meta' => $meta
            ], 200);

        } catch (\Exception $e) {
            Log::error('MetaLocalController@getCurrentLocalMeta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * MÉTODOS LEGACY (se mantienen por compatibilidad)
     */
    public function indexLegacy(Request $request)
    {
        try {
            // Obtener todas las sucursales
            $sucursales = Aplication::select('id', 'name', 'client')
                ->orderBy('name', 'asc')
                ->get();

            $metasConSucursales = [];

            foreach ($sucursales as $sucursal) {
                // Buscar meta activa para esta sucursal
                $meta = MetaLocal::where('sucursal_id', $sucursal->id)
                    ->where('activo', 1)
                    ->first();

                $metasConSucursales[] = [
                    'sucursal_id' => $sucursal->id,
                    'sucursal_nombre' => $sucursal->name,
                    'meta' => $meta ? [
                        'id' => $meta->id,
                        'meta_lunes' => $meta->meta_lunes,
                        'meta_martes' => $meta->meta_martes,
                        'meta_miercoles' => $meta->meta_miercoles,
                        'meta_jueves' => $meta->meta_jueves,
                        'meta_viernes' => $meta->meta_viernes,
                        'meta_sabado' => $meta->meta_sabado,
                        'meta_domingo' => $meta->meta_domingo,
                        'fecha_inicio' => $meta->fecha_inicio,
                        'activo' => $meta->activo
                    ] : null
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $metasConSucursales
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener metas: ' . $e->getMessage()
            ], 500);
        }
    }
}
