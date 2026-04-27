<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('license');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('actor', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($licenseId = $request->get('license_id')) {
            $query->where('license_id', $licenseId);
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit.index', compact('logs', 'actions'));
    }

    public function export(Request $request)
    {
        $query = AuditLog::with('license');

        if ($licenseId = $request->get('license_id')) {
            $query->where('license_id', $licenseId);
        }

        $logs = $query->latest('created_at')->get();

        $csv = "ID,License ID,Serial Number,Action,Actor,IP Address,Created At\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                $log->license_id ?? '-',
                $log->license?->serial_number ?? '-',
                $log->action,
                $log->actor,
                $log->ip_address ?? '-',
                $log->created_at?->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"');
    }
}
