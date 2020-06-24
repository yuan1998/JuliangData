<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixAdvertiserPlanDataPlayDurationSumOutOfRange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('j_l_advertiser_plan_data', function (Blueprint $table) {
            $table->bigInteger('play_duration_sum')->change();
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
            $table->integer('play_duration_sum')->change();
        });
    }
}
