<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuiaDespacho extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guias_despachos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('total', 16, 2)->default(0.00); // Enforce a default value (optional)
            $table->unsignedBigInteger('user');
            $table->unsignedBigInteger('client')->nullable();
            $table->tinyInteger('translado');
            $table->tinyInteger('despacho');
            $table->text('comment')->nullable();
            $table->integer('guia_folio')->nullable();
            $table->boolean('cancel')->default(false); // Use boolean type for cancel flag
            $table->boolean('trash')->default(false);
            $table->timestamps();

            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client')->references('id')->on('clients')->onDelete('set null'); // Allow null for client (optional)

            $table->primary('id');
            $table->index('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guias_despachos');
    }
}