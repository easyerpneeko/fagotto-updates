<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsSubmodulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings_submodules', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name',32);
            $table->string('keyname',32);

            $table->bigInteger('submodule_id')->unsigned();
            $table->foreign('submodule_id')->references('id')->on('submodules');

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
        Schema::dropIfExists('settings_submodules');
    }
}
