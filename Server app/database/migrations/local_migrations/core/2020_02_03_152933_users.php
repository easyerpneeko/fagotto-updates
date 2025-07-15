<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('users', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->string('username', 24)->unique();
        //   $table->string('email', 128);
          $table->string('fullname', 48);
          $table->text('password');
          $table->string('avatar')->nullable();
          $table->boolean('active')->default(1);
          $table->boolean('trash')->default(0);

          $table->bigInteger('role')->unsigned()->nullable();
          $table->foreign('role')->references('id')->on('type_users');

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
        Schema::dropIfExists('users');
    }
}
