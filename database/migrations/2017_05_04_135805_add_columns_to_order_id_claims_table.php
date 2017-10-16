<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToOrderIdClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->dropIfExists('order_id_claims');
        Schema::connection('mysql2')->create('order_id_claims', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('country_code')->nullable();
            $table->string('order_id')->nullable();
            $table->integer('quantity_ordered')->default(0);
            $table->integer('quantity_refunded')->default(0);
            $table->integer('quantity_adjusted')->default(0);
            $table->double('total_ordered')->default(0);
            $table->double('total_refunded')->default(0);
            $table->double('total_adjusted')->default(0);
            $table->integer('quantity_returned')->default(0);
            $table->dateTime('date_of_return')->nullable();
            $table->string('over_45days')->nullable();
            $table->string('claim_type')->nullable();
            $table->string('detailed_disposition')->nullable();
            $table->double('claim_amount')->default(0);
            $table->string('support_ticket')->nullable();
            $table->string('reimbursement_id1')->nullable();
            $table->string('reimbursement_id2')->nullable();
            $table->string('reimbursement_id3')->nullable();
            $table->double('total_amount_reimbursed')->default(0);
            $table->double('difference')->default(0);
            $table->string('currency')->nullable();
            $table->string('status')->nullable();
            $table->string('comments')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('order_id_claims');
        Schema::connection('mysql2')->create('order_id_claims', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('support_ticket');
            $table->string('reimbursement_id1');
            $table->string('reimbursement_id2');
            $table->string('reimbursement_id3');
            $table->double('total_amount_reimbursed');
            $table->double('difference');
            $table->string('currency');
            $table->string('status');
            $table->string('comments');
            $table->timestamps();
        });
    }
}
