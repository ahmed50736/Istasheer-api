<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->integer('user_type')->default(3);
            $table->integer('login_type')->default(1);
            $table->string('password')->nullable();
            $table->string('phone_no')->unique()->nullable();
            $table->integer('verified')->default(1);
            $table->string('social_id')->nullable();
            $table->string('social_email')->nullable();
            $table->dateTime('DOB')->nullable();
            $table->string('gender')->nullable();
            $table->float('balance')->default(0.00);
            $table->boolean('account_sts')->default(1);
            $table->text('notes')->nullable();
            $table->text('other_info')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
