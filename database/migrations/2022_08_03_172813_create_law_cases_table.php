<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLawCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('law_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('uid');
            $table->string('order_no')->unique();
            $table->uuid('category_id');
            $table->uuid('subcategory_id')->nullable();
            $table->enum('case_type',['individual','company']);
            $table->string('client_name')->nullable();
            $table->text('purpouse')->nullable();
            $table->string('contract_term')->nullable();
            $table->string('contract_ammount')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->longText('document_details')->nullable();
            $table->string('capacity')->nullable();
            $table->string('against')->nullable();
            $table->string('capacity2')->nullable();
            $table->string('court_location')->nullable();
            $table->string('expert_location')->nullable();
            $table->string('chamber')->nullable();
            $table->string('room')->nullable();
            $table->text('automated_no')->nullable();
            $table->string('court_case_no')->nullable();
            $table->longText('details')->nullable();
            $table->integer('case_status')->default(0);
            $table->double('other_case_price')->nullable();
            $table->dateTime('create_time');
            $table->softDeletes();
        });
        Schema::table('law_cases',function (Blueprint $table){
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
        Schema::dropIfExists('law_cases');
    }
}
