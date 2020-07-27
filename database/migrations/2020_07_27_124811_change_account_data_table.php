<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAccountDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('j_l_accounts', function (Blueprint $table) {
            $table->dropColumn('refresh_token');
            $table->dropColumn('access_token');
            $table->dropColumn('refresh_token_expires_in');
            $table->dropColumn('expires_in');
            $table->dropColumn('status');
            $table->dropColumn('account_type');
            $table->dropColumn('hospital_type');
            $table->unsignedBigInteger('token_id')->index()->nullable();
            $table->integer('advertiser_role')->nullable();
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
            $table->string('refresh_token')->nullable();
            $table->string('access_token')->nullable();
            $table->string('account_type')->nullable();
            $table->string('hospital_type')->nullable();
            $table->datetime('refresh_token_expires_in')->nullable();
            $table->datetime('expires_in')->nullable();
            $table->string('status')->default('disable');
            $table->dropColumn('token_id');
            $table->dropColumn('advertiser_role');
        });
    }
}
