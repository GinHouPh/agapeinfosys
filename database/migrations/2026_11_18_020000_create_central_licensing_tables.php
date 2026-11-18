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
        // 1. Software Products Table
        Schema::create('software_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 80)->unique();
            $table->string('envato_item_id', 50)->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 2. Licenses Table
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('software_products')->cascadeOnDelete();
            $table->string('license_key', 100)->unique()->index();
            $table->enum('channel', ['envato', 'agape_direct', 'partner'])->default('agape_direct');
            $table->string('buyer_email')->nullable()->index();
            $table->string('buyer_username')->nullable();
            $table->enum('tier', ['regular', 'extended', 'developer', 'agency', 'enterprise'])->default('regular');
            $table->unsignedInteger('max_domains')->default(1);
            $table->timestamp('support_until')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['active', 'suspended', 'revoked', 'refunded'])->default('active');
            $table->string('client_secret_salt', 64);
            $table->timestamps();
        });

        // 3. Domain Bindings Table
        Schema::create('domain_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->string('domain', 191)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('installed_version', 30)->nullable();
            $table->enum('status', ['active', 'deactivated'])->default('active');
            $table->timestamp('activated_at')->useCurrent();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();

            $table->unique(['license_id', 'domain']);
        });

        // 4. Software Releases Table
        Schema::create('software_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('software_products')->cascadeOnDelete();
            $table->string('version', 30);
            $table->string('min_php_version', 10)->default('8.2.0');
            $table->longText('changelog')->nullable();
            $table->string('file_path', 255);
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('sha256_checksum', 64);
            $table->boolean('is_prerelease')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'version']);
        });

        // 5. License Audit Logs Table
        Schema::create('license_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();
            $table->string('event', 50)->index();
            $table->string('domain', 191)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('response_code')->default(200);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_audit_logs');
        Schema::dropIfExists('software_releases');
        Schema::dropIfExists('domain_bindings');
        Schema::dropIfExists('licenses');
        Schema::dropIfExists('software_products');
    }
};
