<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Sells extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::dropIfExists('sells');
      Schema::create('sells', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->decimal('total', 16,2);
        $table->decimal('gananciaTotal', 16,2)->nullable();
        $table->boolean('cancel')->default(0);
        $table->boolean('trash')->default(0);
        $table->string('trash_comment')->nullable();
        $table->decimal('tip',16,2)->nullable();
        $table->decimal('discount', 16,2)->nullable();
        $table->bigInteger('order_id')->unsigned()->nullable();
       // $tabla->foreign('order_id')->references('id')->on('orders');
        // Venta rapida
        $table->boolean('fast_sell')->default(0);
        $table->bigInteger('user')->unsigned();
        $table->foreign('user')->references('id')->on('users');
        $table->bigInteger('user_trash')->unsigned()->nullable();
        $table->foreign('user_trash')->references('id')->on('users');
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
        Schema::dropIfExists('sells');
    }
}
