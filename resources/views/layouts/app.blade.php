<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My TrackTime')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        :root {
            --primary: #4f46e5;
            --sidebar-width: 250px;
            --bg-body: #f1f5f9;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: white;
            border-right: 1px solid #e2e8f0;
            z-index: 1040;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-brand {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-brand .icon {
            width: 38px;
            height: 38px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .sidebar-brand h5 {
            font-weight: 700;
            font-size: 18px;
            color: #0f172a;
            margin: 0;
        }
        
        .sidebar-brand small {
            font-size: 10px;
            color: #94a3b8;
            display: block;
            margin-top: -2px;
        }
        
        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
        }
        
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #64748b;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .sidebar-nav .nav-link:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        
        .sidebar-nav .nav-link.active {
            background: #eef2ff;
            color: var(--primary);
        }
        
        .sidebar-nav .nav-link i {
            font-size: 18px;
            width: 22px;
        }
        
        .sidebar-nav .nav-link .badge {
            margin-left: auto;
            font-size: 10px;
            padding: 2px 10px;
            border-radius: 20px;
            background: #ef4444;
            color: white;
        }
        
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid #f1f5f9;
        }
        
        .sidebar-footer .user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-footer .user .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
        }
        
        .sidebar-footer .user .name {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }
        
        .sidebar-footer .user .role {
            font-size: 11px;
            color: #94a3b8;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px 28px 28px;
            min-height: 100vh;
        }
        
        /* Topbar */
        .topbar {
            background: white;
            border-radius: 14px;
            padding: 12px 20px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .topbar .page-title {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        
        .topbar .page-sub {
            font-size: 12px;
            color: #94a3b8;
        }
        
        .topbar .status {
            font-size: 12px;
            padding: 4px 14px;
            border-radius: 20px;
            background: #dcfce7;
            color: #16a34a;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .topbar .status .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #16a34a;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px 22px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .stat-card:hover {
            border-color: #cbd5e1;
        }
        
        .stat-card .number {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
        }
        
        .stat-card .label {
            font-size: 13px;
            color: #94a3b8;
            font-weight: 500;
        }
        
        .stat-card .icon {
            font-size: 24px;
            opacity: 0.3;
        }
        
        /* Map */
        #map {
            height: 420px;
            width: 100%;
            border-radius: 14px;
            z-index: 1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 16px; }
        }
        
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="icon"><i class="bi bi-satellite"></i></div>
            <div>
                <h5>My TrackTime</h5>
                <small>Real-time Monitoring</small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Devices
                <span class="badge">{{ auth()->user() ? auth()->user()->items()->count() : 0 }}</span>
            </a>
            <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Transactions
            </a>
            
           @auth
            @if(auth()->user()->role === 'admin')
            <div class="nav-label mt-3">Admin</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i> Verifikasi
                <span class="badge">{{ \App\Models\Transaction::where('status', 'processing')->count() }}</span>
            </a>
            @endif
        @endauth
        </nav>
        
        <div class="sidebar-footer">
            @auth
            <div class="user">
                <div class="avatar">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                <div>
                    <div class="name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="role">{{ auth()->user()->role ?? 'User' }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 50%; width: 30px; height: 30px; padding: 0; font-size: 12px;">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="topbar">
            <div>
                <div class="page-title">@yield('header', 'Dashboard')</div>
                <div class="page-sub">@yield('subheader', 'Pantau perangkat Anda secara real-time')</div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle" style="border: none; padding: 0 8px;">
                    <i class="bi bi-list" style="font-size: 20px;"></i>
                </button>
                <div class="status">
                    <span class="dot"></span> Live
                </div>
            </div>
        </header>
        
        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px; border: none;">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px; border: none;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @yield('content')
    </main>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Sidebar toggle mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
        
        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (window.innerWidth <= 768 && sidebar?.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !toggle?.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
        
        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
                    bsAlert.close();
                }, 5000);
            });
        }, 500);
    </script>
    
    @stack('scripts')
</body>
</html>