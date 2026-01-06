<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSystemConfigTable extends Migration
{
    /**
     * Run the migrations.
     * Crea tabla de configuración centralizada del sistema
     * Incluye la versión oficial de la aplicación
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_config', function (Blueprint $table) {
            $table->id();
            $table->string('config_key')->unique()->comment('Llave única de configuración');
            $table->text('config_value')->comment('Valor de la configuración');
            $table->string('config_type')->default('string')->comment('Tipo: string, number, boolean, json');
            $table->text('description')->nullable()->comment('Descripción de la configuración');
            $table->boolean('is_editable')->default(true)->comment('Si se puede editar desde el panel');
            $table->timestamps();
            
            $table->index('config_key');
        });

        // Insertar configuraciones iniciales
        DB::table('system_config')->insert([
            [
                'config_key' => 'app_official_version',
                'config_value' => '1.11.50',
                'config_type' => 'string',
                'description' => 'Versión oficial actual de la aplicación Fagotto ERP. Esta es la versión que todos los negocios deberían tener.',
                'is_editable' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'config_key' => 'version_check_source',
                'config_value' => 'database',
                'config_type' => 'string',
                'description' => 'Fuente principal para verificar versión: database, github, or package. Prioridad: 1) database, 2) github, 3) package (fallback)',
                'is_editable' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'config_key' => 'version_last_updated_by',
                'config_value' => 'migration',
                'config_type' => 'string',
                'description' => 'Último usuario que actualizó la versión oficial',
                'is_editable' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'config_key' => 'github_repo_url',
                'config_value' => 'easyerpneeko/fagotto-updates',
                'config_type' => 'string',
                'description' => 'Repositorio de GitHub para verificar releases',
                'is_editable' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_config');
    }
}
