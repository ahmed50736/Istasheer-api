<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reminders', function (Blueprint $table) {

            //droping feilds
            $table->dropColumn('order_no');
            $table->dropColumn('createTime');

            //adding feilds
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reminders', function (Blueprint $table) {

            //adding drropped feilds
            $table->string('order_no');
            $table->dateTime('createTime');

            //droping added feilds
            $table->dropColumn('status');
            $table->dropColumn('created_at');
        });
    }
}
