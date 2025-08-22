<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArqueoCajaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arqueo_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id');
            $table->date('fecha_arqueo');
            $table->decimal('total_contado', 12, 2)->default(0);
            $table->decimal('total_ventas_efectivo', 12, 2)->default(0);
            $table->decimal('diferencia', 12, 2)->default(0);
            $table->text('detalle_conteo')->nullable(); // JSON con detalle de denominaciones
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario_nombre')->nullable();
            $table->enum('estado', ['abierto', 'cerrado', 'revision'])->default('abierto');
            $table->timestamps();

            // Índices
            $table->index(['app_id', 'fecha_arqueo']);
            $table->index('fecha_arqueo');
            $table->index('app_id');
            
            // Constraint único para evitar múltiples arqueos el mismo día
            $table->unique(['app_id', 'fecha_arqueo'], 'unique_arqueo_por_dia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arqueo_caja');
    }
}
