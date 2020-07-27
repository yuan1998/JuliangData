<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAdvertiserPlanDataTableAddHospitalId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('j_l_advertiser_plan_data', function (Blueprint $table) {
            $table->unsignedBigInteger('hospital_id')->index()->nullable();
            $table->unsignedBigInteger('account_id')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('j_l_advertiser_plan_data', function (Blueprint $table) {
            $table->dropColumn('hospital_id');
            $table->dropColumn('account_id');
        });
    }
}
