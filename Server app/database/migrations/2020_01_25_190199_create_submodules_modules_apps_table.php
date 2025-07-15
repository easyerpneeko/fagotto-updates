<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmodulesModulesAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submodules_modules_apps', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('version',4);

            $table->bigInteger('module_app_id')->unsigned();
            $table->foreign('module_app_id')->references('id')->on('modules_apps');

            $table->bigInteger('submodule')->unsigned();
            $table->foreign('submodule')->references('id')->on('submodules');

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
        Schema::dropIfExists('submodules_modules_apps');
    }
}
