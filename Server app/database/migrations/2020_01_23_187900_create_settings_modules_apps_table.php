<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsModulesAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings_modules_apps', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->boolean('active')->default(0);

            $table->bigInteger('settings_module_id')->unsigned();
            $table->foreign('settings_module_id')->references('id')->on('settings_modules');

            $table->bigInteger('modules_apps_id')->unsigned();
            $table->foreign('modules_apps_id')->references('id')->on('modules_apps');

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
        Schema::dropIfExists('settings_modules_apps');
    }
}
