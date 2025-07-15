<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('boards', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->string('name', 40)->unique();
          $table->boolean('trash')->default(0);
          // UPDATE 3/4/22
          $table->string('color', 16)->nullable();
          $table->timestamps();
      });
      if (Schema::hasTable('orders')) {
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
        Schema::dropIfExists('boards');
    }
}
