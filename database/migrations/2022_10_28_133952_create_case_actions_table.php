<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id');
            $table->string('actionType');
            $table->integer('actionStatus')->default(1);
            $table->uuid('attorney_id');
            $table->string('importance');
            $table->date('startDate');
            $table->date('endDate');
            $table->dateTime('createTime');
            $table->softDeletes();
        });
        Schema::table('case_actions', function (Blueprint $table) {
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
        Schema::dropIfExists('case_actions');
    }
}
