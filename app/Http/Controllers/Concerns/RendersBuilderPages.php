<?php

namespace App\Http\Controllers\Concerns;

use App\Builder\PageRenderer;
use App\Models\Page;
use Illuminate\Contracts\View\View;

/**
 * Lets a flagship route serve a builder-managed page when one is published
 * under the matching slug, falling back to the hand-built view otherwise.
 *
 * This is how "all marketing pages" migrate to the builder with zero risk:
 * the live route keeps working until a builder page with the reserved slug
 * is published.
 */
trait RendersBuilderPages
{
    /**
     * Render the builder page for the current route, or null to fall back.
     */
    protected function builderPage(string $slug, array $data = []): ?View
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
            'html' => $renderer->render($page),
        ] + $data);
    }
}
