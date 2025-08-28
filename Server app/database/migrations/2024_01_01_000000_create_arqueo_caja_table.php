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
        Schema::create('TurnosCaja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_termino')->nullable();
            $table->decimal('total_sistema', 12, 2)->default(0);
            $table->decimal('total_contado', 12, 2)->default(0);
            $table->decimal('diferencia', 12, 2)->default(0);
            $table->enum('estado', ['abierto', 'cerrado', 'revision'])->default('abierto');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['app_id', 'fecha_inicio']);
            $table->index('fecha_inicio');
            $table->index('app_id');
            $table->index('usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TurnosCaja');
    }
}
