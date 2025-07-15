<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class XmlCargadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('xml_cargados', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->integer('typeNumber');
          $table->enum('type',['factura','boleta','nota_de_credito','guia_de_despacho']);
          $table->integer('folio_inicial');
          $table->integer('folio_final');
          $table->text('jsonXML');
          $table->text('contentXML');
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
      Schema::dropIfExists('xml_cargados');
    }
}
