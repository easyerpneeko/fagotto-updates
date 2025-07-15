<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 32);
            $table->string('icon', 24)->nullabe();
            $table->string('description', 256)->nullabe();
            $table->string('keyname', 16)->unique();

            $table->text('migrations')->nullable();//['','']
            $table->text('dependencies')->nullable();//['','']
            $table->text('permissions')->nullable();//['','']
            $table->text('env_vars')->nullable();
            $table->text('typeUsers')->nullable();

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
        Schema::dropIfExists('modules');
    }

}
