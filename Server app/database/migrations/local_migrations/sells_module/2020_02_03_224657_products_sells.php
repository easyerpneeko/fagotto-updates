<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductsSells extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::dropIfExists('products_sells');
      Schema::create('products_sells', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->decimal('price', 16,2);
        $table->decimal('gananciaTotal', 16,2)->nullable();
        $table->string('quantity');
        $table->decimal('unitary_price', 16,2);

        $table->bigInteger('product')->unsigned();
        $table->foreign('product')->references('id')->on('products');

        $table->bigInteger('sell')->unsigned()->nullable();
        $table->foreign('sell')->references('id')->on('sells');
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_sells');
    }
}
