<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules_apps', function (Blueprint $table) {
            $table->bigIncrements('id');
            //$table->boolean('active')->default(0);

            $table->bigInteger('app_id')->unsigned();
            $table->foreign('app_id')->references('id')->on('aplications');

            $table->string('version',4);

            $table->bigInteger('module_id')->unsigned();
            $table->foreign('module_id')->references('id')->on('modules');

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
        Schema::dropIfExists('modules_apps');
    }
}
