<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionOnTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->longText('description')->nullable()->after('topic');
            $table->string('topic_image_path')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('topic_image_path');
        });
    }
}
