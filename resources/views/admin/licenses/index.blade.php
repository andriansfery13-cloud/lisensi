@extends('layouts.admin')
@section('title', 'Licenses')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-key"></i> License Management</h2>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> → Licenses</div>
    </div>
    <a href="{{ route('admin.licenses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create License
    </a>
</div>

<!-- Search & Filter -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Search serial, customer, product..." value="{{ request('search') }}">
            <select name="status" class="form-control" style="max-width:160px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                <option value="suspended" {{ request('status')=='suspended'?'selected':'' }}>Suspended</option>
                <option value="revoked" {{ request('status')=='revoked'?'selected':'' }}>Revoked</option>
                <option value="expired" {{ request('status')=='expired'?'selected':'' }}>Expired</option>
            </select>
            <select name="type" class="form-control" style="max-width:160px;">
                <option value="">All Types</option>
                <option value="perpetual" {{ request('type')=='perpetual'?'selected':'' }}>Perpetual</option>
                <option value="yearly" {{ request('type')=='yearly'?'selected':'' }}>Yearly</option>
                <option value="monthly" {{ request('type')=='monthly'?'selected':'' }}>Monthly</option>
            </select>
            <button type="submit" class="btn btn-ghost"><i class="fas fa-search"></i> Search</button>
            @if(request()->hasAny(['search','status','type']))
                <a href="{{ route('admin.licenses.index') }}" class="btn btn-ghost"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>
</div>

<!-- Licenses Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Domains</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($licenses as $license)
                    <tr>
                        <td>
                            <a href="{{ route('admin.licenses.show', $license) }}" class="mono" style="color: var(--accent-cyan); text-decoration: none; font-size: 0.8rem;">
                                {{ $license->serial_number }}
                            </a>
                        </td>
                        <td>{{ $license->product_name }}</td>
                        <td>
                            <div style="font-weight:500;">{{ $license->customer_name }}</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">{{ $license->customer_email }}</div>
                        </td>
                        <td><span class="badge badge-{{ $license->type }}">{{ ucfirst($license->type) }}</span></td>
                        <td><span class="badge badge-{{ $license->status }}"><i class="fas fa-circle"></i> {{ ucfirst($license->status) }}</span></td>
                        <td>
                            <span style="color:var(--accent-blue);font-weight:600;">{{ $license->current_activations_count }}</span>
                            <span style="color:var(--text-muted);">/ {{ $license->max_domains }}</span>
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">
                            @if($license->expires_at)
                                {{ $license->expires_at->format('d M Y') }}
                                @if($license->is_expired)
                                    <div style="color:var(--accent-red);font-size:0.7rem;">Expired</div>
                                @endif
                            @else
                                <span style="color:var(--accent-teal);">∞ Perpetual</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
                                <a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-ghost btn-xs" title="View"><i class="fas fa-eye"></i></a>
                                @if($license->status !== 'revoked')
                                    <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-ghost btn-xs" title="Edit"><i class="fas fa-pen"></i></a>
                                @endif
                                @if($license->status === 'active')
                                    <form method="POST" action="{{ route('admin.licenses.suspend', $license) }}" style="display:inline;" onsubmit="return confirm('Suspend this license?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-xs btn-warning" title="Suspend"><i class="fas fa-pause"></i></button>
                                    </form>
                                @elseif($license->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.licenses.activate', $license) }}" style="display:inline;" onsubmit="return confirm('Reactivate this license?')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-xs btn-success" title="Activate"><i class="fas fa-play"></i></button>
                                    </form>
                                @endif
                                {{-- ⛔ NO DELETE BUTTON --}}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-key"></i>
                                <p>No licenses found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($licenses->hasPages())
    <div class="pagination-wrapper">{{ $licenses->links() }}</div>
    @endif
</div>
@endsection
