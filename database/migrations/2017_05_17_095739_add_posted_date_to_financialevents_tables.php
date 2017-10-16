<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostedDateToFinancialeventsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
    {
        Schema::connection('mysql2')->table('financial_event_performance_bond_refunds', function (Blueprint $table) {            
            $table->dateTime('posted_date')->nullable();
        });
        Schema::connection('mysql2')->table('financial_event_service_fees', function (Blueprint $table) {            
            $table->dateTime('posted_date')->nullable();
        });
        Schema::connection('mysql2')->table('financial_event_debt_recoveries', function (Blueprint $table) {            
            $table->dateTime('posted_date')->nullable();
        });
        Schema::connection('mysql2')->table('financial_event_loan_servicings', function (Blueprint $table) {            
            $table->dateTime('posted_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('financial_event_performance_bond_refunds', function (Blueprint $table) {            
            $table->dropColumn('posted_date');
        });
        Schema::connection('mysql2')->table('financial_event_service_fees', function (Blueprint $table) {            
            $table->dropColumn('posted_date');
        });
        Schema::connection('mysql2')->table('financial_event_debt_recoveries', function (Blueprint $table) {            
            $table->dropColumn('posted_date');
        });
        Schema::connection('mysql2')->table('financial_event_loan_servicings', function (Blueprint $table) {            
            $table->dropColumn('posted_date');
        });
    }
}
