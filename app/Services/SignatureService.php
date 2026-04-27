<?php

namespace App\Services;

class SignatureService
{
    private string $secretKey;
    private string $encryptionKey;

    public function __construct()
    {
        $this->secretKey = config('app.license_secret_key', env('LICENSE_SECRET_KEY', 'default-key'));
        $this->encryptionKey = config('app.license_encryption_key', env('LICENSE_ENCRYPTION_KEY', 'default-enc-key'));
    }

    /**
     * Create HMAC SHA256 signature from data array.
     */
    public function sign(array $data): string
    {
        ksort($data);
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $payload, $this->secretKey);
    }

    /**
     * Verify HMAC signature.
     */
    public function verify(array $data, string $signature): bool
    {
        $expectedSignature = $this->sign($data);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Encrypt response payload with AES-256-CBC.
     */
    public function encrypt(array $data): string
    {
        $json = json_encode($data);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($json, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Decrypt payload.
     */
    public function decrypt(string $encrypted): ?array
    {
        try {
            $decoded = base64_decode($encrypted);
            [$iv, $data] = explode('::', $decoded, 2);
            $decrypted = openssl_decrypt($data, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
            return json_decode($decrypted, true);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get the secret key (for loader generator).
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * Get the encryption key (for loader generator).
     */
    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }
}
