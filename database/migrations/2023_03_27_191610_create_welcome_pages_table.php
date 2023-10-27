<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWelcomePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('welcome_pages', function (Blueprint $table) {
            $table->uuid('id');
            $table->enum('lang',['ar','en']);
            $table->string('title');
            $table->longText('description');
            $table->enum('user_type',['admin','attorney','user']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('welcome_pages');
    }
}
