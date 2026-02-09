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
        Schema::create('creative_briefs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->text('main_message');
            $table->json('supporting_messages')->nullable();
            $table->json('hook_types')->nullable();
            $table->enum('script_style', ['story', 'problem-solution', 'testimonial', 'listicle'])->nullable();
            $table->string('creator_gender')->nullable();
            $table->string('creator_age_range')->nullable();
            $table->enum('creator_style', ['selfie', 'vlog', 'cinematic'])->nullable();
            $table->json('locations')->nullable();
            $table->enum('mood', ['fun', 'emotional', 'serious', 'excited'])->nullable();
            $table->text('product_usage_style')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creative_briefs');
    }
};
