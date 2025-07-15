<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedPromosSubmodule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      if (Schema::hasTable('products')) {
        Schema::table('products', function (Blueprint $table) {
          $table->decimal('promo_price', 15,2)->nullable();
          $table->tinyInteger('promo_active')->nullable()->default(0);
          $table->bigInteger('product_variable_category')->unsigned()->nullable();
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

    }
}
