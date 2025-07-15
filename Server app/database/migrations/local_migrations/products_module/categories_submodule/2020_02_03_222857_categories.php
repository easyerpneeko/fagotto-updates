<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Categories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      Schema::dropIfExists('categories');
      
      Schema::create('categories', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('name', 24);
        $table->boolean('trash')->default(0);
        $table->tinyInteger('status')->default(0);
        $table->bigInteger('user')->unsigned();
        $table->foreign('user')->references('id')->on('users');
        $table->timestamps();
      });

      if (Schema::hasTable('products')) {
        Schema::table('products', function (Blueprint $table) {
          $table->bigInteger('category')->unsigned()->nullable();
          $table->foreign('category')->references('id')->on('categories');
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
        Schema::dropIfExists('categories');
    }
}
