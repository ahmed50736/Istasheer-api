<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHearingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hearings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id');
            $table->uuid('attorney_id');
            $table->string('session_type');
            $table->boolean('informe')->default(0);
            $table->date('date');
            $table->time('time');
            $table->text('decission')->nullable();
            $table->text('note')->nullable();
            $table->integer('action')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('hearings',function (Blueprint $table){
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
        Schema::dropIfExists('hearings');
    }
}
