<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('attribute_option_translations')) {
            Schema::create('attribute_option_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('attribute_option_id')->unsigned();
                $table->string('locale');
                $table->text('label')->nullable();

                $table->unique(['attribute_option_id', 'locale']);
                $table->foreign('attribute_option_id')->references('id')->on('attribute_options')->onDelete('cascade');
            });
        }
        // If table exists, skip - it's already set up
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_option_translations');
    }
};
