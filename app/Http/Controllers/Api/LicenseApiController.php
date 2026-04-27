<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseApiController extends Controller
{
    protected ValidationService $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * POST /api/v1/activate
     * Activate license on a domain.
     */
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'serial_number' => 'required|string',
            'domain' => 'required|string',
            'ip' => 'nullable|string',
            'server_info' => 'nullable|array',
        ]);

        $result = $this->validationService->activate(
            serialNumber: $request->input('serial_number'),
            domain: strtolower(preg_replace('/^www\./', '', $request->input('domain'))),
            ip: $request->input('ip', $request->ip()),
            serverInfo: $request->input('server_info', [])
        );

        $httpCode = match($result['status']) {
            'valid', 'activated' => 200,
            'suspended', 'revoked', 'expired', 'blocked' => 403,
            'max_domains_reached' => 429,
            default => 404,
        };

        return response()->json($result, $httpCode);
    }

    /**
     * POST /api/v1/heartbeat
     * Periodic license check.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $request->validate([
            'serial_number' => 'required|string',
            'domain' => 'required|string',
            'ip' => 'nullable|string',
        ]);

        $result = $this->validationService->heartbeat(
            serialNumber: $request->input('serial_number'),
            domain: strtolower(preg_replace('/^www\./', '', $request->input('domain'))),
            ip: $request->input('ip', $request->ip())
        );

        $httpCode = in_array($result['status'], ['valid']) ? 200 : 403;

        return response()->json($result, $httpCode);
    }

    /**
     * POST /api/v1/validate
     * Quick validation check.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'serial_number' => 'required|string',
            'domain' => 'required|string',
        ]);

        $result = $this->validationService->validate(
            serialNumber: $request->input('serial_number'),
            domain: strtolower(preg_replace('/^www\./', '', $request->input('domain'))),
            ip: $request->input('ip', $request->ip())
        );

        $httpCode = in_array($result['status'], ['valid']) ? 200 : 403;

        return response()->json($result, $httpCode);
    }
}
