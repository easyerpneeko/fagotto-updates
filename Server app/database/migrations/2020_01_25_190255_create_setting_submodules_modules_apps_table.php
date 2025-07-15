<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingSubmodulesModulesAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_submodules_modules_apps', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('submodule_module_apps_id')->unsigned();
            $table->foreign('submodule_module_apps_id')->references('id')->on('submodules_modules_apps');

            $table->bigInteger('setting_submodule_id')->unsigned();
            $table->foreign('setting_submodule_id')->references('id')->on('settings_submodules');

            $table->boolean('active')->default(0);

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
        Schema::dropIfExists('setting_submodules_modules_apps');
    }
}
