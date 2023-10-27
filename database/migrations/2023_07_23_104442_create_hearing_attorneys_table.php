<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHearingAttorneysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hearing_attorneys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hearing_id');
            $table->uuid('attorney_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('hearing_attorneys', function (Blueprint $table) {
            $table->foreign('hearing_id')->references('id')->on('hearings')->onDelete('cascade');
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
        Schema::dropIfExists('hearing_attorneys');
    }
}
