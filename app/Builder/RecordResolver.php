<?php

namespace App\Builder;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolves the sample record a single-record template should preview with.
 *
 * A record template page (e.g. "service-detail") has no URL of its own — it
 * renders every /services/{slug} page. So when the canvas needs something to
 * show, or a widget needs an example, this picks a live record of the type the
 * template is bound to, using the model/scope pair declared in config.
 */
class RecordResolver
{
    /**
     * The template definition bound to a page's slug, if any.
     *
     * @return array{label: string, model: class-string<Model>, scope: string, route: ?string, pattern: string}|null
     */
    public function templateFor(Page $page): ?array
    {
        return $this->templateForSlug($page->slug);
    }

    /**
     * The template definition bound to a slug, if any.
     *
     * @return array{label: string, model: class-string<Model>, scope: string, route: ?string, pattern: string}|null
     */
    public function templateForSlug(string $slug): ?array
    {
        $template = config('builder.record_templates')[$slug] ?? null;

        return is_array($template) ? $template : null;
    }

    /**
     * A live record of the type bound to the given slug, for previews.
     */
    public function forSlug(string $slug): ?Model
    {
        $template = $this->templateForSlug($slug);

        if ($template === null) {
            return null;
        }

        $model = $template['model'];
        $scope = $template['scope'];

        return $model::query()->{$scope}()->first();
    }

    /**
     * A live record of the type the given page is a template for.
     */
    public function forPage(Page $page): ?Model
    {
        return $this->forSlug($page->slug);
    }
}
