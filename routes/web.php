<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\BusinessSettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\FormFieldController as AdminFormFieldController;
use App\Http\Controllers\Admin\FormSubmissionController as AdminFormSubmissionController;
use App\Http\Controllers\Admin\GatewayController as AdminGatewayController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageBuilderController as AdminPageBuilderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ShopOrderController as AdminShopOrderController;
use App\Http\Controllers\Admin\ShowcaseProjectController as AdminShowcaseProjectController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController as PageBuilderPageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Shop\CartController as ShopCartController;
use App\Http\Controllers\Shop\CheckoutController as ShopCheckoutController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\ShowcaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/portfolio', [ProjectController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{project:slug}', [ProjectController::class, 'show'])->name('portfolio.show');

/*
| Poster wall of ongoing & upcoming projects. Registered before the
| root-level {product} catch-all so the static paths are never shadowed.
*/
Route::get('/projects', [ShowcaseController::class, 'index'])->name('projects.index');

/*
| Builder-managed forms. /requirements is a stable alias of the seeded
| requirement specification form; every other form lives at /forms/{slug}.
*/
Route::get('/requirements', [FormController::class, 'showRequirement'])->name('requirements.create');
Route::post('/requirements', [FormController::class, 'storeRequirement'])
    ->middleware('throttle:6,1')
    ->name('requirements.store');
Route::get('/forms/{form:slug}', [FormController::class, 'show'])->name('forms.show');
Route::post('/forms/{form:slug}', [FormController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('forms.store');

Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');

/*
| Builder-managed pages. Registered before the root-level {product}
| catch-all so a landing page slug is never shadowed by a product.
*/
Route::get('/pages/{page:slug}', [PageBuilderPageController::class, 'show'])->name('pages.show');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

/*
|--------------------------------------------------------------------------
| Shop — browse the product catalog and check out a cart
|--------------------------------------------------------------------------
|
| Registered before the root-level {product} catch-all below so "/shop"
| is never swallowed by a product slug.
|
*/
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/cart', [ShopCartController::class, 'index'])->name('shop.cart.index');
Route::post('/shop/cart', [ShopCartController::class, 'store'])->name('shop.cart.store');
Route::delete('/shop/cart', [ShopCartController::class, 'destroy'])->name('shop.cart.destroy');
Route::get('/shop/checkout', [ShopCheckoutController::class, 'show'])->name('shop.checkout.show');
Route::post('/shop/checkout', [ShopCheckoutController::class, 'store'])->name('shop.checkout.store');
Route::get('/shop/orders/{order:reference}/pay', [ShopCheckoutController::class, 'pay'])
    ->name('shop.pay');
Route::match(['get', 'post'], '/shop/orders/{order:reference}', [ShopCheckoutController::class, 'result'])
    ->name('shop.result');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

Route::prefix('admin')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('admin.login.attempt');

    Route::middleware('admin')->group(function (): void {
        Route::post('/logout', [LoginController::class, 'destroy'])->name('admin.logout');

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::resource('services', AdminServiceController::class, ['as' => 'admin'])->except('show');
        Route::resource('projects', AdminProjectController::class, ['as' => 'admin'])->except('show');
        Route::resource('showcase-projects', AdminShowcaseProjectController::class, ['as' => 'admin'])->except('show');
        Route::resource('posts', AdminPostController::class, ['as' => 'admin'])->except('show');
        Route::resource('testimonials', AdminTestimonialController::class, ['as' => 'admin'])->except('show');

        Route::get('messages', [MessageController::class, 'index'])->name('admin.messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->name('admin.messages.show');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('admin.messages.destroy');

        /*
        | Dynamic form builder + submissions inbox
        */
        Route::resource('forms', AdminFormController::class, ['as' => 'admin'])->except('show');
        Route::post('forms/{form}/fields', [AdminFormFieldController::class, 'store'])->name('admin.forms.fields.store');
        Route::put('forms/{form}/fields/{field}', [AdminFormFieldController::class, 'update'])->name('admin.forms.fields.update');
        Route::delete('forms/{form}/fields/{field}', [AdminFormFieldController::class, 'destroy'])->name('admin.forms.fields.destroy');
        Route::get('submissions', [AdminFormSubmissionController::class, 'index'])->name('admin.submissions.index');
        Route::get('submissions/{submission}', [AdminFormSubmissionController::class, 'show'])->name('admin.submissions.show');
        Route::patch('submissions/{submission}/status', [AdminFormSubmissionController::class, 'status'])->name('admin.submissions.status');
        Route::delete('submissions/{submission}', [AdminFormSubmissionController::class, 'destroy'])->name('admin.submissions.destroy');

        Route::get('business', [BusinessSettingsController::class, 'edit'])->name('admin.business.edit');
        Route::put('business', [BusinessSettingsController::class, 'update'])->name('admin.business.update');

        /*
        | Visual page builder
        */
        Route::resource('pages', AdminPageController::class, ['as' => 'admin'])->except('show');
        Route::post('builder/preview', [AdminPageBuilderController::class, 'preview'])->name('admin.builder.preview');
        Route::post('builder/widget-schema', [AdminPageBuilderController::class, 'widgetSchema'])->name('admin.builder.widget-schema');
        Route::post('builder/upload', [AdminPageBuilderController::class, 'upload'])->name('admin.builder.upload');

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
        Route::get('shop-orders', [AdminShopOrderController::class, 'index'])->name('admin.shop-orders.index');
        Route::get('shop-orders/{order}', [AdminShopOrderController::class, 'show'])->name('admin.shop-orders.show');
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
