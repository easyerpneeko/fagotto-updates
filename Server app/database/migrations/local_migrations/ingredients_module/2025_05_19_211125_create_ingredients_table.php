<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT
            $table->string('name', 80); // VARCHAR(80) NOT NULL
            $table->string('image', 255)->nullable(); // VARCHAR(255) NULL DEFAULT NULL
            $table->decimal('stock_quantity', 10, 3)->nullable(); // DECIMAL(10,3) NULL DEFAULT NULL
            $table->decimal('quantity_per_unit', 10, 3)->nullable(); // DECIMAL(10,3) NULL DEFAULT NULL
            $table->string('unit_of_measurement', 50)->nullable(); // VARCHAR(50) NULL DEFAULT NULL
            $table->integer('min_quantity')->nullable(); // INT(11) NULL DEFAULT NULL
            $table->integer('category_id')->nullable(); // INT(11) NULL DEFAULT NULL (Consider adding a foreign key constraint if 'categories' table exists)
            $table->timestamps(); // created_at TIMESTAMP NULL DEFAULT NULL, updated_at TIMESTAMP NULL DEFAULT NULL
            $table->softDeletes(); // deleted_at TIMESTAMP NULL DEFAULT NULL
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingredients');
    }
}