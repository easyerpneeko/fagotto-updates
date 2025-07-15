<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsGuiaDespacho extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_guia_despacho', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('price', 16, 2);
            $table->decimal('gananciaTotal', 16, 2)->nullable();
            $table->string('quantity', 255); // Consider using integer if whole numbers are appropriate
            $table->decimal('unitary_price', 16, 2);

            $table->unsignedBigInteger('product');
            $table->unsignedBigInteger('guia')->nullable();

            $table->timestamps();

            $table->foreign('product')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('guia')->references('id')->on('guias_despachos')->onDelete('set null');

            $table->primary('id');
            $table->index('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_guia_despacho');
    }
}
