<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAttorneyIdInCaseActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('case_actions', function (Blueprint $table) {
            $table->renameColumn('attorney_id', 'created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->tinyInteger('inform')->default(0)->after('endDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('case_actions', function (Blueprint $table) {
            // Step 1: Remove the foreign key constraint
            $table->dropForeign(['created_by']);

            // Step 2: Rename the column back to its original name
            $table->renameColumn('created_by', 'attorney_id');
            $table->dropColumn('inform');
        });
    }
}
