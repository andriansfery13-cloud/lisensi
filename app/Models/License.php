<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class License extends Model
{
    protected $fillable = [
        'serial_number',
        'product_name',
        'customer_name',
        'customer_email',
        'type',
        'status',
        'max_domains',
        'activated_domains',
        'activated_at',
        'expires_at',
        'last_heartbeat_at',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'activated_domains' => 'array',
        'metadata' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
    ];

    // ⛔ PREVENT DELETE - Override delete methods
    public function delete()
    {
        throw new \RuntimeException('Licenses cannot be deleted. Use suspend() or revoke() instead.');
    }

    public static function destroy($ids)
    {
        throw new \RuntimeException('Licenses cannot be deleted. Use suspend() or revoke() instead.');
    }

    public function forceDelete()
    {
        throw new \RuntimeException('Licenses cannot be deleted.');
    }

    // Relationships
    public function activations()
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public function currentActivations()
    {
        return $this->hasMany(LicenseActivation::class)->where('is_current', true);
    }

    public function heartbeats()
    {
        return $this->hasMany(LicenseHeartbeat::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', 'suspended');
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->where('status', 'revoked');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now());
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && !$this->is_expired;
    }

    public function getActivatedDomainCountAttribute(): int
    {
        return $this->currentActivations()->count();
    }

    public function getCanActivateMoreDomainsAttribute(): bool
    {
        return $this->activated_domain_count < $this->max_domains;
    }
}
