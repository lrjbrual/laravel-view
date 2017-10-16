<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuantityUnsellableToOrderIdClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('order_id_claims', function (Blueprint $table) {
            $table->string('return_reason');
            $table->integer('quantity_unsellable');
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
            $table->dropColumn('return_reason');
            $table->dropColumn('quantity_unsellable');
        });
    }
}
