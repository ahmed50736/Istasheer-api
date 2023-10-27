<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transection_id');
            $table->uuid('uid');
            $table->float('ammount');
            $table->uuid('case_id');
            $table->dateTime('purchase_time');
            $table->softDeletes();
            
        });
    }

   
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
