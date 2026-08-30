<nav class="navbar navbar-expand-lg navbar-public">
    <div class="container">
        <a class="navbar-brand navbar-brand-text d-flex align-items-center gap-2" href="{{ route('home') }}">
            <i class="bi bi-controller" style="color:var(--clr-cyan);font-size:1.4rem;"></i>
            Maxibox <span>Playstation</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPublic">
            <ul class="navbar-nav ms-auto gap-2 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'text-white fw-600' : '' }}" href="{{ route('home') }}">
                        <i class="bi bi-house"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('booking.create') }}">
                        <i class="bi bi-cart3"></i> Pesan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">
                        <i class="bi bi-person-circle"></i> Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
