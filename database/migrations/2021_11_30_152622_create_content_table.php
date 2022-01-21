<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content', function (Blueprint $table) {
            $table->id();
            $table->integer('website_id')->nullable()->index();
            $table->integer('primary_topic_id')->nullable()->index();
            $table->integer('child_topic_id')->nullable()->index();
            $table->integer('user_id')->index();
            $table->text('title');
            $table->longText('comment');
            $table->string('content_type')->default('article');//outline, article
            $table->string('status')->default('open');//approved, rejected, open, work in progress
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
        Schema::dropIfExists('content');
    }
}
