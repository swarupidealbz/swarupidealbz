<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSideMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('side_menu', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable()->index();
            $table->string('name');
            $table->text('access');
            $table->integer('created_by_id')->index();
            $table->integer('updated_by_id')->index();
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
        Schema::dropIfExists('side_menu');
    }
}
