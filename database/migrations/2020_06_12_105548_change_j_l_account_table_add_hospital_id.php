<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeJLAccountTableAddHospitalId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('j_l_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('hospital_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('j_l_accounts', function (Blueprint $table) {
            $table->dropColumn('hospital_id');
        });
    }
}
