<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname',100);
            $table->string('lastname',100);
            $table->string('email',100);
            $table->string('company',100);
            $table->string('address',100);
            $table->string('city',100);
            $table->string('state',100);
            $table->string('zipcode',100);
            $table->integer('country_id')->nullable();
            $table->string('phone',100);
            $table->boolean('is_deleted');
            $table->string('reason_for_leaving',100)->nullable();
            $table->string('email_for_crm',100);
            $table->string('emailpw_for_crm',100);
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
        Schema::dropIfExists('sellers');
    }
}
