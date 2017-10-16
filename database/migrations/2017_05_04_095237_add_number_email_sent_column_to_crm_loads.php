<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumberEmailSentColumnToCrmLoads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('crm_loads', function (Blueprint $table) {
            $table->integer('number_email_sent')->default(0); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('crm_loads', function (Blueprint $table) {
            $table->dropColumn('number_email_sent');
        });
    }
}
