<?php

namespace App\Services;

class SerialNumberService
{
    /**
     * Generate a cryptographically secure serial number.
     * Format: LSNSI-XXXX-XXXX-XXXX-XXXX
     * Last char of last group is a checksum digit.
     */
    public function generate(string $prefix = 'LSNSI'): string
    {
        $groups = [];
        for ($i = 0; $i < 4; $i++) {
            $groups[] = strtoupper(bin2hex(random_bytes(2)));
        }
        
        $serial = $prefix . '-' . implode('-', $groups);
        
        // Add checksum to make serial self-validating
        $checksum = $this->calculateChecksum($serial);
        $serial = substr($serial, 0, -1) . $checksum;
        
        return $serial;
    }

    /**
     * Validate serial number format and checksum.
     */
    public function validate(string $serial): bool
    {
        // Check format
        if (!preg_match('/^[A-Z]{2,10}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}$/', $serial)) {
            return false;
        }

        // Check checksum
        $withoutChecksum = substr($serial, 0, -1);
        $expectedChecksum = $this->calculateChecksum($withoutChecksum . '0');
        $actualChecksum = substr($serial, -1);
        
        return $expectedChecksum === $actualChecksum;
    }

    /**
     * Calculate checksum character using modular arithmetic on hex values.
     */
    private function calculateChecksum(string $serial): string
    {
        $hex = str_replace('-', '', $serial);
        $hex = preg_replace('/[^A-F0-9]/i', '', $hex);
        
        $sum = 0;
        for ($i = 0; $i < strlen($hex) - 1; $i++) {
            $sum += hexdec($hex[$i]) * ($i + 1);
        }
        
        return strtoupper(dechex($sum % 16));
    }
}
