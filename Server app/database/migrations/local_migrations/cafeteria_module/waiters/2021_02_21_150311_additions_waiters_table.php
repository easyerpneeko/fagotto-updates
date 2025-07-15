<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdditionsWaitersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('additions_waiters', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->string('name', 180)->nullable();
          $table->decimal('balance', 16,2);
          $table->integer('quantity')->default(1);

          $table->bigInteger('waiter')->unsigned()->nullable();
          $table->foreign('waiter')->references('id')->on('waiters');

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
        Schema::dropIfExists('additions_waiters');
    }
}
