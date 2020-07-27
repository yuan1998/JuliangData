<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('refresh_token')->nullable();
            $table->string('access_token')->nullable();
            $table->datetime('refresh_token_expires_in')->nullable();
            $table->datetime('expires_in')->nullable();
            $table->integer('status')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('token_list');
    }
}
