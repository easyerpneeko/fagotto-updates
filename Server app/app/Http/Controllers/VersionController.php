<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Aplication;

class VersionController extends Controller
{
    /**
     * Obtener listado de versiones de aplicaciones desde app_version_tracking
     */
    public function index()
    {
        try {
            // Obtener la última versión desde GitHub (package.json)
            $latestVersionFromGitHub = $this->getLatestVersionFromGitHub();
            
            // Obtener versiones desde app_version_tracking (versión actual de cada app)
            $versiones = DB::table('easyerp.app_version_tracking as avt')
                ->join('easyerp.aplications as a', 'avt.app_id', '=', 'a.id')
                ->whereNotIn('a.id', [120, 121, 122]) // Excluir IDs específicos
                ->select(
                    'a.id',
                    'a.name',
                    'a.name_public',
                    'a.serial',
                    'a.active',
                    'avt.current_version as version',
                    'avt.last_ping',
                    'avt.system_info',
                    'avt.updated_at'
                )
                ->orderBy('avt.last_ping', 'desc')
                ->get()
                ->map(function($app) {
                    // Calcular minutos desde último ping
                    $lastPing = $app->last_ping ? \Carbon\Carbon::parse($app->last_ping) : null;
                    $minutesSincePing = $lastPing ? $lastPing->diffInMinutes(now()) : null;
                    $lastPingFormatted = $lastPing ? $lastPing->format('Y-m-d H:i:s') : 'Nunca';
                    
                    return [
                        'app_id' => $app->id,
                        'app_name' => $app->name_public ?? $app->name ?? 'Sin nombre',
                        'serial' => $app->serial,
                        'current_version' => $app->version ?? '0.0.0',
                        'last_ping' => $app->last_ping,
                        'last_ping_formatted' => $lastPingFormatted,
                        'minutes_since_ping' => $minutesSincePing ?? 9999,
                        'system_info' => $app->system_info,
                        'active' => $app->active,
                        'updated_at' => $app->updated_at,
                    ];
                });

            // Si no hay datos en app_version_tracking, obtener todas las aplicaciones
            if ($versiones->isEmpty()) {
                $versiones = DB::table('easyerp.aplications')
                    ->whereNotIn('id', [120, 121, 122]) // Excluir IDs específicos
                    ->select('id', 'name', 'name_public', 'serial', 'active', 'updated_at')
                    ->orderBy('updated_at', 'desc')
                    ->get()
                    ->map(function($app) {
                        return [
                            'app_id' => $app->id,
                            'app_name' => $app->name_public ?? $app->name ?? 'Sin nombre',
                            'serial' => $app->serial,
                            'current_version' => '0.0.0',
                            'last_ping' => null,
                            'last_ping_formatted' => 'Nunca',
                            'minutes_since_ping' => 9999,
                            'system_info' => null,
                            'active' => $app->active,
                            'updated_at' => $app->updated_at,
                        ];
                    });
            }

            // Usar la versión de GitHub como la última versión, si no se pudo obtener, usar fallback
            $latestVersion = $latestVersionFromGitHub ?? DB::table('easyerp.app_version_tracking')
                ->max('current_version') ?? '1.11.50';

            // Calcular estadísticas
            $total = $versiones->count();
            $actualizado = $versiones->where('current_version', $latestVersion)->count();
            $desactualizado = $versiones->where('current_version', '!=', $latestVersion)
                                        ->where('current_version', '!=', '0.0.0')
                                        ->count();
            $sin_version = $versiones->where('current_version', '0.0.0')->count();

            $stats = [
                'total' => $total,
                'actualizado' => $actualizado,
                'desactualizado' => $desactualizado,
                'sin_version' => $sin_version,
            ];

            return response()->json([
                'success' => true,
                'data' => $versiones,
                'latest_version' => $latestVersion,
                'stats' => $stats
            ], 200);

        } catch (\Exception $e) {
            \Log::error('VersionController@index Error: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener versiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener la última versión desde GitHub (package.json)
     */
    private function getLatestVersionFromGitHub()
    {
        try {
            // Primero intentar leer el package.json local (más confiable)
            $localPackagePath = base_path('../package.json');
            
            if (file_exists($localPackagePath)) {
                $jsonContent = file_get_contents($localPackagePath);
                $packageData = json_decode($jsonContent, true);
                
                if (isset($packageData['version'])) {
                    \Log::info('Versión obtenida desde package.json local: ' . $packageData['version']);
                    return $packageData['version'];
                }
            }
            
            // Si no existe local, intentar desde GitHub
            $url = 'https://raw.githubusercontent.com/orlandodaniel/FRONT-PROJECT-VUE-DEV/fagotto-dev/package.json';
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5
                ]
            ]);
            
            $jsonContent = @file_get_contents($url, false, $context);
            
            if ($jsonContent !== false) {
                $packageData = json_decode($jsonContent, true);
                
                if (isset($packageData['version'])) {
                    \Log::info('Versión obtenida desde GitHub: ' . $packageData['version']);
                    return $packageData['version'];
                }
            }
            
            // Fallback: versión hardcodeada
            \Log::warning('Usando versión hardcodeada como fallback');
            return '1.11.50';
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo versión: ' . $e->getMessage());
            return '1.11.50'; // Fallback
        }
    }
}
