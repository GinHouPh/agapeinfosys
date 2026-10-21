<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\CollectionTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A content type an author defines: a set of fields and the records that
 * use them.
 */
class CollectionType extends Model
{
    /** @use HasFactory<CollectionTypeFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'route_pattern',
        'is_active',
        'is_record_template',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_record_template' => 'boolean',
        ];
    }

    /**
     * The slug is built from the type name, not a title.
     *
     * @var list<string>
     */
    protected $slugSource = ['name'];

    /**
     * The admin who created the type.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The fields of this type.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(CollectionField::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The records of this type.
     */
    public function records(): HasMany
    {
        return $this->hasMany(CollectionRecord::class);
    }

    /**
     * Types that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The public path of one of this type's records.
     */
    public function recordPath(CollectionRecord $record): string
    {
        $pattern = $this->route_pattern;

        if ($pattern === null || $pattern === '') {
            return '/'.$this->slug.'/'.$record->slug;
        }

        return str_replace('{slug}', $record->slug, $pattern);
    }
}
