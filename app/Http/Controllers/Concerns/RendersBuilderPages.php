<?php

namespace App\Http\Controllers\Concerns;

use App\Builder\PageRenderer;
use App\Models\Page;
use Illuminate\Contracts\View\View;

/**
 * Lets a public route serve a builder-managed page when one is published under
 * the matching reserved slug, falling back to the hand-built view otherwise.
 *
 * This is how the site migrates to the builder with zero risk: the live route
 * keeps working until a builder page with the reserved slug is published.
 *
 * Flagship slugs (home, about, services, portfolio, ...) take over a single
 * marketing route. Record template slugs (service-detail, article-detail, ...)
 * take over every detail route of that record type and receive the current
 * record through $context, so widgets like record-body can render it.
 */
trait RendersBuilderPages
{
    /**
     * Render the builder page for the current route, or null to fall back.
     *
     * @param  array<string, mixed>  $context  Passed to the renderer; ['record' => $model] drives record widgets.
     * @param  array<string, mixed>  $data  Extra data for the wrapping view (pageTitle, pageDescription).
     */
    protected function builderPage(string $slug, array $context = [], array $data = []): ?View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->first();

        if ($page === null) {
            return null;
        }

        $renderer = app(PageRenderer::class);

        return view('builder.page', [
            'page' => $page,
            'html' => $renderer->render($page, $context),
        ] + $data);
    }
}
