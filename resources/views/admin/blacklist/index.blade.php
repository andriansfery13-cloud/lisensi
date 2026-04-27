@extends('layouts.admin')
@section('title', 'Domain Blacklist')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-ban"></i> Domain Blacklist</h2>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> → Domain Blacklist</div>
    </div>
</div>

<div class="grid-2">
    <!-- Add Domain Form -->
    <div class="card" style="align-self:start;">
        <div class="card-header">
            <h3><i class="fas fa-plus-circle"></i> Block Domain</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.blacklist.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Domain *</label>
                    <input type="text" name="domain" class="form-control" placeholder="example.com" value="{{ old('domain') }}" required>
                    <div class="form-text">Domain yang ingin diblokir dari menggunakan lisensi</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Alasan pemblokiran...">{{ old('reason') }}</textarea>
                </div>
                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                    <i class="fas fa-ban"></i> Block Domain
                </button>
            </form>

            <div class="alert alert-warning" style="margin-top:1rem;margin-bottom:0;">
                <i class="fas fa-exclamation-triangle"></i>
                <div style="font-size:0.8rem;">Domain yang diblokir tidak akan bisa mengaktifkan lisensi apapun.</div>
            </div>
        </div>
    </div>

    <!-- Blacklisted Domains -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Blocked Domains ({{ $blacklists->total() }})</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Reason</th>
                            <th>Blocked At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blacklists as $bl)
                        <tr>
                            <td class="mono" style="font-weight:600;color:var(--accent-red);">{{ $bl->domain }}</td>
                            <td style="font-size:0.8rem;color:var(--text-muted);max-width:200px;">{{ $bl->reason ?? '-' }}</td>
                            <td style="font-size:0.8rem;color:var(--text-muted);">{{ $bl->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.blacklist.destroy', $bl) }}" onsubmit="return confirm('Remove {{ $bl->domain }} from blacklist?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-ghost" title="Unblock"><i class="fas fa-unlock"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-state"><i class="fas fa-check-circle"></i><p>No blocked domains</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($blacklists->hasPages())
        <div class="pagination-wrapper">{{ $blacklists->links() }}</div>
        @endif
    </div>
</div>
@endsection
