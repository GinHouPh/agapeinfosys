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
        Schema::create('showcase_projects', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 160);
            $table->string('slug', 180)->unique();
            $table->string('industry', 80);
            $table->string('status', 20);
            $table->string('tagline', 240)->nullable();
            $table->text('description')->nullable();
            $table->string('poster')->nullable();
            $table->json('stack')->nullable();
            $table->date('expected_launch')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'status']);
            $table->index(['is_active', 'industry']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showcase_projects');
    }
};
