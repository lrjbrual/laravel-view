<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSellerCronToMasterlist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cron_master_lists', function (Blueprint $table) {
            $table->boolean('is_seller_cron')->default(true);
        });
            
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cron_master_lists', function (Blueprint $table) {
            $table->dropColumn('is_seller_cron');
        });
    }
}
