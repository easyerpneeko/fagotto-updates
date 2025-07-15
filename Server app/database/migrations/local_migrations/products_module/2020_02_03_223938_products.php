<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Products extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::dropIfExists('products');
      Schema::create('products', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('image');
        $table->string('name', 80);

        $table->integer('key_system')->nullable();

        $table->string('barcode', 48)->nullable();
        $table->integer('stock')->nullable();
        $table->integer('min_quantity')->nullable();
        $table->boolean('cecina')->default(0);

        $table->decimal('compra', 15,2)->nullable();
        $table->decimal('price', 15,2)->nullable();
        $table->decimal('mayor', 15,2)->nullable();
        $table->text('prices')->nullable();
        $table->decimal('ganancia', 15,2)->nullable();
        $table->decimal('ganancia_mayor', 15,2)->nullable();

        $table->boolean('active')->default(1);
        $table->boolean('trash')->default(0);

        $table->bigInteger('user')->unsigned()->nullable();
        $table->foreign('user')->references('id')->on('users');

        $table->timestamps();
      });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
