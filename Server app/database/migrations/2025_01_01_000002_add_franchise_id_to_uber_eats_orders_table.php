<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFranchiseIdToUberEatsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uber_eats_orders', function (Blueprint $table) {
            $table->string('franchise_id')->nullable()->after('store_id');
            $table->index(['franchise_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('uber_eats_orders', function (Blueprint $table) {
            $table->dropIndex(['franchise_id', 'status']);
            $table->dropColumn('franchise_id');
        });
    }
}
