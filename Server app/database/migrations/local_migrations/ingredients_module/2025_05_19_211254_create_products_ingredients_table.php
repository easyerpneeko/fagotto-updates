<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT
            $table->bigInteger('product_id')->unsigned(); // BIGINT(20) UNSIGNED NOT NULL
            $table->bigInteger('ingredient_id')->unsigned(); // BIGINT(20) UNSIGNED NOT NULL
            $table->timestamps(); // created_at TIMESTAMP NULL DEFAULT NULL, updated_at TIMESTAMP NULL DEFAULT NULL

            // Foreign key constraints
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onUpdate('restrict') // ON UPDATE RESTRICT
                  ->onDelete('restrict'); // ON DELETE RESTRICT

            $table->foreign('ingredient_id')
                  ->references('id')->on('ingredients')
                  ->onUpdate('restrict') // ON UPDATE RESTRICT
                  ->onDelete('restrict'); // ON DELETE RESTRICT
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_ingredients');
    }
}