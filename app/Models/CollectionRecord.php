<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\CollectionRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One record of a collection type.
 */
class CollectionRecord extends Model
{
    /** @use HasFactory<CollectionRecordFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'collection_type_id',
        'slug',
        'title',
        'data',
        'is_published',
        'published_at',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The slug is built from the record title, not a title.
     *
     * @var list<string>
     */
    protected $slugSource = ['title'];

    /**
     * The type this record belongs to.
     */
    public function collectionType(): BelongsTo
    {
        return $this->belongsTo(CollectionType::class);
    }

    /**
     * Records that are live.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * The value of one field.
     */
    public function value(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * The public path of this record.
     */
    public function publicPath(): string
    {
        return $this->collectionType?->recordPath($this) ?? '/'.$this->slug;
    }
}
