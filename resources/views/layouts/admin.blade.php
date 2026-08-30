<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Maxibox Playstation</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --clr-dark:#24252A; --clr-magenta:#E6007E; --clr-cyan:#00D9FF; --clr-light:#F4F5F7; }
        body { font-family:'Inter',sans-serif; background:var(--clr-light); margin:0; }
        /* Sidebar */
        .sidebar {
            width: 260px; background: var(--clr-dark); min-height: 100vh;
            position: fixed; left: 0; top: 0; z-index: 1000;
            display: flex; flex-direction: column;
            transition: width 0.3s;
        }
        .sidebar-brand { padding: 1.5rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand-text { color: #fff; font-weight: 700; font-size: 1rem; }
        .sidebar-brand-text span { color: var(--clr-cyan); }
        .sidebar-badge { background: var(--clr-magenta); color: #fff; font-size: 0.65rem; padding: 2px 7px; border-radius: 20px; margin-left: 6px; }
        .sidebar-nav { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 0.6rem 1.25rem; color: rgba(255,255,255,0.75);
            text-decoration: none; font-size: 0.88rem; font-weight: 500;
            transition: all 0.2s; border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            color: #fff; background: rgba(255,255,255,0.07);
            border-left-color: var(--clr-cyan);
        }
        .sidebar-nav a i { font-size: 1rem; width: 20px; color: var(--clr-cyan); }
        .sidebar-section { padding: 0.5rem 1.25rem; font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 0.5rem; }
        /* Main content */
        .main-content { margin-left: 260px; min-height: 100vh; }
        .topbar { background: #fff; padding: 0.75rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
        .topbar-title { font-weight: 600; font-size: 1rem; color: var(--clr-dark); }
        .page-content { padding: 1.5rem; }
        /* Cards */
        .stat-card { background: #fff; border-radius: 12px; padding: 1.25rem; border: 1px solid #e5e7eb; box-shadow: 0 1px 6px rgba(0,0,0,0.04); }
        .stat-card .stat-value { font-size: 1.8rem; font-weight: 700; }
        .stat-card .stat-label { font-size: 0.82rem; color: #6b7280; }
        .stat-card .stat-icon { font-size: 1.8rem; }
        /* Tables */
        .table th { background: var(--clr-dark); color: #fff; font-size: 0.82rem; font-weight: 600; padding: 0.65rem 0.85rem; }
        .table td { font-size: 0.87rem; padding: 0.65rem 0.85rem; vertical-align: middle; }
        .table-hover tbody tr:hover { background: #f8f9fc; }
        /* Badges */
        .badge { font-size: 0.75rem; padding: 0.35em 0.7em; }
        /* Forms */
        .form-control, .form-select { border-radius: 8px; border-color: #d1d5db; font-size: 0.9rem; }
        .form-control:focus, .form-select:focus { border-color: var(--clr-cyan); box-shadow: 0 0 0 3px rgba(0,217,255,0.15); }
        .form-label { font-size: 0.85rem; font-weight: 500; color: #374151; }
        /* Buttons */
        .btn-primary { background: var(--clr-magenta); border-color: var(--clr-magenta); font-weight: 500; border-radius: 8px; }
        .btn-primary:hover { background: #c4006b; border-color: #c4006b; }
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 0; overflow: hidden; }
            .sidebar.open { width: 260px; }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('components.sidebar-admin')

    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm d-lg-none" id="sidebarToggle" style="border:none;background:none;">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">{{ auth()->user()?->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:6px;font-size:0.8rem;">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </button>
                </form>
            </div>
        </div>

        <div class="page-content">
            @include('components.alert')
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('open');
        });
    </script>
    @stack('scripts')
</body>
</html>
