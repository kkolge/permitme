<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableIotdatasummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iotdatasummary', function (Blueprint $table) {
            $table->id();
            $table->string('device');
            $table->date('fordate');
            $table->integer('highpulserate');
            $table->integer('lowspo2');
            $table->integer('hightemp');
            $table->integer('highpulseratelowspo2');
            $table->integer('highpulseratehightemp');
            $table->integer('lowspo2hightemp');
            $table->integer('allabnormal');
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
        Schema::dropIfExists('iotdatasummary');
    }
}
