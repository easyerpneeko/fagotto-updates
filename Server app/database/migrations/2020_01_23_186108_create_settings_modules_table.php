<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings_modules', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name', 32);
            $table->string('keyname', 32);

            $table->bigInteger('module_id')->unsigned();
            $table->foreign('module_id')->references('id')->on('modules');

            $table->text('env_vars')->nullable();

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
        Schema::dropIfExists('settings_modules');
    }
}
