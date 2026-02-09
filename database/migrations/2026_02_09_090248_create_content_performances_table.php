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
        Schema::create('content_performances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('content_id');
            $table->foreign('content_id')->references('id')->on('generated_contents')->cascadeOnDelete();
            $table->bigInteger('views')->default(0);
            $table->integer('watch_time')->nullable(); // seconds
            $table->decimal('completion_rate', 5, 2)->nullable(); // percentage
            $table->integer('clicks')->default(0);
            $table->decimal('ctr', 5, 2)->nullable(); // click-through rate percentage
            $table->integer('conversions')->default(0);
            $table->decimal('cpa', 10, 2)->nullable(); // cost per acquisition
            $table->decimal('roas', 10, 2)->nullable(); // return on ad spend
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_performances');
    }
};
