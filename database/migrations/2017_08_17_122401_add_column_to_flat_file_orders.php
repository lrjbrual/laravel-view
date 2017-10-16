<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToFlatFileOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('flat_file_all_orders_by_dates', function (Blueprint $table) { 
            $table->string('licensee_name')->nullable(); 
            $table->string('license_number')->nullable(); 
            $table->string('license_state')->nullable();
            $table->string('license_expiration_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('flat_file_all_orders_by_dates', function (Blueprint $table) { 
            $table->dropColumn('licensee_name'); 
            $table->dropColumn('license_number'); 
            $table->dropColumn('license_state'); 
            $table->dropColumn('license_expiration_date');
        });
    }
}
