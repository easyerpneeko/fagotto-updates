<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Orders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('orders', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('barcode', 31);
        $table->text('products');
        $table->enum('state',['procesada','en espera','cancelado'])->default('en espera');
        $table->decimal('total', 16,2);
        $table->decimal('gananciaTotal', 16,2)->nullable();
        // UPDATE 3/4/22 Descriptions in tickets
        $table->text('description')->nullable();
        $table->decimal('tip',16,2)->nullable();
        $table->decimal('discount', 16,2)->nullable();
        $table->timestamps();
      });
      if (Schema::hasTable('boards') && Schema::hasTable('waiters')) {
        Schema::table('orders', function (Blueprint $table) {
          // Mesero
          $table->bigInteger('waiter_id')->unsigned()->nullable();
          $table->foreign('waiter_id')->references('id')->on('waiters');
          // Mesa
          $table->bigInteger('board_id')->unsigned()->nullable();
          $table->foreign('board_id')->references('id')->on('boards');
        });
      }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
