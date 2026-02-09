<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->enum('objective', ['sales', 'traffic', 'leads', 'retargeting']);
            $table->enum('target_platform', ['tiktok', 'instagram', 'youtube', 'meta']);
            $table->string('video_format')->nullable(); // 9:16, 1:1, 16:9
            $table->integer('video_duration')->nullable(); // seconds
            $table->integer('daily_video_goal')->nullable();
            $table->string('call_to_action')->nullable();
            $table->string('landing_url')->nullable();
            $table->string('utm_template')->nullable();
            $table->text('disclaimers')->nullable();
            $table->json('forbidden_claims')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
