<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeppertypeRequestDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peppertype_request_data', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('ideas_product_name')->nullable();
            $table->text('ideas_product_description')->nullable();
            $table->string('intro_product_name')->nullable();
            $table->text('intro_product_description')->nullable();
            $table->string('outline_product_name')->nullable();
            $table->text('outline_product_description')->nullable();
            $table->string('conclusion_product_name')->nullable();
            $table->text('conclusion_product_description')->nullable();
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
        Schema::dropIfExists('peppertype_request_data');
    }
}
