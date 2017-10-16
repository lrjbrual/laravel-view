<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrendleCheckersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trendle_checkers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('checker_name');
            $table->string('checker_status')->nullable();
            $table->date('checker_date')->nullable();
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
        Schema::dropIfExists('trendle_checkers');
    }
}
