<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event', 32)->nullable();
            $table->string('title')->nullable();
            $table->string('history_type', 96)->nullable();
            $table->text('entry_data')->nullable();
            $table->text('entry_info')->nullable();
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
        Schema::dropIfExists('history_movements');
    }
}
