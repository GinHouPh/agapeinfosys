<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoftwareRelease extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'version',
        'min_php_version',
        'changelog',
        'file_path',
        'file_size_bytes',
        'sha256_checksum',
        'is_prerelease',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_prerelease' => 'boolean',
        'is_active' => 'boolean',
        'file_size_bytes' => 'integer',
    ];

    /**
     * @return BelongsTo<SoftwareProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(SoftwareProduct::class, 'product_id');
    }
}
