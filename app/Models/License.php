<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'license_key',
        'channel',
        'buyer_email',
        'buyer_username',
        'tier',
        'max_domains',
        'support_until',
        'expires_at',
        'status',
        'client_secret_salt',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'support_until' => 'datetime',
        'expires_at' => 'datetime',
        'max_domains' => 'integer',
    ];

    /**
     * @return BelongsTo<SoftwareProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(SoftwareProduct::class, 'product_id');
    }

    /**
     * @return HasMany<DomainBinding, $this>
     */
    public function domainBindings(): HasMany
    {
        return $this->hasMany(DomainBinding::class, 'license_id');
    }

    /**
     * @return HasMany<DomainBinding, $this>
     */
    public function activeBindings(): HasMany
    {
        return $this->domainBindings()->where('status', 'active');
    }

    /**
     * @return HasMany<LicenseAuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(LicenseAuditLog::class, 'license_id');
    }

    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
