<?php

use App\Http\Controllers\Api\CheckoutSessionController;
use App\Http\Controllers\Api\EntitlementController;
use App\Http\Controllers\Api\Licensing\LicenseApiController;
use App\Http\Controllers\Api\Licensing\LicenseWebhookController;
use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', fn (Request $request) => $request->user())->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Payment provider webhooks
|--------------------------------------------------------------------------
|
| CSRF-exempt by virtue of living in the api group. Signature checks plus a
| provider status confirmation gate every completion.
|
*/
Route::post('/webhooks/{driver}', PaymentWebhookController::class)
    ->name('checkout.webhook');

/*
|--------------------------------------------------------------------------
| Product entitlement API
|--------------------------------------------------------------------------
|
| Consumed by tongdon.com, smt.com, motionmix.tv … Each product signs
| requests with its own api key/secret pair.
|
*/
Route::prefix('v1')->middleware('product.api')->group(function (): void {
    Route::get('/entitlement', [EntitlementController::class, 'show'])
        ->name('api.entitlement.show');

    Route::post('/orders/lookup', [EntitlementController::class, 'lookup'])
        ->name('api.orders.lookup');

    Route::get('/orders/{reference}', [EntitlementController::class, 'order'])
        ->name('api.orders.show');
});

/*
|--------------------------------------------------------------------------
| Mobile / Client SDK Checkout & Entitlement API
|--------------------------------------------------------------------------
|
| Used by client applications (e.g. SMT mobile app) via X-Agape-Key or product slug.
|
*/
Route::prefix('v1')->group(function (): void {
    Route::post('/checkout/session', CheckoutSessionController::class.'@store')
        ->name('api.checkout.session');

    Route::get('/entitlement/status', [EntitlementController::class, 'status'])
        ->name('api.entitlement.status');
});

/*
|--------------------------------------------------------------------------
| Central Licensing Server API
|--------------------------------------------------------------------------
|
| Dual-channel licensing engine for CodeCanyon (Envato) and Agape Direct sales.
|
*/
Route::prefix('v1/license')->middleware(['throttle:30,1'])->group(function (): void {
    // Client Verification & Heartbeat
    Route::post('/verify', [LicenseApiController::class, 'verify'])
        ->name('api.license.verify');
    Route::post('/heartbeat', [LicenseApiController::class, 'heartbeat'])
        ->name('api.license.heartbeat');
    Route::post('/deactivate', [LicenseApiController::class, 'deactivate'])
        ->name('api.license.deactivate');

    // Software Releases & Updates
    Route::get('/check-update', [LicenseApiController::class, 'checkUpdate'])
        ->name('api.license.check-update');
    Route::get('/download-update', [LicenseApiController::class, 'downloadUpdate'])
        ->name('api.license.download');

    // Ingestion Webhooks
    Route::post('/webhooks/cashfree', [LicenseWebhookController::class, 'handleCashfreeOrder'])
        ->name('api.license.webhooks.cashfree');
    Route::post('/webhooks/envato', [LicenseWebhookController::class, 'handleEnvatoSale'])
        ->name('api.license.webhooks.envato');
});
