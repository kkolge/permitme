<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reguser', function (Blueprint $table) {
            $table->id();
            $table->string('name',50)->nullable(false);
            $table->string('phoneno')->nullable(false);
            $table->string('coverimage')->default('noImage.jpg');
            $table->string('tagid')->nullable(false);
            $table->boolean('isactive')->default(false);
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
        Schema::dropIfExists('reguser');
    }
}
