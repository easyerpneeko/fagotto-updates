<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodToRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->string('payment_method')->default('contado')->after('paymode');
            $table->string('invoice_type')->default('ticket')->after('payment_method');
        });
    }

    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('invoice_type');
        });
    }
}
