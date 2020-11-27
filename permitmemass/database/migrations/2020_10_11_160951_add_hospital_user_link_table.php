<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHospitalUserLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linkHospitalBedUser', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('locationId')->nullable(false);
            $table->bigInteger('bedId')->nullable(false);
            $table->bigInteger('patientId')->nullable(false);
            $table->boolean('isactive')->defaule(false);
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
        //
    }
}
