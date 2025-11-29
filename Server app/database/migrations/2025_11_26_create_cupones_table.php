<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuponesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->create('cupones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->enum('tipo_descuento', ['porcentaje', 'monto_fijo'])->default('porcentaje');
            $table->decimal('valor_descuento', 10, 2); // Porcentaje o monto fijo
            $table->integer('usos_maximos')->default(1); // Cantidad de veces que se puede usar
            $table->integer('usos_actuales')->default(0); // Veces que ya se usó
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_expiracion')->nullable();
            $table->decimal('monto_minimo', 10, 2)->nullable(); // Monto mínimo de compra para aplicar
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('creado_por')->nullable(); // Usuario que creó el cupón
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('activo');
            $table->index('fecha_expiracion');
        });

        // Tabla para registrar cada uso de cupón
        Schema::connection('mysql')->create('cupones_usos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cupon_id');
            $table->unsignedBigInteger('orden_id')->nullable(); // ID de la orden donde se usó
            $table->unsignedBigInteger('usuario_id')->nullable(); // Usuario que aplicó el cupón
            $table->decimal('descuento_aplicado', 10, 2);
            $table->timestamp('usado_en');
            
            $table->foreign('cupon_id')->references('id')->on('cupones')->onDelete('cascade');
            $table->index('cupon_id');
            $table->index('orden_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->dropIfExists('cupones_usos');
        Schema::connection('mysql')->dropIfExists('cupones');
    }
}
