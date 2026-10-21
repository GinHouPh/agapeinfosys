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
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('description', 300)->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('group', 60)->index();
            $table->string('description', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();

            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('role_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();

            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type', 160)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('action', 60)->index();
            $table->string('description', 400)->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 400)->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
