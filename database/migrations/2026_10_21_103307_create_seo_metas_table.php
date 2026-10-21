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
        Schema::create('seo_metas', function (Blueprint $table): void {
            $table->id();
            $table->string('metaable_type', 160);
            $table->unsignedBigInteger('metaable_id');
            $table->string('meta_title', 180)->nullable();
            $table->string('meta_description', 400)->nullable();
            $table->string('meta_keywords', 255)->nullable();
            $table->string('og_title', 180)->nullable();
            $table->string('og_description', 400)->nullable();
            $table->string('og_image', 255)->nullable();
            $table->string('twitter_card', 40)->default('summary_large_image');
            $table->string('canonical_url', 255)->nullable();
            $table->boolean('noindex')->default(false);
            $table->string('sitemap_priority', 10)->default('0.7');
            $table->string('sitemap_changefreq', 20)->default('monthly');
            $table->timestamps();

            $table->unique(['metaable_type', 'metaable_id']);
            $table->index('noindex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
