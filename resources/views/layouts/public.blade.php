<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Maxibox Playstation')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/tab-max.png') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --clr-dark:   #24252A;
            --clr-magenta:#E6007E;
            --clr-cyan:   #00D9FF;
            --clr-white:  #FFFFFF;
            --clr-light:  #F4F5F7;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--clr-light); color: #1a1a2e; }

        /* Navbar */
        .navbar-public {
            background: var(--clr-dark);
            padding: 0.75rem 0;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .navbar-brand-text { color: var(--clr-white); font-weight: 700; font-size: 1.2rem; }
        .navbar-brand-text span { color: var(--clr-cyan); }
        .navbar-brand-text:hover {
             color: var(--clr-cyan) !important;
        }
        .navbar-brand-text:hover span {
             color: var(--clr-cyan) !important;
        }
        .navbar-public .nav-link {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500; font-size: 0.92rem;
            transition: color 0.2s;
        }
        .navbar-public .navbar-nav .nav-link:hover {color: #00D9FF !important;}
        .navbar-toggler { border-color: rgba(255,255,255,0.3); }
        .navbar-toggler-icon { filter: invert(1); }

        /* Buttons */
        .btn-primary-custom {
            background: var(--clr-magenta); border: none;
            color: #fff; font-weight: 600; border-radius: 8px;
            padding: 0.55rem 1.5rem; transition: all 0.2s;
        }
        .btn-primary-custom:hover { background: #c4006b; color: #fff; transform: translateY(-1px); }
        .btn-cyan-outline {
            border: 2px solid var(--clr-cyan); color: var(--clr-cyan);
            background: transparent; font-weight: 600; border-radius: 8px;
            padding: 0.55rem 1.5rem; transition: all 0.2s;
        }
        .btn-cyan-outline:hover { background: var(--clr-cyan); color: var(--clr-dark); }

        /* Cards */
        .card { border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }
        .card-header { background: var(--clr-dark); color: #fff; border-radius: 12px 12px 0 0 !important; padding: 0.85rem 1.25rem; }
        .card-header h5, .card-header h6 { margin: 0; font-weight: 600; }

        /* Hero */
        .hero-section {
            background: var(--clr-dark);
            color: #fff;
            padding: 80px 0 60px;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(230,0,126,0.15) 0%, rgba(0,217,255,0.08) 100%);
        }
        .hero-section .container { position: relative; z-index: 1; }
        .hero-title { font-size: 2.8rem; font-weight: 800; line-height: 1.2; }
        .hero-title span { color: var(--clr-cyan); }
        .hero-subtitle { font-size: 1.15rem; color: rgba(255,255,255,0.8); margin: 1rem 0 2rem; }
        .hero-ps-icon { font-size: 8rem; color: var(--clr-cyan); opacity: 0.85; }

        /* Section */
        .section-title { font-size: 1.8rem; font-weight: 700; color: var(--clr-dark); }
        .section-subtitle { color: #6b7280; font-size: 1rem; }
        .accent-line { width: 50px; height: 4px; background: var(--clr-magenta); border-radius: 2px; margin: 0.75rem 0; }
        #paket {
            scroll-margin-top: 80px;
        }

        /* Package cards */
        .package-card { border-radius: 12px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
        .package-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .package-card .price { font-size: 1.6rem; font-weight: 800; color: var(--clr-magenta); }
        .package-card .duration { color: var(--clr-cyan); font-weight: 600; }

        /* FAQ slider */
        .faq-slider-container { overflow: hidden; position: relative; }
        .faq-slider-track { display: flex; gap: 1.25rem; transition: transform 0.4s ease; }
        .faq-card { min-width: 300px; max-width: 320px; background: #fff; border: 1px solid #e5e7eb;
                    border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .faq-card .faq-q { font-weight: 600; color: var(--clr-dark); margin-bottom: 0.75rem; }
        .faq-card .faq-a { color: #4b5563; font-size: 0.9rem; }
        .slider-btn { background: var(--clr-dark); border: 2px solid var(--clr-cyan); color: var(--clr-cyan);
                      border-radius: 50%; width: 42px; height: 42px; display: flex; align-items: center;
                      justify-content: center; cursor: pointer; transition: all 0.2s; }
        .slider-btn:hover { background: var(--clr-cyan); color: var(--clr-dark); }

        /* Footer */
        footer { background: var(--clr-dark); color: rgba(255,255,255,0.75); padding: 2.5rem 0; }
        footer a { color: var(--clr-cyan); text-decoration: none; }
        footer .footer-brand { color: #fff; font-weight: 700; font-size: 1.1rem; }

        /* Alert */
        .alert { border-radius: 10px; }
        .alert-success { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }
        .alert-danger  { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }

        /* Badge */
        .badge { font-size: 0.75rem; padding: 0.35em 0.7em; border-radius: 6px; font-weight: 500; }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title { font-size: 1.8rem; }
            .hero-ps-icon { font-size: 5rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('components.navbar-public')

    <main>
        @yield('content')
    </main>

    @include('components.footer')

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
