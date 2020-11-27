<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkLocUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linklocusers', function (Blueprint $table) {
            $table->id();
            $table->biginteger('locationid');
            $table->biginteger('userid');
            $table->string('designation');
            $table->string('phoneno1');
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
        Schema::dropIfExists('linklocusers');
    }
}
