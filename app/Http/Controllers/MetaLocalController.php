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
     * GET /api/web/metas-locales?mes=1&anio=2026&dia=6
     */
    public function index(Request $request)
    {
        try {
            $mes = $request->input('mes', date('n'));
            $anio = $request->input('anio', date('Y'));
            $dia = $request->input('dia'); // Filtro opcional por día

            // Usar directamente el nombre de la base de datos maestra (formato: database.tabla)
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
     * POST /api/web/metas-locales/store
     */
    public function store(Request $request)
    {
        try {
            $aplicationId = $request->input('aplication_id');
            $mes = $request->input('mes');
            $anio = $request->input('anio');
            $dia = $request->input('dia'); // Obtener el día
            $metaDiaria = $request->input('meta_diaria');
            $ticketPromedio = $request->input('ticket_promedio'); // Obtener T/C

            if (!$aplicationId || !$mes || !$anio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan campos requeridos'
                ], 400);
            }

            // Usar directamente el nombre de la base de datos maestra
            // Verificar si ya existe (incluyendo el día en la búsqueda)
            $query = DB::connection('easyerp_master')
                ->table('metas_locales')
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
                DB::connection('easyerp_master')
                    ->table('metas_locales')
                    ->where('id', $metaExistente->id)
                    ->update([
                        'meta_diaria' => $metaDiaria,
                        'ticket_promedio' => $ticketPromedio,
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
                    'ticket_promedio' => $ticketPromedio,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $metaId = DB::connection('easyerp_master')
                    ->table('metas_locales')
                    ->insertGetId($data);
            }

            $meta = DB::connection('easyerp_master')
                ->table('metas_locales')
                ->where('id', $metaId)
                ->first();

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
     * Obtener datos del dashboard (ventas vs meta)
     * GET /api/web/metas-locales/dashboard?mes=1&anio=2026
     */
    public function getDashboard(Request $request)
    {
        try {
            $mes = $request->input('mes', date('n'));
            $anio = $request->input('anio', date('Y'));
            $dia = $request->input('dia'); // Filtro opcional por día específico

            // Obtener todas las metas del mes/año (con o sin filtro de día)
            $query = DB::connection('easyerp_master')
                ->table('metas_locales')
                ->where('mes', $mes)
                ->where('anio', $anio);
            
            if ($dia !== null) {
                $query->where('dia', $dia);
            }
            
            $metas = $query->get();

            $dashboard = [];

            foreach ($metas as $meta) {
                // Obtener info de la sucursal
                $sucursal = Aplication::find($meta->aplication_id);
                
                if (!$sucursal) continue;

                // Conectar a la base de datos del local
                $connection = new ConectionDB($sucursal);
                $connection->ChangeDBToApp($sucursal, true);

                // Obtener ventas del día especificado o del día actual
                $diaABuscar = $dia ?? $meta->dia ?? date('j');
                $fechaBusqueda = "$anio-$mes-$diaABuscar";

                // Calcular ventas del día
                $ventasDelDia = Sell::whereDate('created_at', $fechaBusqueda)
                    ->where('trash', 0)
                    ->sum('price');

                $porcentaje = $meta->meta_diaria > 0 
                    ? round(($ventasDelDia / $meta->meta_diaria) * 100, 2) 
                    : 0;

                $dashboard[] = [
                    'sucursal_id' => $sucursal->id,
                    'sucursal_nombre' => $sucursal->name,
                    'fecha' => $fechaBusqueda,
                    'dia' => $diaABuscar,
                    'mes' => $mes,
                    'anio' => $anio,
                    'meta_diaria' => $meta->meta_diaria,
                    'ventas_del_dia' => $ventasDelDia,
                    'porcentaje_cumplimiento' => $porcentaje,
                    'estado' => $porcentaje >= 100 ? 'cumplida' : 'pendiente'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $dashboard
            ], 200);

        } catch (\Exception $e) {
            Log::error('MetaLocalController@getDashboard Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener la meta diaria del local actual (solo la meta, no las ventas)
     * GET /api/metas-locales/current
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
            $hoy = date('Y-m-d');

            Log::info('📊 [META API] Iniciando getCurrentLocalMeta', [
                'app_id' => $app->id,
                'mes' => $mes,
                'anio' => $anio,
                'hoy' => $hoy
            ]);

            // Obtener la meta del local actual para el mes/año actual
            $meta = DB::connection('easyerp_master')
                ->table('metas_locales')
                ->where('aplication_id', $app->id)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->first();

            Log::info('📋 [META API] Meta base encontrada:', ['meta' => $meta]);

            // Calcular meta semanal (lunes a domingo de la semana actual)
            $metaSemanal = 0;
            $diasSemana = [];
            
            if ($meta) {
                // Obtener el lunes de la semana actual
                $fechaActual = new \DateTime($hoy);
                $diaSemana = $fechaActual->format('N'); // 1=lunes, 7=domingo
                $diasHastaLunes = $diaSemana - 1;
                $lunes = clone $fechaActual;
                $lunes->modify("-{$diasHastaLunes} days");
                
                Log::info('📅 [META API] Calculando semana', [
                    'fecha_actual' => $hoy,
                    'dia_semana' => $diaSemana,
                    'lunes' => $lunes->format('Y-m-d')
                ]);
                
                // Generar los 7 días de la semana (lunes a domingo)
                for ($i = 0; $i < 7; $i++) {
                    $fecha = clone $lunes;
                    $fecha->modify("+{$i} days");
                    $diaNum = (int)$fecha->format('j');
                    $mesNum = (int)$fecha->format('n');
                    $anioNum = (int)$fecha->format('Y');
                    
                    Log::info("📆 [META API] Buscando día {$i}", [
                        'fecha' => $fecha->format('Y-m-d'),
                        'dia' => $diaNum,
                        'mes' => $mesNum,
                        'anio' => $anioNum,
                        'app_id' => $app->id
                    ]);
                    
                    // Buscar meta para este día específico
                    $metaDia = DB::connection('easyerp_master')
                        ->table('metas_locales')
                        ->where('aplication_id', $app->id)
                        ->where('dia', $diaNum)
                        ->where('mes', $mesNum)
                        ->where('anio', $anioNum)
                        ->first();
                    
                    $montoDia = $metaDia ? (float)$metaDia->meta_diaria : 0;
                    $metaSemanal += $montoDia;
                    
                    Log::info("💵 [META API] Resultado día {$i}", [
                        'fecha' => $fecha->format('Y-m-d'),
                        'meta_encontrada' => $metaDia ? 'SI' : 'NO',
                        'id_registro' => $metaDia ? $metaDia->id : null,
                        'monto' => $montoDia
                    ]);
                    
                    $diasSemana[] = [
                        'fecha' => $fecha->format('Y-m-d'),
                        'dia' => $diaNum,
                        'mes' => $mesNum,
                        'anio' => $anioNum,
                        'meta_diaria' => $montoDia
                    ];
                }
            }

            // Calcular presupuesto para pedidos (30% de la meta semanal)
            $presupuestoPedidos = $metaSemanal * 0.30;
            $presupuestoPorDespacho = $presupuestoPedidos / 3; // 3 despachos

            Log::info('💰 [META API] Resultado final', [
                'meta_semanal' => $metaSemanal,
                'presupuesto_pedidos' => $presupuestoPedidos,
                'presupuesto_por_despacho' => $presupuestoPorDespacho
            ]);

            return response()->json([
                'success' => true,
                'meta' => $meta,
                'meta_semanal' => $metaSemanal,
                'dias_semana' => $diasSemana,
                'presupuesto_pedidos' => $presupuestoPedidos,
                'presupuesto_por_despacho' => $presupuestoPorDespacho
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

    /**
     * Guardar o actualizar ticket promedio de una semana
     * POST /api/web/metas-locales/store-ticket-promedio
     */
    public function storeTicketPromedio(Request $request)
    {
        try {
            $aplicationId = $request->input('aplication_id');
            $mes = $request->input('mes');
            $anio = $request->input('anio');
            $semana = $request->input('semana');
            $ticketPromedio = $request->input('ticket_promedio');

            if (!$aplicationId || !$mes || !$anio || !$semana) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan campos requeridos'
                ], 400);
            }

            // Verificar si ya existe un registro para esta semana
            $ticketExistente = DB::connection('easyerp_master')
                ->table('metas_locales')
                ->where('aplication_id', $aplicationId)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->where('semana', $semana)
                ->whereNotNull('ticket_promedio')
                ->first();

            if ($ticketExistente) {
                // Actualizar
                DB::connection('easyerp_master')
                    ->table('metas_locales')
                    ->where('id', $ticketExistente->id)
                    ->update([
                        'ticket_promedio' => $ticketPromedio,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                $ticketId = $ticketExistente->id;
            } else {
                // Insertar nuevo registro
                $data = [
                    'aplication_id' => $aplicationId,
                    'mes' => $mes,
                    'anio' => $anio,
                    'semana' => $semana,
                    'ticket_promedio' => $ticketPromedio,
                    'dia' => null, // NULL para indicar que es un registro de semana, no de día
                    'meta_diaria' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $ticketId = DB::connection('easyerp_master')
                    ->table('metas_locales')
                    ->insertGetId($data);
            }

            $ticket = DB::connection('easyerp_master')
                ->table('metas_locales')
                ->where('id', $ticketId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Ticket promedio guardado',
                'ticket' => $ticket
            ], 200);

        } catch (\Exception $e) {
            Log::error('MetaLocalController@storeTicketPromedio Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tickets promedio de un mes/año
     * GET /api/web/metas-locales/tickets-promedio?mes=1&anio=2026
     */
    public function getTicketsPromedio(Request $request)
    {
        try {
            $mes = $request->input('mes', date('n'));
            $anio = $request->input('anio', date('Y'));

            $tickets = DB::connection('easyerp_master')
                ->table('metas_locales')
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->whereNotNull('ticket_promedio')
                ->whereNotNull('semana')
                ->orderBy('semana', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tickets
            ], 200);

        } catch (\Exception $e) {
            Log::error('MetaLocalController@getTicketsPromedio Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
