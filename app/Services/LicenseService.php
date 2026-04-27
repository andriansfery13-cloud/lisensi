<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseActivation;

class LicenseService
{
    protected SerialNumberService $serialService;
    protected AuditService $auditService;

    public function __construct(SerialNumberService $serialService, AuditService $auditService)
    {
        $this->serialService = $serialService;
        $this->auditService = $auditService;
    }

    /**
     * Create a new license with auto-generated serial number.
     */
    public function create(array $data): License
    {
        $data['serial_number'] = $this->serialService->generate();
        
        // Ensure serial is unique
        while (License::where('serial_number', $data['serial_number'])->exists()) {
            $data['serial_number'] = $this->serialService->generate();
        }

        $license = License::create($data);

        $this->auditService->log(
            action: 'license_created',
            actor: 'admin',
            licenseId: $license->id,
            newValue: $license->toArray()
        );

        return $license;
    }

    /**
     * Update license (limited fields only).
     */
    public function update(License $license, array $data): License
    {
        $oldValues = $license->only(['product_name', 'customer_name', 'customer_email', 'type', 'max_domains', 'expires_at', 'notes']);
        
        $license->update($data);

        $this->auditService->log(
            action: 'license_updated',
            actor: 'admin',
            licenseId: $license->id,
            oldValue: $oldValues,
            newValue: $license->fresh()->only(['product_name', 'customer_name', 'customer_email', 'type', 'max_domains', 'expires_at', 'notes'])
        );

        return $license->fresh();
    }

    /**
     * Suspend a license (reversible).
     */
    public function suspend(License $license): License
    {
        $oldStatus = $license->status;
        $license->update(['status' => 'suspended']);

        $this->auditService->log(
            action: 'license_suspended',
            actor: 'admin',
            licenseId: $license->id,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => 'suspended']
        );

        return $license->fresh();
    }

    /**
     * Revoke a license (irreversible).
     */
    public function revoke(License $license): License
    {
        $oldStatus = $license->status;
        $license->update(['status' => 'revoked']);

        // Deactivate all current activations
        $license->currentActivations()->update([
            'is_current' => false,
            'deactivated_at' => now(),
        ]);

        $this->auditService->log(
            action: 'license_revoked',
            actor: 'admin',
            licenseId: $license->id,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => 'revoked']
        );

        return $license->fresh();
    }

    /**
     * Reactivate a suspended license.
     */
    public function activate(License $license): License
    {
        if ($license->status !== 'suspended') {
            throw new \RuntimeException('Only suspended licenses can be reactivated.');
        }

        $license->update(['status' => 'active']);

        $this->auditService->log(
            action: 'license_reactivated',
            actor: 'admin',
            licenseId: $license->id,
            oldValue: ['status' => 'suspended'],
            newValue: ['status' => 'active']
        );

        return $license->fresh();
    }

    /**
     * Transfer license to a new domain (admin only).
     */
    public function transfer(License $license, string $oldDomain, string $newDomain): License
    {
        // Deactivate old domain
        LicenseActivation::where('license_id', $license->id)
            ->where('domain', $oldDomain)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'deactivated_at' => now(),
            ]);

        // Update activated_domains
        $domains = $license->activated_domains ?? [];
        $domains = array_filter($domains, fn($d) => $d !== $oldDomain);
        $domains[] = $newDomain;
        $license->update(['activated_domains' => array_values(array_unique($domains))]);

        $this->auditService->log(
            action: 'license_transferred',
            actor: 'admin',
            licenseId: $license->id,
            oldValue: ['domain' => $oldDomain],
            newValue: ['domain' => $newDomain]
        );

        return $license->fresh();
    }

    /**
     * Get dashboard statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => License::count(),
            'active' => License::active()->count(),
            'suspended' => License::suspended()->count(),
            'revoked' => License::revoked()->count(),
            'expired' => License::expired()->count(),
            'expiring_soon' => License::expiringSoon()->count(),
        ];
    }
}
