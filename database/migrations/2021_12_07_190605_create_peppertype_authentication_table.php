<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeppertypeAuthenticationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peppertype_authentication', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->longText('idToken');
            $table->longText('refreshToken');
            $table->longText('accessToken');
            $table->longText('response');
            $table->integer('user_id')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('peppertype_authentication');
    }
}
