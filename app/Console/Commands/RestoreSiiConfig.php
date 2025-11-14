<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestoreSiiConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:sii-config {--dry-run : Run without making changes}';

    /**
     * The console description of the console command.
     *
     * @var string
     */
    protected $description = 'Restaura las configuraciones del SII que se desactivaron';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔧 Iniciando restauración de configuraciones SII...');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios en la base de datos');
        }

        try {
            // 1. Verificar aplicaciones con módulo de ventas
            $this->info('📋 Verificando aplicaciones con módulo de ventas...');
            $ventasApps = DB::table('aplications as a')
                ->join('modulos_aplicaciones as ma', 'a.id', '=', 'ma.aplication_id')
                ->join('modulos as m', 'ma.modulo_id', '=', 'm.id')
                ->where('m.name', 'ventas')
                ->whereNull('a.deleted_at')
                ->whereNull('ma.deleted_at')
                ->select('a.id as app_id', 'a.name as app_name', 'ma.id as modulo_app_id')
                ->get();

            $this->table(['App ID', 'Nombre', 'Módulo App ID'], 
                $ventasApps->map(function($app) {
                    return [$app->app_id, $app->app_name, $app->modulo_app_id];
                })->toArray()
            );

            // 2. Verificar submódulos SII desactivados
            $this->info('🔍 Verificando submódulos SII...');
            $siiSubmodules = DB::table('aplications as a')
                ->join('modulos_aplicaciones as ma', 'a.id', '=', 'ma.aplication_id')
                ->join('modulos as m', 'ma.modulo_id', '=', 'm.id')
                ->join('submodulos_aplicaciones as sma', 'ma.id', '=', 'sma.modulo_aplicacion_id')
                ->join('submodulos as sm', 'sma.submodulo_id', '=', 'sm.id')
                ->where('m.name', 'ventas')
                ->where('sm.name', 'sii')
                ->whereNull('a.deleted_at')
                ->whereNull('ma.deleted_at')
                ->whereNull('sma.deleted_at')
                ->select('a.id as app_id', 'a.name as app_name', 'sma.id as submodulo_app_id', 'sma.status', 'sma.version')
                ->get();

            $this->table(['App ID', 'Nombre', 'Submódulo App ID', 'Status', 'Versión'], 
                $siiSubmodules->map(function($sub) {
                    return [$sub->app_id, $sub->app_name, $sub->submodulo_app_id, $sub->status, $sub->version];
                })->toArray()
            );

            $desactivados = $siiSubmodules->where('status', '!=', 1);
            
            if ($desactivados->count() > 0) {
                $this->error("❌ Se encontraron {$desactivados->count()} submódulos SII desactivados");
                
                if (!$dryRun) {
                    // 3. Reactivar submódulos SII
                    $this->info('🔄 Reactivando submódulos SII...');
                    $updated = DB::table('submodulos_aplicaciones as sma')
                        ->join('modulos_aplicaciones as ma', 'sma.modulo_aplicacion_id', '=', 'ma.id')
                        ->join('modulos as m', 'ma.modulo_id', '=', 'm.id')
                        ->join('submodulos as sm', 'sma.submodulo_id', '=', 'sm.id')
                        ->join('aplications as a', 'ma.aplication_id', '=', 'a.id')
                        ->where('m.name', 'ventas')
                        ->where('sm.name', 'sii')
                        ->whereNull('a.deleted_at')
                        ->whereNull('ma.deleted_at')
                        ->whereNull('sma.deleted_at')
                        ->update(['sma.status' => 1]);

                    $this->info("✅ Se reactivaron {$updated} submódulos SII");
                }
            } else {
                $this->info('✅ Todos los submódulos SII están activos');
            }

            // 4. Verificar configuraciones de ajustes
            $this->info('⚙️  Verificando configuraciones de ajustes SII...');
            $siiSettings = DB::table('aplications as a')
                ->join('modulos_aplicaciones as ma', 'a.id', '=', 'ma.aplication_id')
                ->join('modulos as m', 'ma.modulo_id', '=', 'm.id')
                ->join('submodulos_aplicaciones as sma', 'ma.id', '=', 'sma.modulo_aplicacion_id')
                ->join('submodulos as sm', 'sma.submodulo_id', '=', 'sm.id')
                ->join('submodulos_settings as ss', 'sma.id', '=', 'ss.submodulo_aplicacion_id')
                ->where('m.name', 'ventas')
                ->where('sm.name', 'sii')
                ->whereIn('ss.key_name', [
                    'boleta', 'boleta_local', 'factura', 'nota_de_credito',
                    'debito', 'transferencia', 'efectivo', 'banco',
                    'credito', 'cheque', 'rappi', 'junaeb', 'uber',
                    'amipass', 'edenred', 'convenio_empresa', 'multicaja',
                    'pedidos_ya', 'pluxee', 'banco_chile_20', 'halloween_20'
                ])
                ->whereNull('a.deleted_at')
                ->whereNull('ma.deleted_at')
                ->whereNull('sma.deleted_at')
                ->select('a.id as app_id', 'a.name as app_name', 'ss.key_name', 'ss.value', 'ss.status')
                ->orderBy('a.name')
                ->orderBy('ss.key_name')
                ->get();

            // Mostrar configuraciones por aplicación
            $appsWithSettings = $siiSettings->groupBy('app_id');
            foreach ($appsWithSettings as $appId => $settings) {
                $appName = $settings->first()->app_name;
                $this->info("📱 Configuraciones para: {$appName} (ID: {$appId})");
                
                $settingsData = $settings->map(function($setting) {
                    $statusIcon = $setting->value == '1' ? '✅' : '❌';
                    return [$setting->key_name, $setting->value, $setting->status, $statusIcon];
                })->toArray();
                
                $this->table(['Configuración', 'Valor', 'Status', 'Estado'], $settingsData);
            }

            // 5. Restaurar configuraciones críticas
            $criticalSettings = ['boleta', 'boleta_local', 'efectivo', 'debito', 'transferencia'];
            $problematicSettings = $siiSettings->whereIn('key_name', $criticalSettings)->where('value', '!=', '1');
            
            if ($problematicSettings->count() > 0) {
                $this->error("❌ Se encontraron {$problematicSettings->count()} configuraciones críticas desactivadas");
                
                if (!$dryRun) {
                    $this->info('🔄 Restaurando configuraciones críticas...');
                    $updatedSettings = DB::table('submodulos_settings as ss')
                        ->join('submodulos_aplicaciones as sma', 'ss.submodulo_aplicacion_id', '=', 'sma.id')
                        ->join('modulos_aplicaciones as ma', 'sma.modulo_aplicacion_id', '=', 'ma.id')
                        ->join('modulos as m', 'ma.modulo_id', '=', 'm.id')
                        ->join('submodulos as sm', 'sma.submodulo_id', '=', 'sm.id')
                        ->join('aplications as a', 'ma.aplication_id', '=', 'a.id')
                        ->where('m.name', 'ventas')
                        ->where('sm.name', 'sii')
                        ->whereIn('ss.key_name', $criticalSettings)
                        ->whereNull('a.deleted_at')
                        ->whereNull('ma.deleted_at')
                        ->whereNull('sma.deleted_at')
                        ->update([
                            'ss.value' => '1',
                            'ss.status' => 1
                        ]);

                    $this->info("✅ Se restauraron {$updatedSettings} configuraciones críticas");
                }
            } else {
                $this->info('✅ Todas las configuraciones críticas están activas');
            }

            // 6. Verificación final
            if (!$dryRun) {
                $this->info('🔍 Verificación final...');
                $finalCheck = DB::table('aplications as a')
                    ->join('modulos_aplicaciones as ma', 'a.id', '=', 'ma.aplication_id')
                    ->join('modulos as m', 'ma.modulo_id', '=', 'm.id')
                    ->join('submodulos_aplicaciones as sma', 'ma.id', '=', 'sma.modulo_aplicacion_id')
                    ->join('submodulos as sm', 'sma.submodulo_id', '=', 'sm.id')
                    ->where('m.name', 'ventas')
                    ->where('sm.name', 'sii')
                    ->where('sma.status', '!=', 1)
                    ->whereNull('a.deleted_at')
                    ->whereNull('ma.deleted_at')
                    ->whereNull('sma.deleted_at')
                    ->count();

                if ($finalCheck == 0) {
                    $this->info('🎉 ¡Restauración completada exitosamente!');
                    $this->info('✅ Todos los submódulos SII están activos');
                } else {
                    $this->error("❌ Aún hay {$finalCheck} submódulos SII desactivados");
                }
            }

            $this->info('🏁 Proceso completado');
            
            if ($dryRun) {
                $this->warn('💡 Para aplicar los cambios, ejecuta: php artisan restore:sii-config');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error durante la restauración: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
