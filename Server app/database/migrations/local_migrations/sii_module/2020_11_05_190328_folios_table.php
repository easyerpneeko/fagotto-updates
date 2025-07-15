<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FoliosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::dropIfExists('folios');
      Schema::create('folios', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->integer('folio')->unsigned()->index();
          $table->integer('folioAsign')->nullable();
          $table->boolean('trash')->default(0);
          $table->enum('type',['factura','boleta','nota_de_credito','guia_de_despacho']);
          $table->text('pdf_url')->nullable();
          $table->text('xml_string')->nullable();
          $table->text('glosa_sii')->nullable();
          $table->text('response_json')->nullable();
          $table->bigInteger('guia_id')->nullable();
          $table->bigInteger('sell_id')->unsigned()->nullable();
          $table->foreign('sell_id')->references('id')->on('sells');
          $table->bigInteger('xml_id')->unsigned();
          $table->foreign('xml_id')->references('id')->on('xml_cargados');
          $table->timestamps();
      });
      if (Schema::hasTable('sells')) {
        Schema::table('sells', function (Blueprint $table) {
          // Sii
          $table->enum('siiState',['aceptada','rechazada','en_espera'])->default('en_espera');
          $table->enum('typeSell',['factura','boleta','nota_de_credito','other','guia_de_despacho'])->nullable();
          $table->enum('other_type',['debito','transferencia','cheque','banco','amipass','multicaja','edenred','convenio_empresa','sodexo','efectivo','credito','guia_despacho','rappi','junaeb','uber'])->nullable();
          $table->text('observacion')->nullable(); // error 
          $table->text('paymode')->nullable();
          $table->text('fecha_vencimiento')->nullable();
          $table->text('fecha_emision')->nullable();
          $table->text('nro_transaccion')->nullable();
          $table->text('documento_referencia')->nullable();
          //$table->decimal('tip',16,2)->nullable(); already added on other migration of sells
          //$table->decimal('discount', 16,2)->nullable(); already added on other migration of sells
          $table->integer('sell_folio')->unsigned()->nullable();
          $table->foreign('sell_folio')->references('folio')->on('folios');
        });


      }
      if (Schema::hasTable('guias_despacho')) {
        Schema::table('guias_despacho', function (Blueprint $table) {
          // Sii
          $table->integer('guia_folio')->unsigned()->nullable();
          $table->foreign('guia_folio')->references('folio')->on('folios');
        });


      }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folios');
    }
}
