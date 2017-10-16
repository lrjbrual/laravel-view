<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToFnskuClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->dropIfExists('fnsku_claims');
        Schema::connection('mysql2')->create('fnsku_claims', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('country_code')->nullable();
            $table->string('fnsku')->nullable();
            $table->integer('three')->default(0);
            $table->integer('four')->default(0);
            $table->integer('five')->default(0);
            $table->integer('d')->default(0);
            $table->integer('e')->default(0);
            $table->integer('f')->default(0);
            $table->integer('m')->default(0);
            $table->integer('n')->default(0);
            $table->integer('o')->default(0);
            $table->integer('p')->default(0);
            $table->integer('q')->default(0);
            $table->integer('summation')->default(0);
            $table->integer('units')->default(0);
            $table->double('average_value')->default(0);
            $table->double('total_owed')->default(0);
            $table->string('support_ticket')->nullable();
            $table->string('reimbursement_id1')->nullable();
            $table->string('reimbursement_id2')->nullable();
            $table->string('reimbursement_id3')->nullable();
            $table->double('total_amount_reimbursed')->default(0);
            $table->double('difference')->default(0);
            $table->string('currency')->nullable();
            $table->string('status')->nullable();
            $table->string('comments')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('fnsku_claims');
        Schema::connection('mysql2')->create('fnsku_claims', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('support_ticket');
            $table->string('reimbursement_id1');
            $table->string('reimbursement_id2');
            $table->string('reimbursement_id3');
            $table->double('total_amount_reimbursed');
            $table->double('difference');
            $table->string('currency');
            $table->string('status');
            $table->string('comments');
            $table->timestamps();
        });
    }
}
