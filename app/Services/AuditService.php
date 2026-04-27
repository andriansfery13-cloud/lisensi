<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    /**
     * Create an immutable audit log entry.
     */
    public function log(
        string $action,
        string $actor = 'system',
        ?int $licenseId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        $log = new AuditLog();
        $log->license_id = $licenseId;
        $log->action = $action;
        $log->actor = $actor;
        $log->old_value = $oldValue;
        $log->new_value = $newValue;
        $log->ip_address = $ipAddress ?? request()->ip();
        $log->user_agent = $userAgent ?? request()->userAgent();
        $log->created_at = now();
        $log->save();

        return $log;
    }
}
