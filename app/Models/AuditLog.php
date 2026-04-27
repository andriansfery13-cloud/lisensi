<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'license_id',
        'action',
        'actor',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];

    // ⛔ IMMUTABLE - No updates or deletes
    public function save(array $options = [])
    {
        if ($this->exists) {
            throw new \RuntimeException('Audit logs are immutable and cannot be updated.');
        }
        return parent::save($options);
    }

    public function delete()
    {
        throw new \RuntimeException('Audit logs are immutable and cannot be deleted.');
    }

    public static function destroy($ids)
    {
        throw new \RuntimeException('Audit logs are immutable and cannot be deleted.');
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function scopeForLicense($query, int $licenseId)
    {
        return $query->where('license_id', $licenseId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
