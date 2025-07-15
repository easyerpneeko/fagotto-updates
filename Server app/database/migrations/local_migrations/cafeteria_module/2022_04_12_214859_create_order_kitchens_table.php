<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderKitchensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_kitchens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->bigInteger('sell_id')->unsigned()->nullable();

            $table->text('products')->nullable();
            $table->string('random_color', 7)->nullable();
            $table->string('kitchen_state'); // ["pending","process","complete", "cancelled", "closed"]

            if (Schema::hasTable('orders')) {
                $table->foreign('order_id')->references('id')->on('orders');
            }

            $table->foreign('sell_id')->references('id')->on('sells');

            $table->timestamp('completed_at')->nullable();
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
        Schema::dropIfExists('order_kitchens');
    }
}
