<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('invoice_number')->nullable();
            $table->string('product_description')->nullable();
            $table->string('product_subscription')->nullable();
            $table->double('amount')->default(0);
            $table->double('vat')->default(0);
            $table->string('country_code')->nullable();
            $table->string('currency')->nullable();
            $table->string('promocode')->nullable();
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
        Schema::dropIfExists('billing_invoices');
    }
}
