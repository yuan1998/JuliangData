<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJLAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('j_l_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('advertiser_id');
            $table->string('advertiser_name')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('access_token')->nullable();
            $table->datetime('refresh_token_expires_in')->nullable();
            $table->datetime('expires_in')->nullable();
            $table->string('status')->default('disable');
            $table->string('account_type')->nullable();
            $table->float('rebate')->default(0);

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
        Schema::dropIfExists('j_l_accounts');
    }
}
