<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseIdToCaseresponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caseresponses', function (Blueprint $table) {
            $table->uuid('case_id')->after('case_no');
            $table->uuid('response_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caseresponses', function (Blueprint $table) {
            $table->dropColumn('case_id');
        });
    }
}
