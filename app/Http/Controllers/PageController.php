<?php

namespace App\Http\Controllers;

use App\Builder\PageRenderer;
use App\Models\Page;
use Illuminate\Contracts\View\View;

/**
 * Public renderer for builder-managed pages at /pages/{path}.
 *
 * The three flagship routes (home, about, services) delegate to the builder
 * via their own controllers; every other builder page lives under /pages.
 */
class PageController extends Controller
{
    public function __construct(private readonly PageRenderer $renderer) {}

    /**
     * Render a published builder page.
     */
    public function show(Page $page): View
    {
        abort_unless($page->isLive(), 404);

        return view('builder.page', [
            'page' => $page,
            'html' => $this->renderer->render($page),
        ]);
    }
}
