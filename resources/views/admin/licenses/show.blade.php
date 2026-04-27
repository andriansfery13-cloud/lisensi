@extends('layouts.admin')
@section('title', 'License Detail')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-key"></i> License Detail</h2>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> → <a href="{{ route('admin.licenses.index') }}">Licenses</a> → {{ $license->serial_number }}</div>
    </div>
    <div style="display:flex;gap:0.5rem;">
        @if($license->status !== 'revoked')
            <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-ghost"><i class="fas fa-pen"></i> Edit</a>
        @endif
        <a href="{{ route('admin.licenses.index') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<!-- License Info -->
<div class="grid-2" style="margin-bottom:1.5rem;">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> License Information</h3>
            <span class="badge badge-{{ $license->status }}" style="font-size:0.8rem;padding:0.4rem 0.8rem;">
                <i class="fas fa-circle"></i> {{ strtoupper($license->status) }}
            </span>
        </div>
        <div class="card-body">
            <div style="background:rgba(56,97,251,0.05);border:1px dashed rgba(56,97,251,0.2);border-radius:10px;padding:1rem;margin-bottom:1.25rem;text-align:center;">
                <div style="font-size:0.7rem;color:var(--text-muted);margin-bottom:0.25rem;">SERIAL NUMBER</div>
                <div class="mono" style="font-size:1.1rem;font-weight:700;color:var(--accent-cyan);letter-spacing:0.05em;">{{ $license->serial_number }}</div>
            </div>

            <table style="width:100%;border-collapse:collapse;">
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;width:40%;">Product</td><td style="padding:0.5rem 0;font-weight:500;">{{ $license->product_name }}</td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Customer</td><td style="padding:0.5rem 0;font-weight:500;">{{ $license->customer_name }}</td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Email</td><td style="padding:0.5rem 0;">{{ $license->customer_email }}</td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Type</td><td style="padding:0.5rem 0;"><span class="badge badge-{{ $license->type }}">{{ ucfirst($license->type) }}</span></td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Max Domains</td><td style="padding:0.5rem 0;font-weight:600;">{{ $license->max_domains }}</td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Active Domains</td><td style="padding:0.5rem 0;font-weight:600;color:var(--accent-blue);">{{ $license->current_activations_count }}</td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Created</td><td style="padding:0.5rem 0;">{{ $license->created_at->format('d M Y H:i') }}</td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">First Activated</td><td style="padding:0.5rem 0;">{{ $license->activated_at?->format('d M Y H:i') ?? '-' }}</td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Expires</td><td style="padding:0.5rem 0;">{{ $license->expires_at?->format('d M Y') ?? '∞ Perpetual' }}</td></tr>
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Last Heartbeat</td><td style="padding:0.5rem 0;">{{ $license->last_heartbeat_at?->diffForHumans() ?? 'Never' }}</td></tr>
                @if($license->notes)
                <tr><td style="padding:0.5rem 0;color:var(--text-muted);font-size:0.8rem;">Notes</td><td style="padding:0.5rem 0;">{{ $license->notes }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <!-- Actions Card -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-cog"></i> Actions</h3>
        </div>
        <div class="card-body">
            @if($license->status === 'active')
                <div style="margin-bottom:1rem;">
                    <form method="POST" action="{{ route('admin.licenses.suspend', $license) }}" onsubmit="return confirm('Suspend license ini? Customer tidak akan bisa menggunakan aplikasi.')">
                        @csrf @method('PATCH')
                        <button class="btn btn-warning" style="width:100%;margin-bottom:0.5rem;"><i class="fas fa-pause"></i> Suspend License</button>
                    </form>
                    <div class="form-text">Suspend sementara. Bisa di-reactivate kembali.</div>
                </div>
                <div style="margin-bottom:1rem;">
                    <form method="POST" action="{{ route('admin.licenses.revoke', $license) }}" onsubmit="return confirm('⚠️ PERMANEN! Revoke license ini? Tindakan ini TIDAK BISA dibatalkan!')">
                        @csrf @method('PATCH')
                        <button class="btn btn-danger" style="width:100%;margin-bottom:0.5rem;"><i class="fas fa-skull-crossbones"></i> Revoke License (Permanent)</button>
                    </form>
                    <div class="form-text" style="color:var(--accent-red);">⚠️ Permanen! Tidak bisa dibatalkan.</div>
                </div>
            @elseif($license->status === 'suspended')
                <div style="margin-bottom:1rem;">
                    <form method="POST" action="{{ route('admin.licenses.activate', $license) }}" onsubmit="return confirm('Reactivate license ini?')">
                        @csrf @method('PATCH')
                        <button class="btn btn-success" style="width:100%;margin-bottom:0.5rem;"><i class="fas fa-play"></i> Reactivate License</button>
                    </form>
                    <div class="form-text">Aktifkan kembali lisensi yang di-suspend.</div>
                </div>
                <div style="margin-bottom:1rem;">
                    <form method="POST" action="{{ route('admin.licenses.revoke', $license) }}" onsubmit="return confirm('⚠️ PERMANEN! Revoke license ini?')">
                        @csrf @method('PATCH')
                        <button class="btn btn-danger" style="width:100%;margin-bottom:0.5rem;"><i class="fas fa-skull-crossbones"></i> Revoke License (Permanent)</button>
                    </form>
                </div>
            @elseif($license->status === 'revoked')
                <div class="alert alert-danger" style="margin:0;">
                    <i class="fas fa-lock"></i> License ini telah di-revoke secara permanen dan tidak bisa diubah lagi.
                </div>
            @endif

            @if($license->status !== 'revoked' && $license->currentActivations->count() > 0)
                <hr style="border-color:var(--border-color);margin:1.25rem 0;">
                <h4 style="font-size:0.85rem;margin-bottom:0.75rem;"><i class="fas fa-exchange-alt"></i> Transfer Domain</h4>
                <form method="POST" action="{{ route('admin.licenses.transfer', $license) }}" onsubmit="return confirm('Transfer license ke domain baru?')">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label class="form-label">From Domain</label>
                        <select name="old_domain" class="form-control">
                            @foreach($license->currentActivations as $act)
                                <option value="{{ $act->domain }}">{{ $act->domain }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To New Domain</label>
                        <input type="text" name="new_domain" class="form-control" placeholder="new-domain.com" required>
                    </div>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-exchange-alt"></i> Transfer</button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Domain Activations History -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h3><i class="fas fa-globe"></i> Domain Activation History</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>IP Address</th>
                        <th>Server</th>
                        <th>PHP</th>
                        <th>Status</th>
                        <th>Activated</th>
                        <th>Deactivated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($license->activations as $act)
                    <tr>
                        <td class="mono">{{ $act->domain }}</td>
                        <td class="mono" style="font-size:0.75rem;">{{ $act->ip_address }}</td>
                        <td style="font-size:0.8rem;">{{ $act->server_hostname ?? '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $act->php_version ?? '-' }}</td>
                        <td>
                            @if($act->is_current)
                                <span class="badge badge-active"><i class="fas fa-circle"></i> Active</span>
                            @else
                                <span class="badge badge-expired"><i class="fas fa-circle"></i> Deactivated</span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">{{ $act->activated_at->format('d M Y H:i') }}</td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">{{ $act->deactivated_at?->format('d M Y H:i') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-globe"></i><p>No domain activations yet</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Audit Trail -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-scroll"></i> Audit Trail</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Action</th><th>Actor</th><th>Details</th><th>IP</th><th>Time</th></tr>
                </thead>
                <tbody>
                    @forelse($license->auditLogs as $log)
                    <tr>
                        <td><span class="badge badge-{{ in_array($log->action, ['license_created','license_activated','license_reactivated']) ? 'active' : (str_contains($log->action,'suspend') ? 'suspended' : (str_contains($log->action,'revok') ? 'revoked' : 'activated')) }}">{{ str_replace('_', ' ', $log->action) }}</span></td>
                        <td>{{ $log->actor }}</td>
                        <td style="font-size:0.75rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;">{{ $log->new_value ? json_encode($log->new_value, JSON_UNESCAPED_SLASHES) : '-' }}</td>
                        <td class="mono" style="font-size:0.75rem;">{{ $log->ip_address ?? '-' }}</td>
                        <td style="font-size:0.75rem;color:var(--text-muted);">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="empty-state"><p>No audit entries</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
