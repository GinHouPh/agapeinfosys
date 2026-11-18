<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SoftwareProduct extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'envato_item_id',
        'is_active',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * @return HasMany<License, $this>
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'product_id');
    }

    /**
     * @return HasMany<SoftwareRelease, $this>
     */
    public function releases(): HasMany
    {
        return $this->hasMany(SoftwareRelease::class, 'product_id');
    }

    /**
     * @return HasOne<SoftwareRelease, $this>
     */
    public function latestRelease(): HasOne
    {
        return $this->hasOne(SoftwareRelease::class, 'product_id')->where('is_active', true)->latestOfMany();
    }
}
