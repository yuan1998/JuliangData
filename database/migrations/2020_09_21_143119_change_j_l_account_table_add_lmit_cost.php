<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeJLAccountTableAddLmitCost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('j_l_accounts', function (Blueprint $table) {
            $table->unsignedInteger('limit_cost')->default(0);
            $table->boolean('enable_robot')->default(true);
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
            $table->dropColumn('limit_cost');
            $table->dropColumn('enable_robot');
        });
    }
}
