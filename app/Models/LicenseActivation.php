<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{
    protected $fillable = [
        'license_id',
        'domain',
        'ip_address',
        'server_hostname',
        'php_version',
        'server_signature',
        'activated_at',
        'deactivated_at',
        'is_current',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'is_current' => 'boolean',
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeForDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }
}
