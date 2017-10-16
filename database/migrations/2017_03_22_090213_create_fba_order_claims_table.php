<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFbaOrderClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fba_order_claims', function (Blueprint $table) {
            $table->increments('id'); 
            $table->integer('seller_id');
            $table->string('order_id');
            $table->integer('qty_ordered');
            $table->integer('qty_refunded');
            $table->integer('qty_adjusted');
            $table->double('total_ordered');
            $table->double('total_refunded');
            $table->double('total_adjusted');
            $table->integer('qty_returned');
            $table->string('detailed_disposition');
            $table->dateTimeTz('return_date');
            $table->boolean('over_45days');
            $table->string('claim_type');
            $table->double('total_claim');
            $table->string('support_ticket');
            $table->string('reimbursement_id');
            $table->double('total_reimbursed');
            $table->double('difference');
            $table->string('status');
            $table->string('comments');
            $table->timestamps();
        });
    }

    /**
    Order ID: Display the Order ID in question. Only display Order IDs when the sum of “Total Refunded” and “Total Adjusted” is negative AND “Detailed Disposition” is either “Not Returned” OR “Damaged”
    Qty Ordered: Search the Settlement Report for this Order ID and see how many products were ordered
    Qty Refunded: Search the Settlement Report for this Order ID and see how many products were refunded
    Qty Adjusted: Search the Settlement Report for this Order ID and see how many products were adjusted
    Total Ordered: Search the Settlement Report for this Order ID and see how much (£$) was the order for
    Total Refunded: Search the Settlement Report for this Order ID and see how much (£$) was refunded
    Total Adjusted: Search the Settlement Report for this Order ID and see how much (£$) was adjusted
    Qty Returned: Search the returns report for this Order ID to see how many products were returned
    Detailed Disposition: Search Returns Report for this Order IDs detailed disposition
    Date of Return: Search Returns Report
    Over 45days? : Calculate if the Refund date is more than 45 days away from today’s date. Return yes/no answer.
    Partial or Full Claim: If Total Adjusted is 0 then “Full” if Total Adjusted is a number then “Partial”
    Amount to Claim: Sum of Total Refunded and Total Adjusted
    Support Ticket: This is an open field for the user to enter a single line of text
    Reimbursement ID: This is an open field for the user to enter a single line of text.
    Total amount reimbursed: Search the Reimbursement report for the Order IDs entered and sum these up
    Difference: Difference between the Total Amount Reimbursed and Amount to Claim
    Status: Drop down menu for the user to select (Open, Closed)
    Comments: A free text box for the user to enter comments. Timestamp and username. Use same method as for the Seller Reviews table
    */

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fba_order_claims');
    }
}
