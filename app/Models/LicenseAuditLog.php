<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseAuditLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'license_id',
        'event',
        'domain',
        'ip_address',
        'user_agent',
        'payload',
        'response_code',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
        'response_code' => 'integer',
    ];

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }
}
