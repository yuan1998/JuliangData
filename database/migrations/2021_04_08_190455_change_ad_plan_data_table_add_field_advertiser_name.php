<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAdPlanDataTableAddFieldAdvertiserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('j_l_advertiser_plan_data', function (Blueprint $table) {
            $table->string('advertiser_name')->nullable();
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
            $table->dropColumn('advertiser_name');
        });
    }
}
