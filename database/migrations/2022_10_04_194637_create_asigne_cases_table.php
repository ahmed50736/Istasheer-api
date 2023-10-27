<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsigneCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asigne_cases', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('case_id');
                $table->uuid('attorney_id');
                $table->dateTime('assign_time');
                $table->uuid('asigne_by');
                $table->integer('asigne_status')->default(0);
                $table->dateTime('deadline')->nullable();
                $table->dateTime('submit_time')->nullable();
                $table->dateTime('due_date')->nullable();
                $table->softDeletes();
            
        });

        Schema::table('asigne_cases',function (Blueprint $table){
            $table->foreign('case_id')->references('id')->on('law_cases')->onDelete('cascade');
            $table->foreign('attorney_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('asigne_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asigne_cases');
    }
}
