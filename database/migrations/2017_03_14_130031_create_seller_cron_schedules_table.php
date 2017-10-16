<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellerCronSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_cron_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cron_id', false, true)->length(15)->nullable();
            $table->integer('seller_id', false, true)->length(15)->nullable();
            $table->text('minutes')->nullable();
            $table->text('hours')->nullable();
            $table->text('day_of_month')->nullable();
            $table->text('month')->nullable();
            $table->text('day_of_week')->nullable();
            $table->dateTime('date_created')->nullable();
            $table->boolean('isactive')->default(false);
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
        Schema::dropIfExists('seller_cron_schedules');
    }
}
