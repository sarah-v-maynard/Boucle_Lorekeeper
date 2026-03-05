<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('art_tracker', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('staff_id')->nullable();
            $table->string('status', 96);
            $table->integer('character_id');
            $table->integer('gallery_id')->nullable();
            $table->string('image_url', 1024)->nullable();
            $table->string('url', 1024)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->string('data', 1024)->nullable();
            $table->string('data_temp', 1024)->nullable();
            $table->text('comments')->nullable();
            $table->text('staff_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('art_tracker');
    }
};
