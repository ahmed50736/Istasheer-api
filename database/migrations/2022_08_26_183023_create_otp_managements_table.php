<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otp_managements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('otp');
            $table->uuid('uid')->nullable();
            $table->integer('otp_type')->default(1);
            $table->string('phone_or_email')->nullable();
            $table->dateTime('create_time');
            $table->softDeletes();
        });
        Schema::table('otp_managements',function (Blueprint $table){
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otp_managements');
    }
}
