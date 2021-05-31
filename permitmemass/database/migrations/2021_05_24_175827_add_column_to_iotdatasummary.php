<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToIotdatasummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('iotdatasummary', function (Blueprint $table) {
            $table->integer('allnormal')->after('allabnormal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('iotdatasummary', function (Blueprint $table) {
            $table->dropColumn('allnormal');
        });
    }
}
