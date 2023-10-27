<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('status')->default(0);
            $table->dateTime('submissionTime');
            $table->uuid('case_id');
            $table->text('note')->nullable();
            $table->uuid('attorney_id');
            $table->softDeletes();
        });
        Schema::table('upload_responses',function (Blueprint $table){
            $table->foreign('case_id')->references('id')->on('law_cases')->onDelete('cascade');
            $table->foreign('attorney_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upload_responses');
    }
}
