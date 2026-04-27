<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function index(Request $request)
    {
        $query = License::query()->withCount('currentActivations');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $licenses = $query->latest()->paginate(15)->withQueryString();

        return view('admin.licenses.index', compact('licenses'));
    }

    public function create()
    {
        return view('admin.licenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'type' => 'required|in:perpetual,yearly,monthly',
            'max_domains' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Auto-set expiry based on type
        if ($validated['type'] === 'yearly' && !$validated['expires_at']) {
            $validated['expires_at'] = now()->addYear();
        } elseif ($validated['type'] === 'monthly' && !$validated['expires_at']) {
            $validated['expires_at'] = now()->addMonth();
        }

        $license = $this->licenseService->create($validated);

        return redirect()->route('admin.licenses.show', $license)
            ->with('success', 'License created! Serial: ' . $license->serial_number);
    }

    public function show(License $license)
    {
        $license->load(['activations' => fn($q) => $q->latest('activated_at'), 'auditLogs' => fn($q) => $q->latest('created_at')->take(20)]);
        $license->loadCount('currentActivations');
        
        return view('admin.licenses.show', compact('license'));
    }

    public function edit(License $license)
    {
        return view('admin.licenses.edit', compact('license'));
    }

    public function update(Request $request, License $license)
    {
        if ($license->status === 'revoked') {
            return back()->with('error', 'Revoked licenses cannot be edited.');
        }

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'type' => 'required|in:perpetual,yearly,monthly',
            'max_domains' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->licenseService->update($license, $validated);

        return redirect()->route('admin.licenses.show', $license)
            ->with('success', 'License updated successfully.');
    }

    // ⛔ NO destroy() METHOD - Licenses cannot be deleted

    public function suspend(License $license)
    {
        if ($license->status !== 'active') {
            return back()->with('error', 'Only active licenses can be suspended.');
        }

        $this->licenseService->suspend($license);
        return back()->with('success', 'License suspended.');
    }

    public function revoke(License $license)
    {
        if ($license->status === 'revoked') {
            return back()->with('error', 'License is already revoked.');
        }

        $this->licenseService->revoke($license);
        return back()->with('warning', 'License permanently revoked. This action cannot be undone.');
    }

    public function activate(License $license)
    {
        if ($license->status !== 'suspended') {
            return back()->with('error', 'Only suspended licenses can be reactivated.');
        }

        $this->licenseService->activate($license);
        return back()->with('success', 'License reactivated.');
    }

    public function transfer(Request $request, License $license)
    {
        $validated = $request->validate([
            'old_domain' => 'required|string',
            'new_domain' => 'required|string',
        ]);

        $this->licenseService->transfer($license, $validated['old_domain'], $validated['new_domain']);
        return back()->with('success', 'License transferred to ' . $validated['new_domain']);
    }
}
