<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GatewayController as AdminGatewayController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/portfolio', [ProjectController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{project:slug}', [ProjectController::class, 'show'])->name('portfolio.show');

Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

Route::prefix('admin')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('admin.login.attempt');

    Route::middleware('admin')->group(function (): void {
        Route::post('/logout', [LoginController::class, 'destroy'])->name('admin.logout');

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::resource('services', AdminServiceController::class, ['as' => 'admin'])->except('show');
        Route::resource('projects', AdminProjectController::class, ['as' => 'admin'])->except('show');
        Route::resource('posts', AdminPostController::class, ['as' => 'admin'])->except('show');
        Route::resource('testimonials', AdminTestimonialController::class, ['as' => 'admin'])->except('show');

        Route::get('messages', [MessageController::class, 'index'])->name('admin.messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->name('admin.messages.show');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('admin.messages.destroy');

        /*
        | Digital products, plans, gateways, orders & subscriptions
        */
        Route::resource('products', AdminProductController::class, ['as' => 'admin'])->except('show');
        Route::post('products/{product}/rotate', [AdminProductController::class, 'rotate'])->name('admin.products.rotate');
        Route::resource('plans', AdminPlanController::class, ['as' => 'admin'])->except('show');
        Route::get('gateways', [AdminGatewayController::class, 'index'])->name('admin.gateways.index');
        Route::get('gateways/{gateway}/edit', [AdminGatewayController::class, 'edit'])->name('admin.gateways.edit');
        Route::put('gateways/{gateway}', [AdminGatewayController::class, 'update'])->name('admin.gateways.update');
        Route::get('orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
        Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    });
});

/*
|--------------------------------------------------------------------------
| Product checkout
|--------------------------------------------------------------------------
|
| Registered last: the root-level {product} binding must never shadow the
| static pages above.
|
*/
Route::get('/{product:slug}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/{product:slug}', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/{product:slug}/pay/{order:reference}', [CheckoutController::class, 'pay'])
    ->name('checkout.pay');
Route::match(['get', 'post'], '/{product:slug}/orders/{order:reference}', [CheckoutController::class, 'result'])
    ->name('checkout.result');
