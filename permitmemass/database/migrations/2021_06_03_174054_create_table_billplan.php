<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBillplan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billplan', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default("NO NAME")->unique();
            $table->string('description')->nullable(true);
            $table->integer('secdeposit')->default(0);
            $table->integer('rentpermonth')->default(0);
            $table->decimal('transactionrate')->default(1.0);
            $table->integer('hostingcharges')->default(0);
            $table->decimal('hardwareamcrate')->default(20);
            $table->decimal('softwareamcrate')->default(20);
            $table->integer('trainingcost')->default(0);
            $table->integer('installationandsetupcost')->default(0);
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
        Schema::dropIfExists('billplan');
    }
}
