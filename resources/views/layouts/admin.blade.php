<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — License Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg-primary: #f0f4f8;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --bg-card-hover: #f8fafc;
            --border-color: #e2e8f0;
            --border-glow: rgba(14, 165, 233, 0.35);
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --accent-blue: #0ea5e9;
            --accent-cyan: #06b6d4;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-red: #ef4444;
            --accent-teal: #14b8a6;
            --gradient-primary: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        a { color: var(--accent-blue); }

        /* === SIDEBAR === */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-brand h1 {
            font-size: 1rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .sidebar-brand small {
            font-size: 0.65rem;
            color: var(--text-muted);
            font-weight: 400;
            -webkit-text-fill-color: var(--text-muted);
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-label {
            padding: 0.75rem 1.5rem 0.5rem;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            margin: 2px 0;
        }

        .nav-item:hover {
            color: var(--text-primary);
            background: rgba(56, 97, 251, 0.05);
            border-left-color: rgba(56, 97, 251, 0.3);
        }

        .nav-item.active {
            color: var(--accent-blue);
            background: rgba(56, 97, 251, 0.1);
            border-left-color: var(--accent-blue);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .sidebar-footer .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .sidebar-footer .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--gradient-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: white;
            font-weight: 600;
        }

        .sidebar-footer .user-name {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .sidebar-footer .user-email {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.6rem 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            color: var(--accent-red);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* === MAIN CONTENT === */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h2 i {
            color: var(--accent-blue);
        }

        .breadcrumb {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .breadcrumb a {
            color: var(--accent-blue);
            text-decoration: none;
        }

        /* === CARDS === */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .card:hover {
            border-color: var(--border-glow);
            box-shadow: 0 4px 20px rgba(14, 165, 233, 0.08);
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .card-header h3 {
            font-size: 0.95rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* === STAT CARDS === */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 14px 14px 0 0;
        }

        .stat-card.blue::before { background: var(--gradient-primary); }
        .stat-card.green::before { background: linear-gradient(90deg, #10b981, #34d399); }
        .stat-card.amber::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .stat-card.red::before { background: linear-gradient(90deg, #ef4444, #f87171); }
        .stat-card.teal::before { background: linear-gradient(90deg, #14b8a6, #2dd4bf); }
        .stat-card.cyan::before { background: linear-gradient(90deg, #06b6d4, #22d3ee); }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--border-glow);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.1);
        }

        .stat-card .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }

        .stat-card.blue .stat-icon { background: rgba(14, 165, 233, 0.12); color: var(--accent-blue); }
        .stat-card.green .stat-icon { background: rgba(16, 185, 129, 0.12); color: var(--accent-emerald); }
        .stat-card.amber .stat-icon { background: rgba(245, 158, 11, 0.12); color: var(--accent-amber); }
        .stat-card.red .stat-icon { background: rgba(239, 68, 68, 0.12); color: var(--accent-red); }
        .stat-card.teal .stat-icon { background: rgba(20, 184, 166, 0.12); color: var(--accent-teal); }
        .stat-card.cyan .stat-icon { background: rgba(6, 182, 212, 0.12); color: var(--accent-cyan); }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-card .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* === TABLE === */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        tbody td {
            padding: 0.85rem 1rem;
            font-size: 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        tbody tr { transition: background 0.2s ease; }
        tbody tr:hover { background: #f8fafc; }

        /* === BADGES === */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-active { background: #ecfdf5; color: #059669; }
        .badge-suspended { background: #fffbeb; color: #d97706; }
        .badge-revoked { background: #fef2f2; color: #dc2626; }
        .badge-expired { background: #f1f5f9; color: #64748b; }
        .badge-perpetual { background: #f0fdfa; color: #0d9488; }
        .badge-yearly { background: #ecfeff; color: #0891b2; }
        .badge-monthly { background: #e0f2fe; color: #0284c7; }
        .badge-valid { background: #ecfdf5; color: #059669; }
        .badge-invalid { background: #fef2f2; color: #dc2626; }
        .badge-activated { background: #e0f2fe; color: #0284c7; }

        .badge i { font-size: 0.55rem; }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.25);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.35);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .btn-danger {
            background: var(--gradient-danger);
            color: white;
        }

        .btn-ghost {
            background: #f8fafc;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .btn-ghost:hover {
            background: #f1f5f9;
            color: var(--text-primary);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            border-radius: 8px;
        }

        .btn-xs {
            padding: 0.3rem 0.6rem;
            font-size: 0.7rem;
            border-radius: 6px;
        }

        /* === FORMS === */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem 1rem;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--accent-blue);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            padding-right: 2.5rem;
        }

        .form-text {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 0.35rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* === ALERTS === */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error, .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-info {
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            color: #075985;
        }

        /* === PAGINATION === */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            padding: 1.25rem;
        }

        .pagination-wrapper nav > div:first-child { display: none; }

        .pagination-wrapper .flex {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .pagination-wrapper a, .pagination-wrapper span {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pagination-wrapper a:hover {
            background: #e0f2fe;
            color: var(--accent-blue);
        }

        .pagination-wrapper span[aria-current="page"] span {
            background: var(--accent-blue);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
        }

        /* === SEARCH BAR === */
        .search-bar {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .search-bar .form-control {
            max-width: 300px;
        }

        /* === MONO TEXT === */
        .mono {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 0.8rem;
            letter-spacing: 0.02em;
        }

        /* === EMPTY STATE === */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 0.9rem;
        }

        /* === CODE PREVIEW === */
        .code-preview {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 1.25rem;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 0.75rem;
            line-height: 1.6;
            color: #e2e8f0;
            overflow-x: auto;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* === GRID LAYOUTS === */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; }

        /* === RESPONSIVE === */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 200;
            width: 40px;
            height: 40px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 1.1rem;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: flex; }
            .form-row { grid-template-columns: 1fr; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .page-header { flex-direction: column; align-items: flex-start; }
        }

        /* === SCROLLBAR === */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* === CONFIRM MODAL === */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(4px);
            z-index: 500;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2rem;
            max-width: 440px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .modal-box h3 { font-size: 1.1rem; margin-bottom: 0.75rem; }
        .modal-box p { font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem; }
        .modal-actions { display: flex; gap: 0.75rem; justify-content: center; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Toggle -->
    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-shield-halved"></i></div>
            <div>
                <h1>License Manager</h1>
                <small>Secure License System v2</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <div class="nav-label">License Management</div>
            <a href="{{ route('admin.licenses.index') }}" class="nav-item {{ request()->routeIs('admin.licenses.*') ? 'active' : '' }}">
                <i class="fas fa-key"></i> Licenses
            </a>
            <a href="{{ route('admin.activations.index') }}" class="nav-item {{ request()->routeIs('admin.activations.*') ? 'active' : '' }}">
                <i class="fas fa-globe"></i> Domain Activations
            </a>

            <div class="nav-label">Security</div>
            <a href="{{ route('admin.audit.index') }}" class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                <i class="fas fa-scroll"></i> Audit Trail
            </a>
            <a href="{{ route('admin.blacklist.index') }}" class="nav-item {{ request()->routeIs('admin.blacklist.*') ? 'active' : '' }}">
                <i class="fas fa-ban"></i> Domain Blacklist
            </a>

            <div class="nav-label">Tools</div>
            <a href="{{ route('admin.loader.index') }}" class="nav-item {{ request()->routeIs('admin.loader.*') ? 'active' : '' }}">
                <i class="fas fa-file-code"></i> Loader Generator
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div>
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-email">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-times-circle"></i>
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Confirm action helper
        function confirmAction(formId, title, message) {
            if (confirm(title + '\n\n' + message)) {
                document.getElementById(formId).submit();
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
