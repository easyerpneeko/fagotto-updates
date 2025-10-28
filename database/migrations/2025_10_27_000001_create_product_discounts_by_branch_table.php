<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDiscountsByBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_discounts_by_branch', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id')->comment('ID de la sucursal/negocio');
            $table->unsignedBigInteger('product_id')->nullable()->comment('ID del producto específico (NULL = todos)');
            $table->string('product_category')->nullable()->comment('Categoría de productos (ej: salsas)');
            $table->string('product_name_pattern')->nullable()->comment('Patrón de nombre (ej: %salsa%)');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('Porcentaje de descuento (10.00 = 10%)');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Monto fijo de descuento');
            $table->boolean('active')->default(true)->comment('Si está activo o no');
            $table->date('start_date')->nullable()->comment('Fecha inicio de vigencia');
            $table->date('end_date')->nullable()->comment('Fecha fin de vigencia');
            $table->text('description')->nullable()->comment('Descripción del descuento');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('application_id');
            $table->index('product_id');
            $table->index('active');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_discounts_by_branch');
    }
}
