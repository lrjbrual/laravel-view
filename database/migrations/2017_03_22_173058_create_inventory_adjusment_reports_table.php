<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryAdjusmentReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('inventory_adjustment_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id')->unsigned();
            $table->dateTime('adjusted_date')->nullable();
            $table->text('transaction_item_id')->nullable();
            $table->string('fnsku')->nullable();
            $table->text('sku')->nullable();
            $table->text('product_name')->nullable();
            $table->text('fulfillment_center_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('reason')->nullable();
            $table->string('disposition')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('inventory_adjustment_reports');
    }
}
