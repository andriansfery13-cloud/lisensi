<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseHeartbeat extends Model
{
    protected $fillable = [
        'license_id',
        'domain',
        'ip_address',
        'server_signature',
        'response_status',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
