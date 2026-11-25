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
            $table->string('redirect_url', 500)->nullable()->after('success_message');
        });

        Schema::table('form_fields', function (Blueprint $table): void {
            $table->string('default_value', 500)->nullable()->after('help_text');
            $table->string('css_class', 120)->nullable()->after('width');
            $table->string('condition_match', 10)->default('all')->after('condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->dropColumn(['default_value', 'css_class', 'condition_match']);
        });

        Schema::table('forms', function (Blueprint $table): void {
            $table->dropColumn(['redirect_url']);
        });
    }
};
