<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUberEatsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uber_eats_orders', function (Blueprint $table) {
            $table->increments('id');
            
            // Identificadores de Uber
            $table->string('uber_order_id')->unique();
            $table->string('display_id')->nullable();
            $table->string('external_reference_id')->nullable();
            
            // Estado y tipo de orden
            $table->string('status')->default('created');
            $table->string('order_type')->default('delivery'); // delivery, pickup
            $table->string('brand')->nullable();
            $table->string('store_id')->nullable();
            
            // Información del cliente
            $table->string('eater_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            
            // Información de entrega
            $table->text('delivery_address')->nullable();
            $table->text('delivery_instructions')->nullable();
            
            // Datos de la orden
            $table->json('order_data'); // Toda la data de Uber en JSON
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('CLP');
            
            // Timestamps de Uber
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('estimated_ready_for_pickup_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Integración con POS
            $table->boolean('processed_in_pos')->default(false);
            $table->string('pos_ticket_id')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['status']);
            $table->index(['store_id']);
            $table->index(['created_at']);
            $table->index(['placed_at']);
            $table->index(['processed_in_pos']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uber_eats_orders');
    }
}
