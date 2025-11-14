<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDeliveryPlatformsInfoToSells extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar columnas para información de pagos de plataformas de delivery
        if (Schema::hasTable('sells')) {
            Schema::table('sells', function (Blueprint $table) {
                // Columnas para almacenar información JSON de cada plataforma
                $table->text('uber_payment_info')->nullable()->after('other_type');
                $table->text('rappi_payment_info')->nullable()->after('uber_payment_info');
                $table->text('pedidos_ya_payment_info')->nullable()->after('rappi_payment_info');
                $table->text('special_payment_info')->nullable()->after('pedidos_ya_payment_info');
            });

            // Modificar el ENUM de other_type para agregar 'pedidos_ya'
            // NOTA: En MySQL no se puede modificar un ENUM directamente, hay que usar ALTER TABLE
            DB::statement("ALTER TABLE sells MODIFY COLUMN other_type ENUM('debito','transferencia','cheque','banco','amipass','multicaja','edenred','convenio_empresa','sodexo','efectivo','credito','guia_despacho','rappi','junaeb','uber','uber_eats','pedidos_ya','pluxee','banco_chile_20','fagotto_10','halloween_20') NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('sells')) {
            Schema::table('sells', function (Blueprint $table) {
                $table->dropColumn(['uber_payment_info', 'rappi_payment_info', 'pedidos_ya_payment_info', 'special_payment_info']);
            });

            // Revertir el ENUM a su estado original
            DB::statement("ALTER TABLE sells MODIFY COLUMN other_type ENUM('debito','transferencia','cheque','banco','amipass','multicaja','edenred','convenio_empresa','sodexo','efectivo','credito','guia_despacho','rappi','junaeb','uber') NULL");
        }
    }
}
