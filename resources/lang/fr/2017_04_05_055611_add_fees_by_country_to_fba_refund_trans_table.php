<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeesByCountryToFbaRefundTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('fba_refund_trans', function (Blueprint $table) {
            $table->double('fees_paid_us')->default(0)->nullable(); 
            $table->double('fees_paid_ca')->default(0)->nullable(); 
            $table->double('fees_paid_uk')->default(0)->nullable(); 
            $table->double('fees_paid_fr')->default(0)->nullable(); 
            $table->double('fees_paid_de')->default(0)->nullable(); 
            $table->double('fees_paid_es')->default(0)->nullable(); 
            $table->double('fees_paid_it')->default(0)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('fba_refund_trans', function (Blueprint $table) {
            $table->dropColumn('fees_paid_us');
            $table->dropColumn('fees_paid_ca');
            $table->dropColumn('fees_paid_uk');
            $table->dropColumn('fees_paid_fr');
            $table->dropColumn('fees_paid_de');
            $table->dropColumn('fees_paid_es');
            $table->dropColumn('fees_paid_it');
        });
    }
}
