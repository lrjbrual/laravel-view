<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClaimNameToOrderIdClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('order_id_claims', function (Blueprint $table) {
            $table->string('claim_name')->after('detailed_disposition');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('order_id_claims', function (Blueprint $table) {
            $table->dropColumn('claim_name'); 
        });
    }
}
