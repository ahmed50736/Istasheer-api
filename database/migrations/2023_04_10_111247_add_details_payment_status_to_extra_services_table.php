<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsPaymentStatusToExtraServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extra_services', function (Blueprint $table) {
            $table->longText('details')->nullable();
            $table->string('extra_order_no')->nullable();
            $table->enum('status',['paid','unpaid'])->default('unpaid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extra_services', function (Blueprint $table) {
            $table->dropColumn('details');
            $table->dropColumn('extra_order_no');
            $table->dropColumn('status');
        });
    }
}
