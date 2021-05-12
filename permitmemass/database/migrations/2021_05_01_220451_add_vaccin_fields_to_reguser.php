<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVaccinFieldsToReguser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reguser', function (Blueprint $table) {
            $table->boolean('vaccinated')->default(false)->after('resilandmark');
            $table->date('firstvaccin')->nullable()->after('vaccinated');
            $table->date('secondvaccin')->nullable()->after('firstvaccin');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reguser', function (Blueprint $table) {
            $table->dropColumn('vaccinated','firstvaccin','secondvaccin');
        });
    }
}
