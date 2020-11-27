<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('noofresidents');
            $table->string('address1');
            $table->string('address2')->nullabel(true);
            $table->string('pincode',6);
            $table->string('city');
            $table->string('taluka')->nullable(true);
            $table->string('district')->nullable(true);
            $table->string('state');
            $table->boolean('isactive');
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
        Schema::dropIfExists('location');
    }
}
