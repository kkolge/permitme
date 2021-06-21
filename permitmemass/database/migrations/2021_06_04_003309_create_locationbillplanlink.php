<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationbillplanlink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locationbillplanlink', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('locationid')->nullable(false);
            $table->unsignedBigInteger('planid')->nullable(false);
            $table->boolean('isactive')->defaule(false);
            $table->foreign('locationid')->references('id')->on('location');
            $table->foreign('planid')->references('id')->on('billplan');
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
        Schema::dropIfExists('locationbillplanlink');
    }
}
