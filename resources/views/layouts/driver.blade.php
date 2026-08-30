<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Driver') — Maxibox Playstation</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --clr-dark:#24252A; --clr-magenta:#E6007E; --clr-cyan:#00D9FF; --clr-light:#F4F5F7; }
        body { font-family:'Inter',sans-serif; background:var(--clr-light); }
        .navbar-driver { background: var(--clr-dark); }
        .navbar-driver .navbar-brand { color: #fff; font-weight: 700; }
        .navbar-driver .navbar-brand span { color: var(--clr-cyan); }
        .navbar-driver .nav-link { color: rgba(255,255,255,0.8) !important; }
        .navbar-driver .nav-link:hover { color: var(--clr-cyan) !important; }
        .badge { font-size: 0.75rem; }
        .card { border-radius: 12px; border: 1px solid #e5e7eb; }
        .card-header { background: var(--clr-dark); color: #fff; border-radius: 12px 12px 0 0 !important; }
        .table th { background: var(--clr-dark); color: #fff; font-size: 0.82rem; }
        .btn-primary { background: var(--clr-magenta); border-color: var(--clr-magenta); }
        .btn-primary:hover { background: #c4006b; border-color: #c4006b; }
        .form-control:focus { border-color: var(--clr-cyan); box-shadow: 0 0 0 3px rgba(0,217,255,0.15); }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-driver navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('driver.dashboard') }}">
                Maxibox <span>Playstation</span> <small class="badge ms-1" style="background:#E6007E;font-size:0.65rem;">Driver</small>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white small">{{ auth()->user()?->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light" style="font-size:0.8rem;">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        @include('components.alert')
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
