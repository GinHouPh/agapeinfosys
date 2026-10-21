<?php

namespace App\Providers;

use App\Services\LocalisedUrlGenerator;
use App\Services\SeoService;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The URL generator that puts the language in the address. Bound
        // over the framework's own, so every `route()` and `url()` call in
        // the app carries the language the visitor is reading.
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();

            $app->instance('routes', $routes);

            return new LocalisedUrlGenerator(
                $routes,
                $app->rebinding('request', function ($app): void {
                    $app['url']->setRequest($app['request']);
                }),
                $app['config']['app.asset_url'],
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Shared hosts terminate TLS at a proxy, so Laravel sees the request
        // as http and builds http URLs (webhook/callback URLs, redirects)
        // unless the scheme is forced from the configured public URL.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // The public layout reads the metadata of whichever record the
        // current route is showing, so the tags match the page.
        View::composer('layouts.app', function (ViewContract $view): void {
            $seo = app(SeoService::class);

            $view->with('seoMeta', $seo->forCurrentRequest());
            $view->with('structuredData', $seo->structuredDataForCurrentRequest());
        });
    }
}
