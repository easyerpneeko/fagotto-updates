<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUberEatsConfigTable extends Migration
{
    public function up()
    {
        Schema::create('uber_eats_config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('application_id')->unsigned();
            $table->string('store_uuid', 255);
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->datetime('token_expires_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('webhook_url')->nullable();
            $table->timestamps();
            
            $table->foreign('application_id')->references('id')->on('aplications');
            $table->unique(['application_id', 'store_uuid']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('uber_eats_config');
    }
}
