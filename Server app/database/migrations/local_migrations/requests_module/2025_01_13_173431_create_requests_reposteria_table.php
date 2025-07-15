<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests_reposteria', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contact_name', 70)->collation('utf8mb4_unicode_ci');
            $table->string('contact_phone', 20)->collation('utf8mb4_unicode_ci');
            $table->enum('paymode', ['Credito', 'Transferencia', 'Efectivo'])->collation('utf8mb4_unicode_ci');
            $table->text('voucher')->nullable()->collation('utf8mb4_unicode_ci');
            $table->enum('status_payment', ['impagado', 'pagado'])->collation('utf8mb4_unicode_ci');
            $table->enum('status', ['aprobado', 'rechazado', 'espera','nuevo'])->nullable()->collation('utf8mb4_unicode_ci');
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('iva', 15, 2)->nullable();
            $table->decimal('emergency', 15, 2)->nullable();
            $table->decimal('despacho', 15, 2)->nullable();
            $table->text('products')->collation('utf8mb4_unicode_ci');
            $table->text('comment')->nullable();
            $table->bigInteger('app_id')->unsigned();
            $table->tinyInteger('print')->unsigned()->nullable();
            $table->text('pdf_url')->nullable();
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
        Schema::dropIfExists('requests');
    }
}