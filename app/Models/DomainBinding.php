<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainBinding extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'license_id',
        'domain',
        'ip_address',
        'installed_version',
        'status',
        'activated_at',
        'last_heartbeat_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'activated_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }
}
