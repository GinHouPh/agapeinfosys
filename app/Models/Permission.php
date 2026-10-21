<?php

namespace App\Models;

use Database\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A single capability an admin role can grant.
 */
class Permission extends Model
{
    /** @use HasFactory<PermissionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
    ];

    /**
     * The roles that hold this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * The catalogue of every permission, grouped for the role editor.
     *
     * @return array<string, array<string, string>>
     */
    public static function catalogue(): array
    {
        return config('permissions.groups', []);
    }

    /**
     * Every permission slug in the catalogue.
     *
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(array_merge(...array_values(static::catalogue())));
    }

    /**
     * The label a permission slug is shown with.
     */
    public static function labelFor(string $slug): ?string
    {
        foreach (static::catalogue() as $permissions) {
            if (isset($permissions[$slug])) {
                return $permissions[$slug];
            }
        }

        return null;
    }

    /**
     * The group a permission slug belongs to.
     */
    public static function groupFor(string $slug): ?string
    {
        foreach (static::catalogue() as $group => $permissions) {
            if (isset($permissions[$slug])) {
                return $group;
            }
        }

        return null;
    }
}
