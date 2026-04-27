<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseHeartbeat;
use App\Models\AuditLog;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function index()
    {
        $stats = $this->licenseService->getStatistics();

        // Recent activations (last 10)
        $recentActivations = LicenseActivation::with('license')
            ->latest('activated_at')
            ->take(10)
            ->get();

        // Recent audit logs (last 10)
        $recentAudit = AuditLog::with('license')
            ->latest('created_at')
            ->take(10)
            ->get();

        // Heartbeat chart data (last 30 days)
        $chartData = LicenseHeartbeat::selectRaw("DATE(checked_at) as date, response_status, COUNT(*) as total")
            ->where('checked_at', '>=', now()->subDays(30))
            ->groupByRaw('DATE(checked_at), response_status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        $chartLabels = [];
        $chartValid = [];
        $chartInvalid = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $dayData = $chartData->get($date);
            $chartValid[] = $dayData ? $dayData->where('response_status', 'valid')->sum('total') : 0;
            $chartInvalid[] = $dayData ? $dayData->where('response_status', '!=', 'valid')->sum('total') : 0;
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentActivations',
            'recentAudit',
            'chartLabels',
            'chartValid',
            'chartInvalid'
        ));
    }
}
