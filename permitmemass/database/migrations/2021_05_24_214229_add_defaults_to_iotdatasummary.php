<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultsToIotdatasummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('iotdatasummary', function (Blueprint $table) {
            $table->integer('highpulserate')->default(0)->change();
            $table->integer('lowspo2')->default(0)->change();
            $table->integer('hightemp')->default(0)->change();
            $table->integer('highpulseratelowspo2')->default(0)->change();
            $table->integer('highpulseratehightemp')->default(0)->change();
            $table->integer('lowspo2hightemp')->default(0)->change();
            $table->integer('allabnormal')->default(0)->change();
            $table->integer('allnormal')->default(0)->change();
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
            //
        });
    }
}
