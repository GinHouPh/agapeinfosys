<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Builds a unique, URL-safe slug from a source attribute when none is set.
 */
trait GeneratesSlug
{
    /**
     * Attributes consulted (in order) to build the slug.
     *
     * @var list<string>
     */
    protected function slugSourceAttributes(): array
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : ['title'];
    }

    protected static function bootGeneratesSlug(): void
    {
        static::saving(function (self $model): void {
            if ($model->getAttribute('slug') !== null && $model->getAttribute('slug') !== '') {
                return;
            }

            foreach ($model->slugSourceAttributes() as $attribute) {
                $value = $model->getAttribute($attribute);

                if (is_string($value) && $value !== '') {
                    $model->setAttribute('slug', $model->makeUniqueSlug($value));

                    return;
                }
            }
        });
    }

    protected function makeUniqueSlug(string $value): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'item';

        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->where($this->getSlugColumn(), $slug)
            ->where($this->getKeyName(), '!=', $this->getKey())
            ->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }

    protected function getSlugColumn(): string
    {
        return 'slug';
    }
}
