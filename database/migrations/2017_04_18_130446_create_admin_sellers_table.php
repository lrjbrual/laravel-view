<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('company_name')->nullable();
            $table->string('seller_email')->nullable();
            $table->string('country_code')->nullable();
            $table->string('central_login_email')->nullable();
            $table->string('central_login_password')->nullable();
            $table->string('support_cases')->nullable();
            $table->double('total_owed')->default(0)->nullable();
            $table->double('total_saved')->default(0)->nullable();            
            $table->double('total_collected')->default(0)->nullable();
            $table->double('total_owed_to_collect')->default(0)->nullable();
            $table->string('status')->nullable();
            $table->string('currency')->nullable();
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
        Schema::dropIfExists('admin_sellers');
    }
}
