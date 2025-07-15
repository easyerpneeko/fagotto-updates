<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneRepairOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_repair_orders', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('device_model', 32)->nullable();
            $table->text('device_condition');
            $table->string('device_failure', 256)->nullable();
            $table->string('device_imei', 15)->nullable();
            $table->string('device_password', 24)->nullable();
            $table->string('observations', 512)->nullable();
            $table->bigInteger('technician_id')->unsigned()->nullable();
            $table->foreign('technician_id')->references('id')->on('users');
            $table->decimal('budget', 16,2)->nullable();
            $table->string('contact_email',256)->nullable();
            $table->string('contact_phone',20)->nullable();

            $table->timestamps();
        });
        if (Schema::hasTable('clients')) {
          if (!Schema::hasColumn('client_orders', 'client_id')) {
            Schema::table('client_orders', function (Blueprint $table) {
              $table->bigInteger('client_id')->unsigned()->nullable();
              $table->foreign('client_id')->references('id')->on('clients');
            });
          }
          if (!Schema::hasColumn('clients', 'email')) {
            Schema::table('clients', function (Blueprint $table) {
              $table->string('email',256)->nullable();//new
            });
          }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phone_repair_orders');
    }
}
