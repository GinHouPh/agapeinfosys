<?php

namespace App\Services;

use App\Models\CollectionType;

/**
 * The collections an author can point a widget at.
 *
 * A widget that lists a collection cannot know the collections at compile
 * time, so the list is filled in when the builder asks for the widget's
 * schema.
 */
class CollectionCatalog
{
    /**
     * The active collections, keyed by their slug.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        return CollectionType::query()
            ->active()
            ->orderBy('name')
            ->pluck('name', 'slug')
            ->all();
    }
}
