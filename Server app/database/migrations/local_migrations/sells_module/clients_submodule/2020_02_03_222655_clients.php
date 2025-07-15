<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Clients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::dropIfExists('clients');
      Schema::create('clients', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('name', 32)->nullable();
        $table->string('lastname', 32)->nullable();
        $table->string('rut', 12);
        $table->string('city',20)->nullable();
        $table->string('comuna',20)->nullable();
        $table->string('razon_social',100)->nullable();
        $table->string('phone',20)->nullable();
        $table->string('direction',70)->nullable();
        $table->string('giro',80)->nullable();
        $table->string('email',256)->nullable();//new
        $table->timestamps();
      });
      if (Schema::hasTable('sells') && !Schema::hasColumn('sells', 'client')) {
        Schema::table('sells', function (Blueprint $table) {
          $table->bigInteger('client')->unsigned()->nullable();
          $table->foreign('client')->references('id')->on('clients');
        });
      }
      if (Schema::hasTable('guias_despachos') && !Schema::hasColumn('guias_despachos', 'client')) {
        Schema::table('guias_despachos', function (Blueprint $table) {
          $table->bigInteger('client')->unsigned()->nullable();
          $table->foreign('client')->references('id')->on('clients');
        });
      }
      if (Schema::hasTable('client_orders') && !Schema::hasColumn('client_orders', 'client_id')) {
        Schema::table('client_orders', function (Blueprint $table) {
          $table->bigInteger('client_id')->unsigned()->nullable();
          $table->foreign('client_id')->references('id')->on('clients');
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
        Schema::dropIfExists('clients');
    }
}
