<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCronMasterListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cron_master_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description')->nullable();
            $table->text('route')->nullable();
            $table->integer('sequence', false, true)->length(15)->default(0);
            $table->boolean('isrunning')->default(0);
            $table->boolean('iswaiting')->default(0);
            $table->integer('seller_id', false, true)->length(15)->default(0);
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
        Schema::dropIfExists('cron_master_lists');
    }
}
