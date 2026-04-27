@extends('layouts.admin')
@section('title', 'Loader Generator')

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-file-code"></i> Loader Generator</h2>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> → Loader Generator</div>
    </div>
</div>

<div class="grid-2">
    <!-- Generator Form -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-cog"></i> Configuration</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.loader.generate') }}" method="POST" id="loaderForm">
                @csrf

                <div class="form-group">
                    <label class="form-label">Select License *</label>
                    <select name="serial_number" class="form-control" id="serialSelect" required>
                        <option value="">-- Choose License --</option>
                        @foreach($licenses as $lic)
                            <option value="{{ $lic->serial_number }}">{{ $lic->serial_number }} — {{ $lic->product_name }} ({{ $lic->customer_name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">API URL *</label>
                    <input type="url" name="api_url" class="form-control" id="apiUrl" value="{{ url('/api/v1') }}" required>
                    <div class="form-text">URL server lisensi Anda (termasuk /api/v1)</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cache Duration (days)</label>
                        <input type="number" name="cache_days" class="form-control" value="7" min="1" max="30">
                        <div class="form-text">Berapa hari cache valid jika API unreachable</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heartbeat Interval (hours)</label>
                        <input type="number" name="heartbeat_hours" class="form-control" value="24" min="1" max="168">
                        <div class="form-text">Seberapa sering check ke server</div>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;padding:1rem;background:rgba(20,184,166,0.05);border:1px solid rgba(20,184,166,0.15);border-radius:10px;">
                        <input type="checkbox" name="obfuscated" value="1" id="obfuscatedCheck" style="accent-color:var(--accent-teal);width:18px;height:18px;">
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;color:var(--text-primary);">🛡️ Enable Obfuscation</div>
                            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.15rem;">Multi-layer encoding untuk mencegah reverse-engineering</div>
                        </div>
                    </label>
                </div>

                <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
                        <i class="fas fa-download"></i> Generate & Download ZIP
                    </button>
                    <button type="button" class="btn btn-ghost" onclick="previewCode()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview & Info -->
    <div>
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Generated Package Contents</h3>
            </div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.1);border-radius:8px;">
                        <i class="fas fa-file-code" style="color:var(--accent-emerald);font-size:1.1rem;"></i>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">license.php</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">Main loader file — include di aplikasi client</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:rgba(245,158,11,0.05);border:1px solid rgba(245,158,11,0.1);border-radius:8px;">
                        <i class="fas fa-database" style="color:var(--accent-amber);font-size:1.1rem;"></i>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">license_cache.json</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">Cache file — harus writable oleh web server</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:rgba(56,97,251,0.05);border:1px solid rgba(56,97,251,0.1);border-radius:8px;">
                        <i class="fas fa-book" style="color:var(--accent-blue);font-size:1.1rem;"></i>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">README.txt</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">Instruksi pemasangan untuk client</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Layers -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-shield-halved"></i> Protection Layers</h3>
            </div>
            <div class="card-body" style="font-size:0.8rem;">
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    <div style="display:flex;align-items:center;gap:0.5rem;"><span style="color:var(--accent-emerald);">✓</span> File Integrity Check (SHA-256)</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;"><span style="color:var(--accent-emerald);">✓</span> Domain Binding Verification</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;"><span style="color:var(--accent-emerald);">✓</span> Periodic Heartbeat System</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;"><span style="color:var(--accent-emerald);">✓</span> HMAC SHA-256 Signature</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;"><span style="color:var(--accent-emerald);">✓</span> Encrypted Cache Storage</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;"><span style="color:var(--accent-emerald);">✓</span> Anti-Debug Protection</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;"><span style="color:var(--accent-emerald);">✓</span> Auto-exit on Invalid License</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Code Preview Modal -->
<div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);z-index:500;padding:2rem;overflow-y:auto;">
    <div style="max-width:900px;margin:0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-code"></i> Code Preview</h3>
                <button onclick="document.getElementById('previewModal').style.display='none'" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i> Close</button>
            </div>
            <div class="card-body">
                <div class="code-preview" id="codePreview">Select a license and click Preview to see generated code...</div>
            </div>
        </div>
    </div>
</div>

<script>
async function previewCode() {
    const serial = document.getElementById('serialSelect').value;
    const apiUrl = document.getElementById('apiUrl').value;
    const obfuscated = document.getElementById('obfuscatedCheck').checked;

    if (!serial || !apiUrl) {
        alert('Please select a license and enter API URL');
        return;
    }

    const preview = document.getElementById('codePreview');
    preview.textContent = 'Generating preview...';
    document.getElementById('previewModal').style.display = 'block';

    try {
        const res = await fetch('{{ route("admin.loader.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ serial_number: serial, api_url: apiUrl, obfuscated: obfuscated })
        });
        const data = await res.json();
        preview.textContent = data.code || 'Error generating preview';
    } catch (e) {
        preview.textContent = 'Error: ' + e.message;
    }
}
</script>
@endsection
