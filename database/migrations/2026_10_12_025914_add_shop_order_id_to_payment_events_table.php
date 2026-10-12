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
        Schema::table('payment_events', function (Blueprint $table) {
            $table->foreignId('shop_order_id')
                ->nullable()
                ->after('order_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_order_id');
        });
    }
};
