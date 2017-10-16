<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnsReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('returns_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id')->unsigned();
            $table->string('order_id')->nullable();
            $table->dateTime('return_date');
            $table->string('sku')->nullable();
            $table->string('asin')->nullable();
            $table->string('fnsku')->nullable();
            $table->string('product_name')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('fulfillment_center_id')->nullable();
            $table->string('detailed_disposition')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('returns_reports');
    }
}
