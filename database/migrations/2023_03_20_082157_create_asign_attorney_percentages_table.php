<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsignAttorneyPercentagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asign_attorney_percentages', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('attorney_id');
            $table->uuid('subcategory_id');
            $table->double('admin_percentage');
            $table->uuid('admin_id');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('asign_attorney_percentages', function (Blueprint $table){
            $table->foreign('attorney_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('case_sub_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asign_attorney_percentages');
    }
}
