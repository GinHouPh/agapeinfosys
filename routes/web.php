<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BusinessSettingsController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CollectionController as AdminCollectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DesignController as AdminDesignController;
use App\Http\Controllers\Admin\FeatureFlagController as AdminFeatureFlagController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\FormFieldController as AdminFormFieldController;
use App\Http\Controllers\Admin\FormSubmissionController as AdminFormSubmissionController;
use App\Http\Controllers\Admin\GatewayController as AdminGatewayController;
use App\Http\Controllers\Admin\LocaleController as AdminLocaleController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageBuilderController as AdminPageBuilderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PageDesignController as AdminPageDesignController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\RedirectController as AdminRedirectController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoController as AdminSeoController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ShopOrderController as AdminShopOrderController;
use App\Http\Controllers\Admin\ShowcaseProjectController as AdminShowcaseProjectController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\SystemController as AdminSystemController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\TrafficController as AdminTrafficController;
use App\Http\Controllers\Admin\TranslationController as AdminTranslationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController as ClientLoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController as ClientRegisterController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController as PublicCollectionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Dashboard\DashboardController as ClientDashboardController;
use App\Http\Controllers\Dashboard\EntitlementController as DashboardEntitlementController;
use App\Http\Controllers\Dashboard\OrderController as DashboardOrderController;
use App\Http\Controllers\Dashboard\ProfileController as DashboardProfileController;
use App\Http\Controllers\Dashboard\SubscriptionController as DashboardSubscriptionController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LocaleController as PublicLocaleController;
use App\Http\Controllers\PageController as PageBuilderPageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Shop\CartController as ShopCartController;
use App\Http\Controllers\Shop\CheckoutController as ShopCheckoutController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\SitemapController;
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
| Site search across published services, case studies, articles and
| products. Registered before the root-level {product} catch-all.
*/
Route::get('/search', [SearchController::class, 'index'])->name('search.index');

/*
| The sitemap and the robots policy. The static public/robots.txt was
| removed so these routes are what a crawler actually receives.
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.xml');
Route::get('/sitemap_news.xml', [SitemapController::class, 'news'])->name('sitemap.news');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots.txt');

/*
| The language switcher. A visitor picks a language; the choice is kept in
| a cookie and the site is served in it from then on.
*/
Route::get('/locale/{code}', [PublicLocaleController::class, 'choose'])->name('locale.choose');

/*
| Builder-managed pages. Registered before the root-level {product}
| catch-all so a landing page slug is never shadowed by a product. A
| nested page's path carries its ancestors: /pages/about/team.
*/
Route::get('/pages/{page:path}', [PageBuilderPageController::class, 'show'])
    ->where('page', '[A-Za-z0-9\-_\/]+')
    ->name('pages.show');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

/*
| Legal pages. Each one is editable in the page builder under its reserved
| slug and falls back to the hand-built policy view until published.
*/
Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms-and-conditions', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/cancellation-and-refunds', [LegalController::class, 'refunds'])->name('legal.refunds');
Route::get('/delivery-policy', [LegalController::class, 'delivery'])->name('legal.delivery');

/*
|--------------------------------------------------------------------------
| Client accounts — sign in, register, manage purchases
|--------------------------------------------------------------------------
|
| Clients are non-admin users. Orders are recorded against the email used
| at checkout, so an account sees every purchase made with its address.
|
*/
Route::get('/login', [ClientLoginController::class, 'create'])->name('login');
Route::post('/login', [ClientLoginController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('login.attempt');
Route::get('/register', [ClientRegisterController::class, 'create'])->name('register');
Route::post('/register', [ClientRegisterController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('register.store');

/*
| Password reset for client accounts. The reset link is emailed by the
| password broker and consumed on the reset route.
*/
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('password.update');

Route::prefix('dashboard')->middleware('auth')->group(function (): void {
    Route::post('/logout', [ClientLoginController::class, 'destroy'])->name('logout');

    Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/orders', [DashboardOrderController::class, 'index'])->name('dashboard.orders.index');
    Route::get('/orders/{order}', [DashboardOrderController::class, 'show'])->name('dashboard.orders.show');

    Route::get('/subscriptions', [DashboardSubscriptionController::class, 'index'])->name('dashboard.subscriptions.index');
    Route::post('/subscriptions/{subscription}/cancel', [DashboardSubscriptionController::class, 'cancel'])
        ->name('dashboard.subscriptions.cancel');

    Route::get('/access', [DashboardEntitlementController::class, 'index'])->name('dashboard.access.index');

    Route::get('/profile', [DashboardProfileController::class, 'edit'])->name('dashboard.profile.edit');
    Route::put('/profile', [DashboardProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::put('/password', [DashboardProfileController::class, 'updatePassword'])->name('dashboard.password.update');
});

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
Route::get('/shop/category/{category:slug}', [ShopController::class, 'category'])->name('shop.category');
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

        Route::get('/', [DashboardController::class, 'index'])
            ->middleware('can:dashboard.view')
            ->name('admin.dashboard');

        Route::get('traffic', [AdminTrafficController::class, 'index'])
            ->middleware('can:dashboard.view')
            ->name('admin.traffic.index');

        Route::get('reports', [AdminReportController::class, 'index'])
            ->middleware('can:dashboard.view')
            ->name('admin.reports.index');
        Route::get('reports/export', [AdminReportController::class, 'export'])
            ->middleware('can:dashboard.view')
            ->name('admin.reports.export');

        Route::resource('services', AdminServiceController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:services.manage');
        Route::resource('projects', AdminProjectController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:projects.manage');
        Route::resource('showcase-projects', AdminShowcaseProjectController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:showcase.manage');
        Route::resource('posts', AdminPostController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:posts.manage');
        Route::resource('testimonials', AdminTestimonialController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:testimonials.manage');

        /*
        | Client/user management — the accounts behind the client dashboard
        */
        Route::resource('users', AdminUserController::class, ['as' => 'admin'])
            ->middleware('can:users.manage');

        Route::get('messages', [MessageController::class, 'index'])->middleware('can:messages.manage')->name('admin.messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->middleware('can:messages.manage')->name('admin.messages.show');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->middleware('can:messages.manage')->name('admin.messages.destroy');

        /*
        | Dynamic form builder + submissions inbox
        */
        Route::resource('forms', AdminFormController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:forms.manage');
        Route::post('forms/{form}/fields', [AdminFormFieldController::class, 'store'])->middleware('can:forms.manage')->name('admin.forms.fields.store');
        Route::put('forms/{form}/fields/{field}', [AdminFormFieldController::class, 'update'])->middleware('can:forms.manage')->name('admin.forms.fields.update');
        Route::delete('forms/{form}/fields/{field}', [AdminFormFieldController::class, 'destroy'])->middleware('can:forms.manage')->name('admin.forms.fields.destroy');
        Route::get('submissions', [AdminFormSubmissionController::class, 'index'])->middleware('can:submissions.manage')->name('admin.submissions.index');
        Route::get('submissions/export', [AdminFormSubmissionController::class, 'export'])->middleware('can:submissions.manage')->name('admin.submissions.export');
        Route::get('submissions/{submission}', [AdminFormSubmissionController::class, 'show'])->middleware('can:submissions.manage')->name('admin.submissions.show');
        Route::patch('submissions/{submission}/status', [AdminFormSubmissionController::class, 'status'])->middleware('can:submissions.manage')->name('admin.submissions.status');
        Route::delete('submissions/{submission}', [AdminFormSubmissionController::class, 'destroy'])->middleware('can:submissions.manage')->name('admin.submissions.destroy');

        Route::get('business', [BusinessSettingsController::class, 'edit'])->middleware('can:business.manage')->name('admin.business.edit');
        Route::put('business', [BusinessSettingsController::class, 'update'])->middleware('can:business.manage')->name('admin.business.update');

        /*
        | Navigation management — menus, items and their order
        */
        Route::get('navigation', [NavigationController::class, 'index'])->middleware('can:navigation.manage')->name('admin.navigation.index');
        Route::post('navigation', [NavigationController::class, 'store'])->middleware('can:navigation.manage')->name('admin.navigation.store');
        Route::get('navigation/{menu}/edit', [NavigationController::class, 'edit'])->middleware('can:navigation.manage')->name('admin.navigation.edit');
        Route::put('navigation/{menu}', [NavigationController::class, 'update'])->middleware('can:navigation.manage')->name('admin.navigation.update');
        Route::delete('navigation/{menu}', [NavigationController::class, 'destroy'])->middleware('can:navigation.manage')->name('admin.navigation.destroy');
        Route::post('navigation/{menu}/items', [MenuItemController::class, 'store'])->middleware('can:navigation.manage')->name('admin.navigation.items.store');
        Route::put('navigation/{menu}/items/{item}', [MenuItemController::class, 'update'])->middleware('can:navigation.manage')->name('admin.navigation.items.update');
        Route::delete('navigation/{menu}/items/{item}', [MenuItemController::class, 'destroy'])->middleware('can:navigation.manage')->name('admin.navigation.items.destroy');

        /*
        | Templates and reusable components
        */
        Route::get('templates', [AdminTemplateController::class, 'index'])->middleware('can:templates.manage')->name('admin.templates.index');
        Route::post('templates/components', [AdminTemplateController::class, 'storeComponent'])->middleware('can:templates.manage')->name('admin.templates.components.store');
        Route::delete('templates/components/{component}', [AdminTemplateController::class, 'destroyComponent'])->middleware('can:templates.manage')->name('admin.templates.components.destroy');
        Route::post('templates/pages', [AdminTemplateController::class, 'storePageTemplate'])->middleware('can:templates.manage')->name('admin.templates.pages.store');
        Route::delete('templates/pages/{template}', [AdminTemplateController::class, 'destroyPageTemplate'])->middleware('can:templates.manage')->name('admin.templates.pages.destroy');

        // Read by the builder canvas, so it follows the page permission.
        Route::get('templates/components', [AdminTemplateController::class, 'components'])->middleware('can:pages.manage')->name('admin.templates.components');
        Route::post('templates/components/{component}/insert', [AdminTemplateController::class, 'insert'])->middleware('can:pages.manage')->name('admin.templates.components.insert');

        Route::get('media', [AdminMediaController::class, 'index'])->middleware('can:media.manage')->name('admin.media.index');
        Route::get('media/library', [AdminMediaController::class, 'library'])->middleware('can:media.manage')->name('admin.media.library');
        Route::post('media', [AdminMediaController::class, 'store'])->middleware('can:media.manage')->name('admin.media.store');
        Route::put('media/{media}', [AdminMediaController::class, 'update'])->middleware('can:media.manage')->name('admin.media.update');
        Route::delete('media/{media}', [AdminMediaController::class, 'destroy'])->middleware('can:media.manage')->name('admin.media.destroy');

        /*
        | Design system — themes, tokens, fonts and surfaces
        */
        Route::get('design', [AdminDesignController::class, 'index'])->middleware('can:design.manage')->name('admin.design.index');
        Route::post('design', [AdminDesignController::class, 'store'])->middleware('can:design.manage')->name('admin.design.store');
        Route::get('design/{theme}/edit', [AdminDesignController::class, 'edit'])->middleware('can:design.manage')->name('admin.design.edit');
        Route::put('design/{theme}', [AdminDesignController::class, 'update'])->middleware('can:design.manage')->name('admin.design.update');
        Route::delete('design/{theme}', [AdminDesignController::class, 'destroy'])->middleware('can:design.manage')->name('admin.design.destroy');
        Route::post('design/{theme}/activate', [AdminDesignController::class, 'activate'])->middleware('can:design.manage')->name('admin.design.activate');

        /*
        | Collections — content types an author defines
        */
        Route::get('collections', [AdminCollectionController::class, 'index'])->middleware('can:collections.manage')->name('admin.collections.index');
        Route::post('collections', [AdminCollectionController::class, 'store'])->middleware('can:collections.manage')->name('admin.collections.store');
        Route::put('collections/{type}', [AdminCollectionController::class, 'update'])->middleware('can:collections.manage')->name('admin.collections.update');
        Route::delete('collections/{type}', [AdminCollectionController::class, 'destroy'])->middleware('can:collections.manage')->name('admin.collections.destroy');
        Route::post('collections/{type}/fields', [AdminCollectionController::class, 'storeField'])->middleware('can:collections.manage')->name('admin.collections.fields.store');
        Route::put('collections/{type}/fields/{field}', [AdminCollectionController::class, 'updateField'])->middleware('can:collections.manage')->name('admin.collections.fields.update');
        Route::delete('collections/{type}/fields/{field}', [AdminCollectionController::class, 'destroyField'])->middleware('can:collections.manage')->name('admin.collections.fields.destroy');
        Route::get('collections/{type}/records', [AdminCollectionController::class, 'records'])->middleware('can:collections.manage')->name('admin.collections.records');
        Route::post('collections/{type}/records', [AdminCollectionController::class, 'storeRecord'])->middleware('can:collections.manage')->name('admin.collections.records.store');
        Route::put('collections/{type}/records/{record}', [AdminCollectionController::class, 'updateRecord'])->middleware('can:collections.manage')->name('admin.collections.records.update');
        Route::delete('collections/{type}/records/{record}', [AdminCollectionController::class, 'destroyRecord'])->middleware('can:collections.manage')->name('admin.collections.records.destroy');

        /*
        | Languages and translations
        */
        Route::get('locales', [AdminLocaleController::class, 'index'])->middleware('can:locales.manage')->name('admin.locales.index');
        Route::post('locales', [AdminLocaleController::class, 'store'])->middleware('can:locales.manage')->name('admin.locales.store');
        Route::post('locales/{locale}/activate', [AdminLocaleController::class, 'activate'])->middleware('can:locales.manage')->name('admin.locales.activate');
        Route::delete('locales/{locale}', [AdminLocaleController::class, 'destroy'])->middleware('can:locales.manage')->name('admin.locales.destroy');
        Route::get('locales/{type}/{id}/{locale}', [AdminTranslationController::class, 'edit'])->middleware('can:locales.manage')->name('admin.locales.edit');
        Route::put('locales/{type}/{id}/{locale}', [AdminTranslationController::class, 'update'])->middleware('can:locales.manage')->name('admin.locales.translations.update');

        /*
        | Search metadata and redirect management
        */
        Route::get('seo/{type}/{id}/edit', [AdminSeoController::class, 'edit'])->middleware('can:seo.manage')->name('admin.seo.edit');
        Route::put('seo/{type}/{id}', [AdminSeoController::class, 'update'])->middleware('can:seo.manage')->name('admin.seo.update');
        Route::get('seo/redirects', [AdminRedirectController::class, 'index'])->middleware('can:seo.manage')->name('admin.seo.redirects');
        Route::post('seo/redirects', [AdminRedirectController::class, 'store'])->middleware('can:seo.manage')->name('admin.seo.redirects.store');
        Route::delete('seo/redirects/{redirect}', [AdminRedirectController::class, 'destroy'])->middleware('can:seo.manage')->name('admin.seo.redirects.destroy');

        /*
        | Platform administration — health, flags and maintenance
        */
        Route::get('system', [AdminSystemController::class, 'index'])->middleware('can:system.manage')->name('admin.system.index');
        Route::put('system/maintenance', [AdminSystemController::class, 'maintenance'])->middleware('can:system.manage')->name('admin.system.maintenance');
        Route::get('system/flags', [AdminFeatureFlagController::class, 'index'])->middleware('can:system.manage')->name('admin.system.flags');
        Route::post('system/flags', [AdminFeatureFlagController::class, 'store'])->middleware('can:system.manage')->name('admin.system.flags.store');
        Route::put('system/flags/{flag}', [AdminFeatureFlagController::class, 'update'])->middleware('can:system.manage')->name('admin.system.flags.update');
        Route::delete('system/flags/{flag}', [AdminFeatureFlagController::class, 'destroy'])->middleware('can:system.manage')->name('admin.system.flags.destroy');

        /*
        | Roles, permissions and the audit trail
        */
        Route::get('roles', [RoleController::class, 'index'])->middleware('can:roles.manage')->name('admin.roles.index');
        Route::post('roles', [RoleController::class, 'store'])->middleware('can:roles.manage')->name('admin.roles.store');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->middleware('can:roles.manage')->name('admin.roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('can:roles.manage')->name('admin.roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('can:roles.manage')->name('admin.roles.destroy');
        Route::get('activity', [ActivityLogController::class, 'index'])->middleware('can:activity.view')->name('admin.activity.index');

        /*
        | The administrator's own account — details, password, and deletion
        */
        Route::get('profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
        Route::put('profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.password.update');
        Route::delete('profile', [AdminProfileController::class, 'destroy'])->name('admin.profile.destroy');

        /*
        | Visual page builder
        */
        Route::resource('pages', AdminPageController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:pages.manage');
        Route::post('pages/reorder', [AdminPageController::class, 'reorder'])->middleware('can:pages.manage')->name('admin.pages.reorder');
        Route::post('pages/{page}/duplicate', [AdminPageController::class, 'duplicate'])->middleware('can:pages.manage')->name('admin.pages.duplicate');
        Route::get('pages/{page}/design', [AdminPageDesignController::class, 'edit'])->middleware('can:design.manage')->name('admin.pages.design');
        Route::put('pages/{page}/design', [AdminPageDesignController::class, 'update'])->middleware('can:design.manage')->name('admin.pages.design.update');
        Route::get('pages/{page}/revisions', [AdminPageController::class, 'revisions'])->middleware('can:pages.manage')->name('admin.pages.revisions');
        Route::get('pages/{page}/revisions/{revision}', [AdminPageController::class, 'diff'])->middleware('can:pages.manage')->name('admin.pages.diff');
        Route::post('pages/{page}/revisions/{revision}/restore', [AdminPageController::class, 'restore'])->middleware('can:pages.manage')->name('admin.pages.restore');
        Route::post('builder/preview', [AdminPageBuilderController::class, 'preview'])->middleware('can:pages.manage')->name('admin.builder.preview');
        Route::post('builder/widget-schema', [AdminPageBuilderController::class, 'widgetSchema'])->middleware('can:pages.manage')->name('admin.builder.widget-schema');
        Route::post('builder/upload', [AdminPageBuilderController::class, 'upload'])->middleware('can:pages.manage')->name('admin.builder.upload');

        /*
        | Digital products, plans, gateways, orders & subscriptions
        */
        Route::resource('products', AdminProductController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:products.manage');
        Route::post('products/{product}/rotate', [AdminProductController::class, 'rotate'])->middleware('can:products.manage')->name('admin.products.rotate');
        Route::resource('categories', AdminCategoryController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:categories.manage');
        Route::resource('plans', AdminPlanController::class, ['as' => 'admin'])
            ->except('show')->middleware('can:plans.manage');
        Route::get('gateways', [AdminGatewayController::class, 'index'])->middleware('can:gateways.manage')->name('admin.gateways.index');
        Route::get('gateways/{gateway}/edit', [AdminGatewayController::class, 'edit'])->middleware('can:gateways.manage')->name('admin.gateways.edit');
        Route::put('gateways/{gateway}', [AdminGatewayController::class, 'update'])->middleware('can:gateways.manage')->name('admin.gateways.update');
        Route::get('orders', [AdminOrderController::class, 'index'])->middleware('can:orders.view')->name('admin.orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->middleware('can:orders.view')->name('admin.orders.show');
        Route::get('shop-orders', [AdminShopOrderController::class, 'index'])->middleware('can:orders.view')->name('admin.shop-orders.index');
        Route::get('shop-orders/{order}', [AdminShopOrderController::class, 'show'])->middleware('can:orders.view')->name('admin.shop-orders.show');
        Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->middleware('can:subscriptions.view')->name('admin.subscriptions.index');
    });
});

/*
| The records of a collection. Registered last, just before the root-level
| {product} catch-all, so every named two-segment route above — /admin/…,
| /shop/…, /services/… — is matched first.
*/
Route::get('/{collection}/{record}', [PublicCollectionController::class, 'show'])
    ->where(['collection' => '[a-z0-9\-_]+', 'record' => '[a-z0-9\-_]+'])
    ->name('collections.show');

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
