@extends('layouts.admin')
@section('title', 'Audit Trail')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-scroll"></i> Audit Trail</h2>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> → Audit Trail</div>
    </div>
    <a href="{{ route('admin.audit.export', request()->query()) }}" class="btn btn-ghost"><i class="fas fa-download"></i> Export CSV</a>
</div>

<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Search action, actor, IP..." value="{{ request('search') }}">
            <select name="action" class="form-control" style="max-width:200px;">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action')==$action?'selected':'' }}>{{ str_replace('_', ' ', $action) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-ghost"><i class="fas fa-filter"></i> Filter</button>
            @if(request()->hasAny(['search','action']))
                <a href="{{ route('admin.audit.index') }}" class="btn btn-ghost"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>
</div>

<div class="alert alert-info" style="font-size:0.8rem;">
    <i class="fas fa-shield-halved"></i>
    Audit logs are <strong>immutable</strong> — they cannot be edited or deleted. This ensures complete traceability.
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>License</th>
                        <th>Actor</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>IP Address</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td style="color:var(--text-muted);font-size:0.75rem;">{{ $log->id }}</td>
                        <td>
                            <span class="badge badge-{{ in_array($log->action, ['license_created','license_activated','license_reactivated','domain_deactivated']) ? 'active' : (str_contains($log->action, 'suspend') ? 'suspended' : (str_contains($log->action, 'revok') || str_contains($log->action, 'block') || str_contains($log->action, 'denied') ? 'revoked' : 'activated')) }}">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td>
                            @if($log->license)
                                <a href="{{ route('admin.licenses.show', $log->license_id) }}" class="mono" style="font-size:0.7rem;color:var(--accent-cyan);text-decoration:none;">{{ Str::limit($log->license->serial_number, 18) }}</a>
                            @else
                                <span style="color:var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(148,163,184,0.1);color:var(--text-secondary);">{{ $log->actor }}</span>
                        </td>
                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;font-size:0.7rem;color:var(--text-muted);" title="{{ json_encode($log->old_value) }}">
                            {{ $log->old_value ? Str::limit(json_encode($log->old_value, JSON_UNESCAPED_SLASHES), 40) : '-' }}
                        </td>
                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;font-size:0.7rem;color:var(--text-secondary);" title="{{ json_encode($log->new_value) }}">
                            {{ $log->new_value ? Str::limit(json_encode($log->new_value, JSON_UNESCAPED_SLASHES), 40) : '-' }}
                        </td>
                        <td class="mono" style="font-size:0.75rem;">{{ $log->ip_address ?? '-' }}</td>
                        <td style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-scroll"></i><p>No audit logs yet</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="pagination-wrapper">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
