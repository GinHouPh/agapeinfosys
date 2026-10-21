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
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->json('validation')->nullable()->after('help_text');
            $table->json('condition')->nullable()->after('validation');
            $table->string('width', 20)->default('full')->after('condition');
            $table->json('file_settings')->nullable()->after('width');
        });

        Schema::table('form_submissions', function (Blueprint $table): void {
            $table->json('meta')->nullable()->after('answers');
            $table->string('page_url', 500)->nullable()->after('meta');
            $table->boolean('is_spam')->default(false)->after('page_url');
        });

        Schema::table('forms', function (Blueprint $table): void {
            $table->json('notification_settings')->nullable()->after('success_message');
            $table->unsignedSmallInteger('throttle_per_minute')->default(6)->after('notification_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table): void {
            $table->dropColumn(['notification_settings', 'throttle_per_minute']);
        });

        Schema::table('form_submissions', function (Blueprint $table): void {
            $table->dropColumn(['meta', 'page_url', 'is_spam']);
        });

        Schema::table('form_fields', function (Blueprint $table): void {
            $table->dropColumn(['validation', 'condition', 'width', 'file_settings']);
        });
    }
};
