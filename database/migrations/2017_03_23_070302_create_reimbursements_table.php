<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReimbursementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('reimbursements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id')->unsigned();
            $table->dateTime('approval_date')->nullable();
            $table->string('reimbursement_id')->nullable();
            $table->string('case_id')->nullable();
            $table->string('amazon_order_id')->nullable();
            $table->string('reason')->nullable();
            $table->text('sku')->nullable();
            $table->string('fnsku')->nullable();
            $table->string('asin')->nullable();
            $table->text('product_name')->nullable();
            $table->text('condition')->nullable();
            $table->string('currency_unit')->nullable();
            $table->float('amount_per_unit', 15, 2)->nullable();
            $table->double('amount_total')->nullable();
            $table->integer('quantity_reimbursed_cash', false, true)->length(15)->nullable();
            $table->integer('quantity_reimbursed_inventory', false, true)->length(15)->nullable();
            $table->integer('quantity_reimbursed_total')->nullable();
            $table->integer('original_reimbursement_id')->nullable();
            $table->string('original_reimbursement_type')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('reimbursements');
    }

}
