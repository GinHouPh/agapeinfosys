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
        Schema::create('locales', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 80);
            $table->string('native_name', 80)->nullable();
            $table->string('direction', 10)->default('ltr');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locale_id')->constrained()->cascadeOnDelete();
            $table->string('translatable_type', 160);
            $table->unsignedBigInteger('translatable_id');
            $table->string('field', 80);
            $table->text('value');
            $table->timestamps();

            $table->unique(['locale_id', 'translatable_type', 'translatable_id', 'field'], 'translations_unique');
            $table->index(['translatable_type', 'translatable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('locales');
    }
};
