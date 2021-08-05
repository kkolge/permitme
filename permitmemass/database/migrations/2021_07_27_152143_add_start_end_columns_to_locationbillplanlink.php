<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartEndColumnsToLocationbillplanlink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locationbillplanlink', function (Blueprint $table) {
            $table->date('planstartdate')->nullable();
            $table->date('planenddate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locationbillplanlink', function (Blueprint $table) {
            $table->dropColumn('planstartdate');
            $table->dropColumn('planenddate');
        });
    }
}
