<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTurbus10PaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar 'turbus_10' al ENUM de other_type en la tabla sells
        if (Schema::hasTable('sells')) {
            DB::statement("ALTER TABLE sells MODIFY COLUMN other_type ENUM('debito','transferencia','cheque','banco','amipass','multicaja','edenred','convenio_empresa','sodexo','efectivo','credito','guia_despacho','rappi','junaeb','uber','uber_eats','pedidos_ya','pluxee','banco_chile_20','fagotto_10','turbus_10','halloween_20') NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el ENUM al estado anterior sin turbus_10
        if (Schema::hasTable('sells')) {
            DB::statement("ALTER TABLE sells MODIFY COLUMN other_type ENUM('debito','transferencia','cheque','banco','amipass','multicaja','edenred','convenio_empresa','sodexo','efectivo','credito','guia_despacho','rappi','junaeb','uber','uber_eats','pedidos_ya','pluxee','banco_chile_20','fagotto_10','halloween_20') NULL");
        }
    }
}
