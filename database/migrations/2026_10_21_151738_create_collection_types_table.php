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
        Schema::create('collection_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('description', 300)->nullable();
            $table->string('icon', 40)->nullable();
            $table->string('route_pattern', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_record_template')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('collection_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collection_type_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('label', 190);
            $table->string('type', 30)->default('text');
            $table->json('options')->nullable();
            $table->json('validation')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['collection_type_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_fields');
        Schema::dropIfExists('collection_types');
    }
};
