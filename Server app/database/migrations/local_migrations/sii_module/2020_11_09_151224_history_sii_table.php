<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HistorySiiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('history_siis', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->string('message');
          $table->text('xml_string')->nullable();
          $table->text('pdf_url')->nullable();
          $table->bigInteger('guia_id')->nullable();
          $table->text('response_json')->nullable();
          $table->integer('folio')->nullable();
          $table->enum('typeFolio',['factura','boleta','nota_de_credito','guia_de_despacho'])->nullable();

          $table->bigInteger('sell_id')->unsigned()->nullable();
          $table->foreign('sell_id')->references('id')->on('sells');
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
        Schema::dropIfExists('history_siis');
    }
}
