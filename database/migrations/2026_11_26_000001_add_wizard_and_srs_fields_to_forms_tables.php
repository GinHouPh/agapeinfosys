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
        Schema::table('forms', function (Blueprint $table): void {
            $table->boolean('is_wizard')->default(false)->after('is_active');
            $table->json('wizard_settings')->nullable()->after('is_wizard');
        });

        Schema::table('form_fields', function (Blueprint $table): void {
            $table->string('section_id', 80)->nullable()->after('group');
            $table->boolean('has_text_input')->default(false)->after('options');
            $table->string('text_placeholder', 240)->nullable()->after('has_text_input');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->dropColumn(['section_id', 'has_text_input', 'text_placeholder']);
        });

        Schema::table('forms', function (Blueprint $table): void {
            $table->dropColumn(['is_wizard', 'wizard_settings']);
        });
    }
};
