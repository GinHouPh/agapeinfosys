<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concerns\RecordsActivity;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    use RecordsActivity;

    /**
     * The record types that carry their own search metadata.
     *
     * @var array<string, class-string<Model>>
     */
    private const MODELS = [
        'page' => Page::class,
        'service' => Service::class,
        'project' => Project::class,
        'post' => Post::class,
    ];

    /**
     * The SEO panel for one record.
     */
    public function edit(string $type, int $id, SeoService $seo): View
    {
        $record = $this->find($type, $id);

        return view('admin.seo.edit', [
            'record' => $record,
            'type' => $type,
            'resolved' => $seo->for($record),
            'meta' => $record->seoMeta()->first(),
        ]);
    }

    /**
     * Save the search metadata for one record.
     */
    public function update(Request $request, string $type, int $id, SeoService $seo): RedirectResponse
    {
        $record = $this->find($type, $id);

        $attributes = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:400'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:180'],
            'og_description' => ['nullable', 'string', 'max:400'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'twitter_card' => ['nullable', 'string', 'max:40'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'noindex' => ['sometimes', 'boolean'],
            'sitemap_priority' => ['nullable', 'string', 'max:10'],
            'sitemap_changefreq' => ['nullable', 'string', 'max:20'],
        ]);

        $attributes['noindex'] = $request->boolean('noindex');

        $record->seoMeta()->updateOrCreate([], $attributes);

        $this->recordActivity('updated', $record, 'Updated the search metadata for "'.$record->getAttribute('title').'".');

        return redirect()
            ->route('admin.seo.edit', ['type' => $type, 'id' => $id])
            ->with('status', 'Search metadata saved.');
    }

    /**
     * Find the record a panel belongs to.
     */
    private function find(string $type, int $id): Model
    {
        $model = self::MODELS[$type] ?? null;

        abort_if($model === null, 404);

        return $model::query()->findOrFail($id);
    }
}
