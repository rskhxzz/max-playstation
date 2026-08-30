<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Maxibox Playstation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --clr-dark:#24252A; --clr-magenta:#E6007E; --clr-cyan:#00D9FF; }
        body { font-family:'Inter',sans-serif; background:var(--clr-dark); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { background:#fff; border-radius:16px; width:100%; max-width:400px; padding:2.5rem; box-shadow:0 20px 60px rgba(0,0,0,0.4); }
        .login-brand { text-align:center; margin-bottom:2rem; }
        .login-brand .brand-icon { font-size:2.5rem; color:var(--clr-cyan); }
        .login-brand h1 { font-size:1.4rem; font-weight:700; color:var(--clr-dark); margin-top:0.5rem; }
        .login-brand h1 span { color:var(--clr-magenta); }
        .form-control { border-radius:8px; padding:0.65rem 1rem; border-color:#d1d5db; }
        .form-control:focus { border-color:var(--clr-cyan); box-shadow:0 0 0 3px rgba(0,217,255,0.15); }
        .btn-login { background:var(--clr-magenta); border:none; border-radius:8px; font-weight:600; padding:0.7rem; width:100%; color:#fff; font-size:1rem; }
        .btn-login:hover { background:#c4006b; color:#fff; }
        .input-group-text { border-radius:0 8px 8px 0; background:#f9fafb; border-color:#d1d5db; }
        .alert-danger { border-radius:8px; font-size:0.87rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-brand">
            <div class="brand-icon"><i class="bi bi-controller"></i></div>
            <h1>Maxibox <span>Playstation</span></h1>
            <p class="text-muted small mb-0">Masuk ke Panel Admin / Driver</p>
        </div>

        @include('components.alert')

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-500" for="username">Username atau Email</label>
                <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}" placeholder="Masukkan username atau email" required autofocus>
                @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-500" for="password">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="Masukkan password" required>
                    <span class="input-group-text" style="cursor:pointer;" id="togglePwd">
                        <i class="bi bi-eye" id="pwdIcon"></i>
                    </span>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('home') }}" class="text-muted small" style="text-decoration:none;">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePwd')?.addEventListener('click', function() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('pwdIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text'; icon.className = 'bi bi-eye-slash';
            } else {
                pwd.type = 'password'; icon.className = 'bi bi-eye';
            }
        });
    </script>
</body>
</html>
