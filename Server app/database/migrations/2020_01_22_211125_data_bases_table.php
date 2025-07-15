<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DataBasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('data_bases', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->string('name', 54); //originalmente 48, explicado el cambio en ConectionDB.php:30
          $table->string('username')->default('root');
          $table->string('password');
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
        Schema::dropIfExists('data_bases');
    }
}
