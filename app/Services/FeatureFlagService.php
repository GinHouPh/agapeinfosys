<?php

namespace App\Services;

use App\Models\FeatureFlag;
use Illuminate\Support\Collection;

/**
 * Reads the platform's feature switches.
 *
 * A flag that does not exist is off. Nothing in the codebase has to create a
 * row before a switch can be used — the switch simply reports false until
 * someone turns it on in the admin.
 */
class FeatureFlagService
{
    /**
     * @var Collection<string, FeatureFlag>|null
     */
    private ?Collection $flags = null;

    /**
     * Every flag, keyed by its key.
     *
     * @return Collection<string, FeatureFlag>
     */
    public function all(): Collection
    {
        return $this->flags ??= FeatureFlag::query()->orderBy('name')->get()->keyBy('key');
    }

    /**
     * Whether a switch is on for this visitor.
     */
    public function enabled(string $key): bool
    {
        $flag = $this->all()->get($key);

        return $flag instanceof FeatureFlag && $flag->appliesTo();
    }

    /**
     * Forget the cached flags — called after a flag is saved.
     */
    public function flush(): void
    {
        $this->flags = null;
    }
}
