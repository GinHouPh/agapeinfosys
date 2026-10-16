<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'domain',
        'description',
        'logo',
        'callback_url',
        'api_key',
        'api_secret',
        'is_active',
        'sort_order',
    ];

    protected $hidden = [
        'api_secret',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'api_secret' => 'encrypted',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $product): void {
            if ($product->api_key === null || $product->api_key === '') {
                $product->api_key = 'pk_'.Str::lower(Str::random(24));
            }

            if ($product->api_secret === null || $product->api_secret === '') {
                $product->api_secret = Str::random(48);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activePlans(): HasMany
    {
        return $this->plans()->where('is_active', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeInCategory(Builder $query, Category $category): Builder
    {
        return $query->whereIn('category_id', $category->descendantIds());
    }

    /**
     * Rotate both API credentials, returning the fresh secret for one-time display.
     */
    public function rotateApiCredentials(): string
    {
        $secret = Str::random(48);

        $this->forceFill([
            'api_key' => 'pk_'.Str::lower(Str::random(24)),
            'api_secret' => $secret,
        ])->save();

        return $secret;
    }

    /**
     * Public URL of the shop image, when one has been uploaded.
     */
    public function imageUrl(): ?string
    {
        if ($this->logo === null || $this->logo === '') {
            return null;
        }

        return Storage::url($this->logo);
    }
}
