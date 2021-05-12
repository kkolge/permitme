<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToReguser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reguser', function (Blueprint $table) {
            $table->string('resiarea',100)->default('NOT KNOWN')->after('tagid');
            $table->string('resilandmark',150)->default('NOT KNOWN')->after('resiarea');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reguser', function (Blueprint $table) {
            $table->dropColumn('resiarea','resilandmark');
        });
    }
}
