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
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_gateway_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('direction')->default('inbound');
            $table->text('payload');
            $table->string('signature')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_processed')->default(false);
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'direction']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
