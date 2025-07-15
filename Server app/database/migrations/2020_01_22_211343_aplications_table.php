<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('aplications', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->string('name',32);
          $table->string('name_public', 16);
          $table->string('serial', 24);
          $table->text('environment_vars')->nullable();
          /*
            dije 24 caracteres de serial, no 32. sin embargo revise el "ApplicationController"
            y contando cuantos genera el "generateCode", supongo deberian ser 25
          */
          $table->timestamp('expiration')->nullable();
          $table->boolean('active')->default(1);

          //Llaves foraneas
          $table->bigInteger('database_app')->unsigned()->nullable();
          $table->foreign('database_app')->references('id')->on('data_bases');

          $table->bigInteger('client')->unsigned()->nullable();
          $table->foreign('client')->references('id')->on('clients');

          // Dinero inicial con el que inicia la app si tiene el modulo de reportes activo
          $table->decimal('init_money', 16,2)->nullable();
          $table->timestamp('init_money_expiration')->nullable();

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
        Schema::dropIfExists('aplications');
    }
}
