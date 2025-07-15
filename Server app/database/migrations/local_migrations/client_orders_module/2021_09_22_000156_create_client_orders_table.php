<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('orderable_type')->nullable();
            $table->string('orderable_id')->nullable();
            $table->timestamps();
        });
        if (Schema::hasTable('clients')) {
          if (!Schema::hasColumn('client_orders', 'client_id')) {
            Schema::table('client_orders', function (Blueprint $table) {
              $table->bigInteger('client_id')->unsigned()->nullable();
              $table->foreign('client_id')->references('id')->on('clients');
            });
          }
          if (!Schema::hasColumn('clients', 'email')) {
            Schema::table('clients', function (Blueprint $table) {
              $table->string('email',256)->nullable();//new
            });
          }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_orders');
    }
}
