<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id', false, true)->length(15);
            $table->text('sku')->nullable();
            $table->text('asin')->nullable();
            $table->text('country')->nullable();
            $table->text('product_name')->nullable();
            $table->float('price', 15, 2)->nullable();
            $table->integer('quantity', false, true)->length(15)->default(0);
            $table->float('ddp_cost', 15, 2)->nullable();
            $table->dateTime('date_created')->nullable();
            $table->boolean('star')->default(0);
            $table->boolean('isactive')->default(1);
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
        Schema::connection('mysql2')->dropIfExists('products');
    }
}
