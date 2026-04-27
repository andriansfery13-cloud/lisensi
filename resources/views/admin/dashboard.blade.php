@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-chart-pie"></i> Dashboard</h2>
        <div class="breadcrumb">Overview sistem lisensi Anda</div>
    </div>
    <a href="{{ route('admin.licenses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New License
    </a>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-key"></i></div>
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Licenses</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value">{{ $stats['active'] }}</div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon"><i class="fas fa-pause-circle"></i></div>
        <div class="stat-value">{{ $stats['suspended'] }}</div>
        <div class="stat-label">Suspended</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value">{{ $stats['revoked'] }}</div>
        <div class="stat-label">Revoked</div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value">{{ $stats['expired'] }}</div>
        <div class="stat-label">Expired</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-value">{{ $stats['expiring_soon'] }}</div>
        <div class="stat-label">Expiring Soon</div>
    </div>
</div>

<!-- Charts & Recent Activity -->
<div class="grid-2" style="margin-bottom: 1.5rem;">
    <!-- Heartbeat Chart -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-area"></i> Heartbeat Activity (30 Days)</h3>
        </div>
        <div class="card-body">
            <canvas id="heartbeatChart" height="200"></canvas>
        </div>
    </div>

    <!-- Recent Activations -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-globe"></i> Recent Domain Activations</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Serial</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivations as $act)
                        <tr>
                            <td><span class="mono">{{ $act->domain }}</span></td>
                            <td><span class="mono" style="font-size:0.7rem;">{{ Str::limit($act->license->serial_number ?? '-', 20) }}</span></td>
                            <td style="color: var(--text-muted); font-size:0.75rem;">{{ $act->activated_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="empty-state"><p>No activations yet</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Audit Log -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-scroll"></i> Recent Audit Log</h3>
        <a href="{{ route('admin.audit.index') }}" class="btn btn-ghost btn-sm">View All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>License</th>
                        <th>Actor</th>
                        <th>IP</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAudit as $log)
                    <tr>
                        <td>
                            <span class="badge badge-{{ in_array($log->action, ['license_created','license_activated','license_reactivated']) ? 'active' : (in_array($log->action, ['license_suspended','heartbeat_invalid_domain']) ? 'suspended' : (in_array($log->action, ['license_revoked','activation_blocked_blacklist']) ? 'revoked' : 'activated')) }}">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="mono" style="font-size:0.75rem;">{{ $log->license?->serial_number ?? '-' }}</td>
                        <td>{{ $log->actor }}</td>
                        <td class="mono" style="font-size:0.75rem;">{{ $log->ip_address ?? '-' }}</td>
                        <td style="color:var(--text-muted);font-size:0.75rem;">{{ $log->created_at?->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="empty-state"><p>No audit entries yet</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('heartbeatChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Valid',
                data: @json($chartValid),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 0,
                pointHitRadius: 10,
            }, {
                label: 'Invalid',
                data: @json($chartInvalid),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239,68,68,0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 0,
                pointHitRadius: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#64748b', font: { size: 10 }, maxTicksLimit: 10 },
                    grid: { color: 'rgba(56,97,251,0.05)' }
                },
                y: {
                    ticks: { color: '#64748b', font: { size: 10 } },
                    grid: { color: 'rgba(56,97,251,0.05)' },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
