<?php

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Gives a record its own search metadata.
 */
trait HasSeoMeta
{
    /**
     * The search metadata for this record.
     */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'metaable');
    }
}
