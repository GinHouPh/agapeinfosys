<?php

namespace App\Models;

use Database\Factories\PageRevisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A saved version of a page's layout.
 */
class PageRevision extends Model
{
    /** @use HasFactory<PageRevisionFactory> */
    use HasFactory;

    protected $fillable = [
        'page_id',
        'title',
        'schema',
        'note',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema' => 'array',
        ];
    }

    /**
     * The page this revision belongs to.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * The admin who saved the revision.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
