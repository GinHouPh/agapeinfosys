<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'phone', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /*
    |--------------------------------------------------------------------------
    | Access control
    |--------------------------------------------------------------------------
    |
    | "is_admin" is the superuser flag: it bypasses every permission check.
    | Everyone else reaches the admin area through the roles assigned to
    | them, and only the capabilities those roles grant.
    |
    */

    /**
     * The roles assigned to this user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Whether this user bypasses every permission check.
     */
    public function isSuperAdmin(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Whether this user may do what the permission allows.
     */
    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissionSlugs()->contains($slug);
    }

    /**
     * Whether this user holds any permission at all.
     */
    public function hasAnyPermission(): bool
    {
        return $this->isSuperAdmin() || $this->permissionSlugs()->isNotEmpty();
    }

    /**
     * Whether this user holds every one of the given permissions.
     *
     * @param  array<int, string>  $slugs
     */
    public function hasEveryPermission(array $slugs): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $granted = $this->permissionSlugs();

        return collect($slugs)->every(fn (string $slug): bool => $granted->contains($slug));
    }

    /**
     * Every permission slug granted through this user's roles.
     *
     * @return Collection<int, string>
     */
    public function permissionSlugs(): Collection
    {
        return $this->roles
            ->loadMissing('permissions')
            ->flatMap(fn (Role $role): Collection => $role->permissions->pluck('slug'))
            ->unique()
            ->values();
    }

    /*
    |----------------------------------------------------------------------
    | Purchases
    |----------------------------------------------------------------------
    |
    | Checkout is a guest flow: an order records the email it was placed
    | with. A client account therefore owns every order and subscription
    | carrying its email — the same lookup the entitlement API performs.
    |
    */

    /**
     * The checkout orders placed with this client's email address.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'email', 'email');
    }

    /**
     * The subscriptions held under this client's email address.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'email', 'email');
    }
}
