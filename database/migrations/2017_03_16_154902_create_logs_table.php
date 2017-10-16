<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id', false, true)->length(15)->nullable();
            $table->text('description')->nullable();
            $table->dateTime('date_sent')->nullable();
            $table->text('subject')->nullable();
            $table->text('api_used')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_sent')->nullable();
            $table->integer('record_fetched', false, true)->length(15)->nullable();
            $table->text('message')->nullable();
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
        Schema::dropIfExists('logs');
    }
}
