<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderIdClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('order_id_claims');
    }
}
