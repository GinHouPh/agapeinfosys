<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Read a stored setting, falling back to the given default.
     */
    public static function value(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('settings.all', function (): array {
            return static::query()->pluck('value', 'key')->all();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Persist one or more settings and flush the cached read.
     *
     * @param  array<string, mixed>  $values
     */
    public static function store(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_scalar($value) || $value === null ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)],
            );
        }

        Cache::forget('settings.all');
    }

    /**
     * Decode a JSON-encoded setting into an array.
     *
     * @return array<int|string, mixed>
     */
    public static function structured(string $key, array $default = []): array
    {
        $raw = static::value($key);

        if (! is_string($raw) || $raw === '') {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $default;
    }
}
