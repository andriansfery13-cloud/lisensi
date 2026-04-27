@extends('layouts.admin')
@section('title', 'Domain Activations')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-globe"></i> Domain Activations</h2>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> → Domain Activations</div>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Search domain, IP, or serial..." value="{{ request('search') }}">
            <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--text-secondary);cursor:pointer;">
                <input type="checkbox" name="current_only" value="1" {{ request('current_only') ? 'checked' : '' }} style="accent-color:var(--accent-blue);">
                Active only
            </label>
            <button type="submit" class="btn btn-ghost"><i class="fas fa-search"></i> Search</button>
            @if(request()->hasAny(['search','current_only']))
                <a href="{{ route('admin.activations.index') }}" class="btn btn-ghost"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Serial Number</th>
                        <th>Product</th>
                        <th>IP Address</th>
                        <th>Server</th>
                        <th>Status</th>
                        <th>Activated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activations as $act)
                    <tr>
                        <td class="mono" style="font-weight:600;color:var(--accent-cyan);">{{ $act->domain }}</td>
                        <td>
                            <a href="{{ route('admin.licenses.show', $act->license_id) }}" class="mono" style="font-size:0.75rem;color:var(--accent-blue);text-decoration:none;">
                                {{ $act->license?->serial_number ?? '-' }}
                            </a>
                        </td>
                        <td>{{ $act->license?->product_name ?? '-' }}</td>
                        <td class="mono" style="font-size:0.75rem;">{{ $act->ip_address }}</td>
                        <td style="font-size:0.8rem;">{{ $act->server_hostname ?? '-' }}</td>
                        <td>
                            @if($act->is_current)
                                <span class="badge badge-active"><i class="fas fa-circle"></i> Active</span>
                            @else
                                <span class="badge badge-expired"><i class="fas fa-circle"></i> Inactive</span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">{{ $act->activated_at->format('d M Y H:i') }}</td>
                        <td>
                            @if($act->is_current)
                                <form method="POST" action="{{ route('admin.activations.deactivate', $act) }}" onsubmit="return confirm('Deactivate domain {{ $act->domain }}?')" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-power-off"></i></button>
                                </form>
                            @else
                                <span style="color:var(--text-muted);font-size:0.75rem;">{{ $act->deactivated_at?->format('d M Y') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-globe"></i><p>No activations found</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($activations->hasPages())
    <div class="pagination-wrapper">{{ $activations->links() }}</div>
    @endif
</div>
@endsection
