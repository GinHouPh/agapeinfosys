<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A named bundle of permissions that can be assigned to admins.
 */
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    /**
     * The slug is built from the role name, not a title.
     *
     * @var list<string>
     */
    protected $slugSource = ['name'];

    /**
     * The permissions this role grants.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * The users holding this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Whether this role grants a permission.
     */
    public function grants(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }
}
