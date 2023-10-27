<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('case_id');
            $table->uuid('extra_service_id')->nullable();
            $table->string('invoice_id');
            $table->string('transaction_id');
            $table->enum('transection_status',['Paid', 'Failed', 'Expired', 'Refunded']);
            $table->string('transection_date');
            $table->string('gateway_refarence_id');
            $table->double('amount');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('payment_details', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('case_id')->references('id')->on('law_cases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_details');
    }
}
