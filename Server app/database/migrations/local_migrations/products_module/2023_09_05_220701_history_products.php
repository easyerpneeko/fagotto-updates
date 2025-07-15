<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HistoryProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('transaction')->nullable();
            $table->enum('type', ['created', 'updated', 'deleted', 'stock'])->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->timestamps();
            $table->string('name_new', 80)->nullable();
            $table->string('name_old', 80)->nullable();
            $table->decimal('price_venta_old', 15, 2)->nullable();
            $table->decimal('price_venta_new', 15, 2)->nullable();
            $table->decimal('price_compra_old', 15, 2)->nullable();
            $table->decimal('price_compra_new', 15, 2)->nullable();
            $table->decimal('ganancia_old', 15, 2)->nullable();
            $table->decimal('ganancia_new', 15, 2)->nullable();
            $table->integer('stock_old')->nullable();
            $table->integer('stock_new')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('field_afected', 100)->nullable();
            $table->string('old_value', 100)->nullable();
            $table->string('new_value', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_products');
    }
}
