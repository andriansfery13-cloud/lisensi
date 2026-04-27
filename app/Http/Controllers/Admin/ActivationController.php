<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseActivation;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = LicenseActivation::with('license');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('license', fn($lq) => $lq->where('serial_number', 'like', "%{$search}%"));
            });
        }

        if ($request->get('current_only')) {
            $query->where('is_current', true);
        }

        $activations = $query->latest('activated_at')->paginate(20)->withQueryString();

        return view('admin.activations.index', compact('activations'));
    }

    public function deactivate(LicenseActivation $activation)
    {
        $activation->update([
            'is_current' => false,
            'deactivated_at' => now(),
        ]);

        $this->auditService->log(
            action: 'domain_deactivated',
            actor: 'admin',
            licenseId: $activation->license_id,
            oldValue: ['domain' => $activation->domain, 'is_current' => true],
            newValue: ['domain' => $activation->domain, 'is_current' => false]
        );

        return back()->with('success', 'Domain ' . $activation->domain . ' deactivated.');
    }
}
