<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmodulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submodules', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('name',32);
            $table->string('keyname',16)->unique();
            $table->string('description', 256)->nullable();

            $table->bigInteger('module_id')->unsigned();
            $table->foreign('module_id')->references('id')->on('modules');

            $table->text('migrations')->nullable();//['','']
            $table->text('dependencies')->nullable();//['','']
            $table->text('permissions')->nullable();//['','']
            $table->text('env_vars')->nullable();

            $table->string('version',4);

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
        Schema::dropIfExists('submodules');
    }
}
