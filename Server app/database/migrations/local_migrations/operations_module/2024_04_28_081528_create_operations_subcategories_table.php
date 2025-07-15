<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationsSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operations_subcategories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->nullable();
            $table->string('description', 250)->nullable();
            $table->unsignedInteger('operations_categories_id');
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('user');
            $table->softDeletes();
            $table->timestamps();
            $table->index('operations_categories_id', 'fk_operations_subcategories_operations_categories_idx');
            $table->foreign('operations_categories_id', 'fk_operations_subcategories_operations_categories')
                ->references('id')
                ->on('operations_categories')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
            $table->foreign('user')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operations_subcategory');
    }
}