<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use App\Models\DomainBlacklist;

class ValidationService
{
    protected SignatureService $signatureService;
    protected AuditService $auditService;

    public function __construct(SignatureService $signatureService, AuditService $auditService)
    {
        $this->signatureService = $signatureService;
        $this->auditService = $auditService;
    }

    /**
     * Activate a license on a domain for the first time.
     */
    public function activate(string $serialNumber, string $domain, string $ip, array $serverInfo = []): array
    {
        // Check domain blacklist
        if (DomainBlacklist::isDomainBlocked($domain)) {
            $this->logAudit('activation_blocked_blacklist', null, $ip, ['domain' => $domain, 'serial' => $serialNumber]);
            return $this->buildResponse('blocked', 'Domain is blacklisted.');
        }

        $license = License::where('serial_number', $serialNumber)->first();

        if (!$license) {
            return $this->buildResponse('invalid', 'License not found.');
        }

        if ($license->status === 'suspended') {
            $this->logAudit('activation_denied_suspended', $license->id, $ip);
            return $this->buildResponse('suspended', 'License is suspended.');
        }

        if ($license->status === 'revoked') {
            $this->logAudit('activation_denied_revoked', $license->id, $ip);
            return $this->buildResponse('revoked', 'License has been permanently revoked.');
        }

        if ($license->is_expired) {
            $license->update(['status' => 'expired']);
            $this->logAudit('activation_denied_expired', $license->id, $ip);
            return $this->buildResponse('expired', 'License has expired.');
        }

        // Check if domain already activated
        $existingActivation = LicenseActivation::where('license_id', $license->id)
            ->where('domain', $domain)
            ->where('is_current', true)
            ->first();

        if ($existingActivation) {
            // Already activated on this domain - return valid
            $license->update(['last_heartbeat_at' => now()]);
            $this->logHeartbeat($license, $domain, $ip, 'valid');
            return $this->buildResponse('valid', 'License is already activated on this domain.', $license);
        }

        // Check max domains limit
        if (!$license->can_activate_more_domains) {
            $this->logAudit('activation_denied_max_domains', $license->id, $ip, ['domain' => $domain]);
            return $this->buildResponse('max_domains_reached', 'Maximum domain limit reached. Contact admin for transfer.');
        }

        // Activate on new domain
        LicenseActivation::create([
            'license_id' => $license->id,
            'domain' => $domain,
            'ip_address' => $ip,
            'server_hostname' => $serverInfo['hostname'] ?? null,
            'php_version' => $serverInfo['php'] ?? null,
            'server_signature' => $this->generateServerSignature($domain, $ip, $serverInfo),
            'activated_at' => now(),
            'is_current' => true,
        ]);

        // Update license
        $domains = $license->activated_domains ?? [];
        $domains[] = $domain;
        $license->update([
            'activated_domains' => array_values(array_unique($domains)),
            'activated_at' => $license->activated_at ?? now(),
            'last_heartbeat_at' => now(),
        ]);

        $this->logAudit('license_activated', $license->id, $ip, ['domain' => $domain]);
        $this->logHeartbeat($license, $domain, $ip, 'activated');

        return $this->buildResponse('activated', 'License activated successfully.', $license->fresh());
    }

    /**
     * Heartbeat check - periodic validation.
     */
    public function heartbeat(string $serialNumber, string $domain, string $ip): array
    {
        if (DomainBlacklist::isDomainBlocked($domain)) {
            return $this->buildResponse('blocked', 'Domain is blacklisted.');
        }

        $license = License::where('serial_number', $serialNumber)->first();

        if (!$license) {
            return $this->buildResponse('invalid', 'License not found.');
        }

        if ($license->status !== 'active') {
            $this->logHeartbeat($license, $domain, $ip, $license->status);
            return $this->buildResponse($license->status, 'License is ' . $license->status . '.');
        }

        if ($license->is_expired) {
            $license->update(['status' => 'expired']);
            $this->logHeartbeat($license, $domain, $ip, 'expired');
            return $this->buildResponse('expired', 'License has expired.');
        }

        // Check domain binding
        $activation = LicenseActivation::where('license_id', $license->id)
            ->where('domain', $domain)
            ->where('is_current', true)
            ->first();

        if (!$activation) {
            $this->logHeartbeat($license, $domain, $ip, 'invalid_domain');
            $this->logAudit('heartbeat_invalid_domain', $license->id, $ip, ['domain' => $domain]);
            return $this->buildResponse('invalid_domain', 'License is not activated for this domain.');
        }

        // Valid heartbeat
        $license->update(['last_heartbeat_at' => now()]);
        $this->logHeartbeat($license, $domain, $ip, 'valid');

        return $this->buildResponse('valid', 'License is valid.', $license);
    }

    /**
     * Quick validate (lighter than heartbeat).
     */
    public function validate(string $serialNumber, string $domain, string $ip): array
    {
        return $this->heartbeat($serialNumber, $domain, $ip);
    }

    /**
     * Build a signed response.
     */
    private function buildResponse(string $status, string $message, ?License $license = null): array
    {
        $timestamp = time();
        
        $responseData = [
            'status' => $status,
            'message' => $message,
            'timestamp' => $timestamp,
        ];

        if ($license && in_array($status, ['valid', 'activated'])) {
            $responseData['license'] = [
                'type' => $license->type,
                'expires_at' => $license->expires_at?->toIso8601String(),
                'product' => $license->product_name,
            ];
            $responseData['next_heartbeat'] = 86400; // 24 hours
        }

        // Sign response
        $responseData['signature'] = $this->signatureService->sign([
            'status' => $status,
            'timestamp' => $timestamp,
        ]);

        return $responseData;
    }

    private function generateServerSignature(string $domain, string $ip, array $serverInfo): string
    {
        return hash('sha256', $domain . $ip . json_encode($serverInfo));
    }

    private function logHeartbeat(License $license, string $domain, string $ip, string $status): void
    {
        LicenseHeartbeat::create([
            'license_id' => $license->id,
            'domain' => $domain,
            'ip_address' => $ip,
            'response_status' => $status,
            'checked_at' => now(),
        ]);
    }

    private function logAudit(string $action, ?int $licenseId, string $ip, array $extra = []): void
    {
        $this->auditService->log(
            action: $action,
            actor: 'api',
            licenseId: $licenseId,
            newValue: $extra,
            ipAddress: $ip
        );
    }
}
