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
        Schema::create('influencers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id')->unique();
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->string('name'); // Ad Soyad
            $table->string('social_media_name'); // Sosyal Medya Adı
            $table->text('face_description'); // Yüz Kimliği Tanımı
            $table->text('body_description'); // Fizik Kimliği Tanımı
            $table->text('character_description'); // Karakter Tanımı
            $table->string('country_ethnicity'); // Yaşadığı Ülke / Etnik Köken
            $table->string('storage_folder'); // Resim klasörü (storage/app/influencers/{id})
            $table->json('face_images')->nullable(); // Yüz resimleri (dosya adları)
            $table->json('body_images')->nullable(); // Fizik resimleri (dosya adları)
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencers');
    }
};
