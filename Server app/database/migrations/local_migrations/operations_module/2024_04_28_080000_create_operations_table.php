<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->nullable();
            $table->string('rut', 20)->nullable();
            $table->string('factura', 50)->nullable();
            $table->string('receptor', 50)->nullable();
            $table->text('observation')->nullable();
            $table->date('fecha');
            $table->string('company_name', 100)->nullable();
            $table->decimal('total', 16,2);
            $table->unsignedBigInteger('user');
            $table->unsignedInteger('operations_subcategories_id');
            $table->unsignedInteger('operations_categories_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('operations_subcategories_id')
                ->references('id')
                ->on('operations_subcategories')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
            $table->foreign('operations_categories_id')
                ->references('id')
                ->on('operations_categories')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
            $table->foreign('user')
                ->references('id')
                ->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operations');
    }
}