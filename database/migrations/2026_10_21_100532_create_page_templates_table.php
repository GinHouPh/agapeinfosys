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
        Schema::create('page_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('description', 300)->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->json('schema')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_templates');
    }
};
