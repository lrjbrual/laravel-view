<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupportTicket2ToFnskuClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('fnsku_claims', function (Blueprint $table) { 
            $table->string('support_ticket2')->after('support_ticket');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('fnsku_claims', function (Blueprint $table) { 
            $table->dropColumn('support_ticket2');
        });
    }
}
