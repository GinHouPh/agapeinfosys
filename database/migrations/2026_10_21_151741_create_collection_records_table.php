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
        Schema::create('collection_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collection_type_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 180);
            $table->string('title', 190);
            $table->json('data')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['collection_type_id', 'slug']);
            $table->index(['is_published', 'collection_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_records');
    }
};
