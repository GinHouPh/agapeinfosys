<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Razorpay was dropped as a payment provider, so its gateway records would
 * otherwise reference a driver class that no longer exists.
 *
 * Orders and payment events keep their history: both foreign keys are
 * nullOnDelete, so they simply end up without a gateway reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_gateways')->where('driver', 'razorpay')->delete();
    }

    /**
     * The removed rows (and any credentials they carried) cannot be
     * restored; Razorpay would have to be re-added by a new driver migration.
     */
    public function down(): void {}
};
