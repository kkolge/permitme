<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToLinkLocDev extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('LinkLocDev', function (Blueprint $table) {
            $table->string('name',150)->default('Main Door')->after('deviceid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('LinkLocDev', function (Blueprint $table) {
            $table->dropColumn('name',150);
        });
    }
}
