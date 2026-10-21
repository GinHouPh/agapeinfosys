<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('pages')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->after('parent_id')->constrained('page_templates')->nullOnDelete();
            $table->timestamp('scheduled_for')->nullable()->after('status');
            $table->unsignedInteger('sort_order')->default(0)->after('slug');
            $table->string('path', 300)->nullable()->after('sort_order');
        });

        // Existing pages are all top level, so their path is their slug.
        DB::table('pages')->update(['path' => DB::raw('slug')]);

        Schema::table('pages', function (Blueprint $table): void {
            $table->unique('path');
        });

        Schema::create('page_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->json('schema')->nullable();
            $table->string('note', 300)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['page_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_revisions');

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropUnique(['path']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropConstrainedForeignId('template_id');
            $table->dropColumn(['scheduled_for', 'sort_order', 'path']);
        });
    }
};
