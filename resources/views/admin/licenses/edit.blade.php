@extends('layouts.admin')
@section('title', 'Edit License')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-pen"></i> Edit License</h2>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> → <a href="{{ route('admin.licenses.index') }}">Licenses</a> → Edit</div>
    </div>
    <a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3><i class="fas fa-file-signature"></i> Edit: {{ $license->serial_number }}</h3>
        <span class="badge badge-{{ $license->status }}"><i class="fas fa-circle"></i> {{ ucfirst($license->status) }}</span>
    </div>
    <div class="card-body">
        <div class="alert alert-info" style="margin-bottom:1.5rem;">
            <i class="fas fa-info-circle"></i>
            Serial Number tidak bisa diubah: <strong class="mono">{{ $license->serial_number }}</strong>
        </div>

        <form action="{{ route('admin.licenses.update', $license) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $license->product_name) }}" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $license->customer_name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer Email *</label>
                    <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $license->customer_email) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">License Type *</label>
                    <select name="type" class="form-control" id="licenseType" onchange="toggleExpiry()">
                        <option value="perpetual" {{ old('type', $license->type)=='perpetual'?'selected':'' }}>♾️ Perpetual</option>
                        <option value="yearly" {{ old('type', $license->type)=='yearly'?'selected':'' }}>📅 Yearly</option>
                        <option value="monthly" {{ old('type', $license->type)=='monthly'?'selected':'' }}>📅 Monthly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Domains *</label>
                    <input type="number" name="max_domains" class="form-control" value="{{ old('max_domains', $license->max_domains) }}" min="1" max="100" required>
                </div>
            </div>

            <div class="form-group" id="expiryGroup">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $license->expires_at?->format('Y-m-d')) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $license->notes) }}</textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update License</button>
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
