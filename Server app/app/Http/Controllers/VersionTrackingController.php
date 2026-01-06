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

            // Obtener la versión oficial con triple verificación
            $latestVersion = $this->getOfficialVersion();
            \Log::info('📊 Versión oficial determinada: ' . $latestVersion['version'] . ' (Fuente: ' . $latestVersion['source'] . ')');

            // Calcular estadísticas
            $total = count($data);
            $updated = 0;
            $outdated = 0;
            $offline = 0;

            foreach ($data as $row) {
                if ($row->minutes_since_ping > 60) {
                    $offline++;
                } elseif ($row->current_version === $latestVersion['version']) {
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
                'latest_version' => $latestVersion['version'],
                'version_source' => $latestVersion['source'],
                'version_timestamp' => $latestVersion['timestamp'],
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

    /**
     * Obtener la versión oficial del sistema con triple verificación
     * Prioridad: 1) Base de datos (system_config), 2) GitHub API, 3) Fallback hardcoded
     * 
     * @return array ['version' => string, 'source' => string, 'timestamp' => string]
     */
    private function getOfficialVersion()
    {
        $version = null;
        $source = null;
        
        // 1️⃣ PRIMERA OPCIÓN: Base de datos (system_config) - LA MÁS CONFIABLE
        try {
            $config = DB::table('system_config')
                ->where('config_key', 'app_official_version')
                ->first();
            
            if ($config && !empty($config->config_value)) {
                \Log::info('✅ Versión obtenida desde DB: ' . $config->config_value);
                return [
                    'version' => $config->config_value,
                    'source' => 'database',
                    'timestamp' => $config->updated_at
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('⚠️ Error obteniendo versión de DB: ' . $e->getMessage());
        }

        // 2️⃣ SEGUNDA OPCIÓN: GitHub API - SI LA DB NO ESTÁ DISPONIBLE
        try {
            $repoConfig = DB::table('system_config')
                ->where('config_key', 'github_repo_url')
                ->first();
            
            $repo = $repoConfig ? $repoConfig->config_value : 'easyerpneeko/fagotto-updates';
            $githubUrl = "https://api.github.com/repos/{$repo}/releases/latest";
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: Fagotto-ERP-Server',
                        'Accept: application/vnd.github.v3+json'
                    ],
                    'timeout' => 5
                ]
            ]);
            
            $response = @file_get_contents($githubUrl, false, $context);
            
            if ($response) {
                $githubData = json_decode($response, true);
                if (isset($githubData['tag_name'])) {
                    $version = str_replace('v', '', $githubData['tag_name']);
                    \Log::info('✅ Versión obtenida desde GitHub: ' . $version);
                    return [
                        'version' => $version,
                        'source' => 'github',
                        'timestamp' => $githubData['published_at'] ?? now()
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('⚠️ Error obteniendo versión de GitHub: ' . $e->getMessage());
        }

        // 3️⃣ ÚLTIMA OPCIÓN: Fallback hardcoded (package.json actual)
        \Log::warning('⚠️ Usando versión hardcoded como fallback');
        return [
            'version' => '1.11.52', // ⚠️ ACTUALIZAR ESTO CUANDO CAMBIES package.json
            'source' => 'fallback_hardcoded',
            'timestamp' => now()
        ];
    }

    /**
     * Actualizar la versión oficial en la base de datos
     * POST /api/versions/update-official
     * Body: { version, updated_by }
     */
    public function updateOfficialVersion(Request $request)
    {
        try {
            $request->validate([
                'version' => 'required|string|regex:/^\d+\.\d+\.\d+$/',
                'updated_by' => 'nullable|string'
            ]);

            // Actualizar o crear versión oficial
            DB::table('system_config')->updateOrInsert(
                ['config_key' => 'app_official_version'],
                [
                    'config_value' => $request->version,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );

            // Registrar quién actualizó
            if ($request->updated_by) {
                DB::table('system_config')->updateOrInsert(
                    ['config_key' => 'version_last_updated_by'],
                    [
                        'config_value' => $request->updated_by,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            }

            \Log::info('📝 Versión oficial actualizada a: ' . $request->version . ' por: ' . ($request->updated_by ?? 'unknown'));

            return response()->json([
                'success' => true,
                'message' => 'Versión oficial actualizada correctamente',
                'new_version' => $request->version,
                'updated_by' => $request->updated_by ?? 'unknown',
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error actualizando versión',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener configuración de versiones
     * GET /api/versions/config
     */
    public function getVersionConfig()
    {
        try {
            $config = DB::table('system_config')
                ->whereIn('config_key', [
                    'app_official_version',
                    'version_check_source',
                    'version_last_updated_by',
                    'github_repo_url'
                ])
                ->get()
                ->keyBy('config_key');

            return response()->json([
                'success' => true,
                'config' => $config,
                'current_official_version' => $this->getOfficialVersion()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error obteniendo configuración',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
