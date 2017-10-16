<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventSAFETReimbursementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_s_a_f_e_t_reimbursements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->dateTime('posteddate');
            $table->string('safetclaimid')->nullable();
            $table->double('reimbursedamount_amount');
            $table->double('reimbursedamount_currencycode');
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
        Schema::connection('mysql2')->dropIfExists('financial_event_s_a_f_e_t_reimbursements');
    }
}
