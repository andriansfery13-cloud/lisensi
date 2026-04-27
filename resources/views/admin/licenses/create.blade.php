@extends('layouts.admin')
@section('title', 'Create License')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-plus-circle"></i> Create New License</h2>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> → <a href="{{ route('admin.licenses.index') }}">Licenses</a> → Create</div>
    </div>
    <a href="{{ route('admin.licenses.index') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3><i class="fas fa-file-signature"></i> License Details</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.licenses.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="product_name" class="form-control" value="{{ old('product_name') }}" placeholder="e.g. POS System Pro" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" placeholder="Full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer Email *</label>
                    <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}" placeholder="email@example.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">License Type *</label>
                    <select name="type" class="form-control" id="licenseType" onchange="toggleExpiry()">
                        <option value="perpetual" {{ old('type')=='perpetual'?'selected':'' }}>♾️ Perpetual (Lifetime)</option>
                        <option value="yearly" {{ old('type')=='yearly'?'selected':'' }}>📅 Yearly</option>
                        <option value="monthly" {{ old('type')=='monthly'?'selected':'' }}>📅 Monthly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Domains *</label>
                    <input type="number" name="max_domains" class="form-control" value="{{ old('max_domains', 1) }}" min="1" max="100" required>
                    <div class="form-text">Berapa domain yang boleh menggunakan lisensi ini</div>
                </div>
            </div>

            <div class="form-group" id="expiryGroup" style="display:none;">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                <div class="form-text">Kosongkan untuk auto-set berdasarkan tipe lisensi</div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes...">{{ old('notes') }}</textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('admin.licenses.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Generate License</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleExpiry() {
    const type = document.getElementById('licenseType').value;
    document.getElementById('expiryGroup').style.display = type === 'perpetual' ? 'none' : 'block';
}
document.addEventListener('DOMContentLoaded', toggleExpiry);
</script>
@endsection
