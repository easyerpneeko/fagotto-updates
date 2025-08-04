<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFranchiseUberCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('franchise_uber_credentials', function (Blueprint $table) {
            $table->increments('id');
            
            // Identificador de la franquicia
            $table->string('franchise_id')->unique();
            $table->string('franchise_name');
            
            // Credenciales de Uber Eats (encriptadas)
            $table->text('uber_client_id');
            $table->text('uber_client_secret');
            $table->text('uber_store_id');
            
            // Configuraciones adicionales
            $table->string('webhook_url')->nullable();
            $table->integer('prep_time_minutes')->default(15);
            $table->boolean('auto_accept_orders')->default(false);
            
            // Estado y metadata
            $table->boolean('is_active')->default(true);
            $table->boolean('is_configured')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->json('last_test_result')->nullable(); // Resultado de última prueba de conexión
            
            // Información de contacto (opcional)
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index(['franchise_id', 'is_active']);
            $table->index('is_configured');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('franchise_uber_credentials');
    }
}
