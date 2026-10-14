<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
