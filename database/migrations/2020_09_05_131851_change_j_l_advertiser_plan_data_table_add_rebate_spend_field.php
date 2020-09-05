<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeJLAdvertiserPlanDataTableAddRebateSpendField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('j_l_advertiser_plan_data', function (Blueprint $table) {
            $table->float('rebate_cost')->default(0);
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
            $table->dropColumn('rebate_cost');
        });
    }
}
