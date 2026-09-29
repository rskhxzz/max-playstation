<aside class="sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-text">
            <i
                class="bi bi-controller me-2"
                style="color:var(--clr-cyan);"></i>

            Maxibox <span>Playstation</span>
        </div>

        <div
            class="mt-1"
            style="font-size:0.72rem;color:rgba(255,255,255,0.5);">
            Panel Admin
        </div>
    </div>

    <nav class="sidebar-nav">
        <a
            href="{{ route('admin.dashboard') }}"
            class="{{ request()->routeIs('admin.dashboard') && request()->query('section') !== 'driver-income' ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

        <div class="sidebar-section">
            Transaksi
        </div>

        <a
            href="{{ route('admin.bookings.index') }}"
            class="{{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
            <i class="bi bi-bag-check"></i>
            Pemesanan
        </a>

        <a
            href="{{ route('admin.payments.index') }}"
            class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i>
            Pembayaran
        </a>

        <a
            href="{{ route(
                'admin.dashboard',
                [
                    'section' => 'driver-income',
                ]
            ) }}"
            class="{{ request()->query('section') === 'driver-income' ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i>
            Pendapatan Driver
        </a>

        <div class="sidebar-section">
            Master Data
        </div>

        <a
            href="{{ route('admin.packages.index') }}"
            class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
            <i class="bi bi-box"></i>
            Paket Sewa
        </a>

        <a
            href="{{ route('admin.units.index') }}"
            class="{{ request()->routeIs('admin.units.*') ? 'active' : '' }}">
            <i class="bi bi-controller"></i>
            Unit PlayStation
        </a>

        <a
            href="{{ route('admin.delivery-rates.index') }}"
            class="{{ request()->routeIs('admin.delivery-rates.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i>
            Tarif Pengiriman
        </a>

        <a
            href="{{ route('admin.faqs.index') }}"
            class="{{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
            <i class="bi bi-question-circle"></i>
            FAQ
        </a>

        <a
            href="{{ route('admin.terms.index') }}"
            class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}">
            <i class="bi bi-file-text"></i>
            Syarat & Ketentuan
        </a>

        <div class="sidebar-section">
            Pengaturan
        </div>

        <a
            href="{{ route('admin.settings.edit') }}"
            class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            Pengaturan Usaha
        </a>

        <a
            href="{{ route('admin.users.index') }}"
            class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            Pengguna
        </a>

        <div
            style="padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,0.1);margin-top:1rem;">
            <form
                action="{{ route('logout') }}"
                method="POST">
                @csrf

                <button
                    type="submit"
                    class="btn w-100"
                    style="background:rgba(230,0,126,0.15);color:#E6007E;border:1px solid rgba(230,0,126,0.3);border-radius:8px;font-size:0.85rem;font-weight:500;">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </button>
            </form>
        </div>
    </nav>
</aside>