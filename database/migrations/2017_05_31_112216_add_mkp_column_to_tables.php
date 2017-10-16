<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMkpColumnToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('reimbursements', function (Blueprint $table) {            
            $table->string('mkp')->nullable();
        });
        Schema::connection('mysql2')->table('inventory_datas', function (Blueprint $table) {            
            $table->string('mkp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('reimbursements', function (Blueprint $table) {            
            $table->dropColumn('mkp');
        });
        Schema::connection('mysql2')->table('inventory_datas', function (Blueprint $table) {            
            $table->dropColumn('mkp');
        });
    }
}
