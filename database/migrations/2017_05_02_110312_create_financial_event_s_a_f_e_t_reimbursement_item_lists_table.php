<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventSAFETReimbursementItemListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_s_a_f_e_t_reimbursement_item_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('financial_event_s_a_f_e_t_reimbursements_id');
            $table->string('chargetype')->nullable();
            $table->string('currencycode')->nullable();
            $table->double('amount');
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
        Schema::connection('mysql2')->dropIfExists('financial_event_s_a_f_e_t_reimbursement_item_lists');
    }
}
