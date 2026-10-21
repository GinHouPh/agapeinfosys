<?php

namespace App\Models;

use Database\Factories\CollectionFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One field of a collection type.
 */
class CollectionField extends Model
{
    /** @use HasFactory<CollectionFieldFactory> */
    use HasFactory;

    protected $fillable = [
        'collection_type_id',
        'name',
        'label',
        'type',
        'options',
        'validation',
        'is_required',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'validation' => 'array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The type this field belongs to.
     */
    public function collectionType(): BelongsTo
    {
        return $this->belongsTo(CollectionType::class);
    }

    /**
     * Derive a machine name from the label when none is supplied.
     */
    public static function nameFromLabel(string $label): string
    {
        $name = Str::slug($label, '_');

        return $name !== '' ? $name : 'field_'.Str::lower(Str::random(6));
    }

    /**
     * The extra validation rules set on this field.
     *
     * @return array<int, string>
     */
    public function validationRules(): array
    {
        $rules = $this->validation ?? [];

        if (! is_array($rules)) {
            return [];
        }

        return array_values(array_filter($rules, 'is_string'));
    }
}
