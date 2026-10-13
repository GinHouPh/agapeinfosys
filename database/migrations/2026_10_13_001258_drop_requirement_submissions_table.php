<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. The static requirement table is superseded by
     * the dynamic forms tables; dev data only, nothing to preserve.
     */
    public function up(): void
    {
        Schema::dropIfExists('requirement_submissions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('requirement_submissions', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 24)->unique();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('phone', 40)->nullable();
            $table->string('company', 120)->nullable();
            $table->string('industry', 80);
            $table->string('project_name', 160);
            $table->text('brief');
            $table->json('solutions')->nullable();
            $table->text('features');
            $table->json('integrations')->nullable();
            $table->string('user_scale', 60);
            $table->string('budget', 60);
            $table->string('timeline', 60);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamps();

            $table->index('status');
        });
    }
};
