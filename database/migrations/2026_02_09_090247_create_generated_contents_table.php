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
        Schema::create('generated_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('queue_id');
            $table->foreign('queue_id')->references('id')->on('content_queues')->cascadeOnDelete();
            $table->text('script_text')->nullable();
            $table->text('hook_text')->nullable();
            $table->text('caption_text')->nullable();
            $table->string('cta_text')->nullable();
            $table->json('video_prompt_data')->nullable();
            $table->decimal('performance_score', 5, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_contents');
    }
};
