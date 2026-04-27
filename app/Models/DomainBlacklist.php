<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainBlacklist extends Model
{
    protected $fillable = [
        'domain',
        'reason',
    ];

    public static function isDomainBlocked(string $domain): bool
    {
        return static::where('domain', $domain)->exists();
    }
}
