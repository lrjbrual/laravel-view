<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('campaign_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->string('template_name')->nullable();
            $table->integer('days_delay')->nullable();
            $table->integer('campaign_trigger_id')->nullable()->unsigned();
            $table->string('subject')->nullable();
            $table->text('email_body')->nullable();
            $table->boolean('is_active');
            $table->boolean('is_deleted');
            $table->boolean('exclude_blacklist');

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
        Schema::connection('mysql2')->dropIfExists('campaign_emails');
    }
}
