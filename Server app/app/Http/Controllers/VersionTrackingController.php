<?php
/**
 * Endpoint para recibir tracking de versiones
 * Se integra con el sistema Laravel existente
 */

namespace App\Http\Controllers;

use App\Aplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VersionTrackingController extends Controller
{
    /**
     * Recibir ping de versión desde la aplicación Electron
     * POST /api/track-version
     * Headers: App-Key (serial)
     * Body: { version, system_info }
     */
    public function trackVersion(Request $request)
    {
        try {
            // Obtener el serial desde el header (igual que en AppSecurity middleware)
            $serial = $request->header('App-Key');
            
            if (!$serial) {
                return response()->json([
                    'error' => 'App-Key header no encontrado'
                ], 401);
            }

            // Validar datos
            $request->validate([
                'version' => 'required|string',
                'system_info' => 'nullable|string'
            ]);

            // Buscar la aplicación por serial
            $app = Aplication::where('serial', $serial)->first();
            
            if (!$app) {
                return response()->json([
                    'error' => 'Serial no encontrado'
                ], 404);
            }

            // Verificar si ya existe un registro
            $existing = DB::table('app_version_tracking')
                ->where('app_id', $app->id)
                ->first();

            if ($existing) {
                // Actualizar registro existente
                $oldVersion = $existing->current_version;
                
                DB::table('app_version_tracking')
                    ->where('app_id', $app->id)
                    ->update([
                        'current_version' => $request->version,
                        'app_name' => $app->name,
                        'last_ping' => Carbon::now(),
                        'system_info' => $request->system_info,
                        'updated_at' => Carbon::now()
                    ]);

                // Si cambió la versión, registrar en historial
                if ($oldVersion !== $request->version) {
                    DB::table('app_version_history')->insert([
                        'app_id' => $app->id,
                        'from_version' => $oldVersion,
                        'to_version' => $request->version,
                        'updated_at' => Carbon::now()
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Versión actualizada',
                    'app_id' => $app->id,
                    'app_name' => $app->name,
                    'version' => $request->version,
                    'updated' => $oldVersion !== $request->version
                ]);
            } else {
                // Insertar nuevo registro
                DB::table('app_version_tracking')->insert([
                    'app_id' => $app->id,
                    'app_name' => $app->name,
                    'current_version' => $request->version,
                    'last_ping' => Carbon::now(),
                    'system_info' => $request->system_info,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Primera versión registrada',
                    'app_id' => $app->id,
                    'app_name' => $app->name,
                    'version' => $request->version
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las versiones
     * GET /api/versions
     */
    public function getVersions()
    {
        try {
            $data = DB::table('app_version_tracking')
                ->select(
                    'app_id',
                    'app_name',
                    'current_version',
                    'last_ping',
                    DB::raw('TIMESTAMPDIFF(MINUTE, last_ping, NOW()) as minutes_since_ping'),
                    DB::raw("DATE_FORMAT(last_ping, '%d/%m/%Y %H:%i:%s') as last_ping_formatted"),
                    'system_info',
                    'created_at',
                    'updated_at'
                )
                ->orderBy('last_ping', 'DESC')
                ->get();

            // Obtener la versión más reciente desde GitHub
            $latestVersion = '1.11.42'; // Default
            
            try {
                $githubUrl = 'https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/latest';
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => [
                            'User-Agent: PHP',
                            'Accept: application/vnd.github.v3+json'
                        ],
                        'timeout' => 5
                    ]
                ]);
                
                $response = @file_get_contents($githubUrl, false, $context);
                
                if ($response) {
                    $githubData = json_decode($response, true);
                    if (isset($githubData['tag_name'])) {
                        $latestVersion = str_replace('v', '', $githubData['tag_name']);
                    }
                }
            } catch (\Exception $e) {
                // Si falla GitHub, usar el default
                \Log::warning('Error obteniendo versión de GitHub: ' . $e->getMessage());
            }

            // Calcular estadísticas
            $total = count($data);
            $updated = 0;
            $outdated = 0;
            $offline = 0;

            foreach ($data as $row) {
                if ($row->minutes_since_ping > 60) {
                    $offline++;
                } elseif ($row->current_version === $latestVersion) {
                    $updated++;
                } else {
                    $outdated++;
                }
            }

            // Parsear system_info
            foreach ($data as $row) {
                if ($row->system_info) {
                    $sysInfo = json_decode($row->system_info, true);
                    if ($sysInfo) {
                        $row->system_info = sprintf(
                            "%s %s",
                            ucfirst($sysInfo['platform'] ?? 'N/A'),
                            $sysInfo['arch'] ?? ''
                        );
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'latest_version' => $latestVersion,
                'stats' => [
                    'total' => $total,
                    'updated' => $updated,
                    'outdated' => $outdated,
                    'offline' => $offline
                ],
                'timestamp' => Carbon::now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
