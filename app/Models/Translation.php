<?php

namespace App\Models;

use Database\Factories\TranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One field of one record, written in one language.
 */
class Translation extends Model
{
    /** @use HasFactory<TranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'locale_id',
        'translatable_type',
        'translatable_id',
        'field',
        'value',
    ];

    /**
     * The language this translation is in.
     */
    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    /**
     * The record this translation belongs to.
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
