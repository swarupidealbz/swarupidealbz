<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->integer('website_id')->nullable()->index();
            $table->integer('primary_topic_id')->nullable()->index();
            $table->tinyInteger('is_primary_topic');
            $table->longText('topic');
            $table->string('keyword')->nullable()->index();
            $table->longText('serp_similarity')->nullable();
            $table->string('search_volume')->nullable();
            $table->integer('keyword_length')->nullable();
            $table->string('keyword_type')->nullable();
            $table->string('status')->default('open');//approved, rejected, open
            $table->string('attr_bg_color')->nullable();
            $table->string('attr_image')->nullable();
            $table->string('attr_font_color')->nullable();
            $table->string('attr_text_placement')->nullable();
            $table->string('attr_tile_mode')->nullable();
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
        Schema::dropIfExists('topics');
    }
}
