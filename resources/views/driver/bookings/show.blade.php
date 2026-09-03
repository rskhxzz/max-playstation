@extends('layouts.driver')
@section('title', 'Detail Pesanan')

@section('content')
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('driver.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    @if($booking->latitude && $booking->longitude)
    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $booking->latitude }},{{ $booking->longitude }}"
       target="_blank" class="btn btn-sm btn-success" style="border-radius:8px;">
        <i class="bi bi-map me-1"></i>Buka Rute
    </a>
    @endif
</div>

@include('components.alert')

<div class="row g-3">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-person me-2"></i>Data Customer</h6></div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="text-muted small">Nama</div>
                    <div class="fw-600 fs-5">{{ $booking->customer?->full_name }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Nomor WhatsApp</div>
                    <a href="https://wa.me/{{ preg_replace('/\D/','',$booking->customer?->phone_number) }}"
                       target="_blank" class="btn btn-sm btn-success mt-1" style="border-radius:6px;">
                        <i class="bi bi-whatsapp me-1"></i>{{ $booking->customer?->phone_number }}
                    </a>
                </div>
                <div>
                    <div class="text-muted small">Alamat Pengiriman</div>
                    <div>{{ $booking->delivery_address }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-controller me-2"></i>Detail Sewa</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-muted small">Paket</div>
                        <div class="fw-600">{{ $booking->rentalPackage?->name }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Unit</div>
                        <div class="fw-600">{{ $booking->playstationUnit?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Mulai</div>
                        <div class="fw-600">{{ \Carbon\Carbon::parse($booking->rental_start_at)->timezone('Asia/Jakarta')->format('d/m H:i') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Selesai</div>
                        <div class="fw-600">{{ \Carbon\Carbon::parse($booking->rental_end_at)->timezone('Asia/Jakarta')->format('d/m H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-cash me-2"></i>Ringkasan Pembayaran</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Tagihan</span><strong>Rp {{ number_format($booking->total_amount,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Sudah Dibayar</span><strong class="text-success">Rp {{ number_format($booking->total_paid,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Sisa Tagihan</span>
                    <strong class="{{ $booking->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($booking->remaining_amount,0,',','.') }}
                    </strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Status Pembayaran</span>
                    <span class="badge bg-{{ $booking->payment_status_badge }}" style="font-size:0.85rem;">{{ $booking->payment_status_label }}</span>
                </div>
            </div>
        </div>

        {{-- Pelunasan: pilih metode (hanya tampil jika masih delivered & partial) --}}
        @if($booking->payment_status === 'partial' && $booking->booking_status === 'delivered')
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Pelunasan Sisa Tagihan
                    <span class="ms-2" style="color:var(--clr-cyan);font-size:0.85rem;">
                        Rp {{ number_format($booking->remaining_amount,0,',','.') }}
                    </span>
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">Pilih metode pelunasan yang digunakan customer:</p>

                {{-- Tab pilihan metode --}}
                <div class="d-flex gap-2 mb-3" id="methodTabs">
                    <button type="button" class="btn btn-sm flex-fill method-tab active" id="tabQris"
                            style="border-radius:8px;border:2px solid var(--clr-cyan);background:rgba(0,217,255,0.1);color:var(--clr-dark);font-weight:600;">
                        <i class="bi bi-qr-code me-1"></i>QRIS
                    </button>
                    <button type="button" class="btn btn-sm flex-fill method-tab" id="tabCash"
                            style="border-radius:8px;border:2px solid #d1d5db;background:#fff;color:#374151;font-weight:600;">
                        <i class="bi bi-cash-coin me-1"></i>Tunai (Cash)
                    </button>
                </div>

                {{-- Panel QRIS --}}
                <div id="panelQris">
                    @if($snapToken && config('services.midtrans.client_key'))
                        <button type="button" class="btn btn-primary w-100" id="snapRemBtn" style="border-radius:8px;">
                            <i class="bi bi-qr-code me-2"></i>Tampilkan QRIS Pelunasan
                        </button>
                    @else
                        <button type="button" class="btn btn-primary w-100" id="createRemBtn" style="border-radius:8px;">
                            <i class="bi bi-qr-code me-2"></i>Buat QRIS Pelunasan
                        </button>
                    @endif
                    <div id="remStatus" class="small text-muted mt-2"></div>
                </div>

                {{-- Panel Cash --}}
                <div id="panelCash" style="display:none;">
                    <div class="alert alert-warning py-2 mb-3" style="font-size:0.83rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Pastikan Anda sudah <strong>menerima uang tunai</strong> sebesar
                        <strong>Rp {{ number_format($booking->remaining_amount,0,',','.') }}</strong>
                        dari customer sebelum menekan tombol di bawah.
                    </div>
                    <button type="button" class="btn w-100 fw-600" id="cashSettleBtn"
                            style="background:#f59e0b;color:#fff;border-radius:8px;border:none;">
                        <i class="bi bi-cash-coin me-2"></i>Konfirmasi Terima Tunai
                    </button>
                    <div id="cashStatus" class="small mt-2"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- Selesai Mengantar / Status Arrived --}}
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-check-circle me-2"></i>Status Pengantaran</h6></div>
            <div class="card-body">
                @if($booking->booking_status === 'arrived')
                    <div class="alert alert-success py-2" style="font-size:0.85rem;">
                        <i class="bi bi-house-check me-1"></i>
                        Pesanan sudah ditandai <strong>Sudah Sampai</strong> pada
                        {{ $booking->arrived_at ? \Carbon\Carbon::parse($booking->arrived_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '—' }}.
                    </div>
                @elseif($booking->payment_status !== 'paid')
                    <div class="alert alert-warning py-2" style="font-size:0.85rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Selesaikan pelunasan terlebih dahulu sebelum menandai selesai.
                    </div>
                @else
                    <p class="small text-muted mb-3">Tekan tombol ini setelah PlayStation diterima customer.</p>
                    <form action="{{ route('driver.bookings.arrived', $booking->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" style="border-radius:8px;font-weight:600;"
                                onclick="return confirm('Tandai pesanan sudah sampai?')">
                            <i class="bi bi-house-check me-2"></i>Selesai Mengantar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(config('services.midtrans.client_key'))
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
@endif
<script>
const bookingId = '{{ $booking->id }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ─── Tab switch: QRIS / Cash ──────────────────────────────────────────────────
document.getElementById('tabQris')?.addEventListener('click', function() {
    document.getElementById('panelQris').style.display = '';
    document.getElementById('panelCash').style.display = 'none';
    this.style.cssText = 'border-radius:8px;border:2px solid var(--clr-cyan);background:rgba(0,217,255,0.1);color:#24252A;font-weight:600;';
    document.getElementById('tabCash').style.cssText = 'border-radius:8px;border:2px solid #d1d5db;background:#fff;color:#374151;font-weight:600;';
});

document.getElementById('tabCash')?.addEventListener('click', function() {
    document.getElementById('panelCash').style.display = '';
    document.getElementById('panelQris').style.display = 'none';
    this.style.cssText = 'border-radius:8px;border:2px solid #f59e0b;background:rgba(245,158,11,0.1);color:#24252A;font-weight:600;';
    document.getElementById('tabQris').style.cssText = 'border-radius:8px;border:2px solid #d1d5db;background:#fff;color:#374151;font-weight:600;';
});

// ─── QRIS: show existing snap token ──────────────────────────────────────────
document.getElementById('snapRemBtn')?.addEventListener('click', function() {
    const token = '{{ $snapToken }}';
    if (token) {
        window.snap?.pay(token, {
            onSuccess: () => location.reload(),
            onPending: () => location.reload(),
            onClose:   () => {}
        });
    }
});

// ─── QRIS: create new snap token ─────────────────────────────────────────────
document.getElementById('createRemBtn')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membuat QRIS...';

    fetch(`/driver/pesanan/${bookingId}/qris-pelunasan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.snap_token) {
            window.snap?.pay(data.snap_token, {
                onSuccess: () => location.reload(),
                onPending: () => location.reload(),
                onClose:   () => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-qr-code me-2"></i>Buat QRIS Pelunasan';
                }
            });
        } else {
            document.getElementById('remStatus').innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>${data.message}</span>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-qr-code me-2"></i>Buat QRIS Pelunasan';
        }
    })
    .catch(() => {
        document.getElementById('remStatus').innerHTML = '<span class="text-danger">Gagal terhubung ke server.</span>';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-qr-code me-2"></i>Buat QRIS Pelunasan';
    });
});

// ─── Cash: konfirmasi terima tunai ────────────────────────────────────────────
document.getElementById('cashSettleBtn')?.addEventListener('click', function() {
    if (!confirm('Konfirmasi: Anda sudah menerima uang tunai dari customer dan ingin mencatat pelunasan ini?')) {
        return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

    fetch(`/driver/pesanan/${bookingId}/cash-pelunasan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cashStatus').innerHTML =
                '<span class="text-success"><i class="bi bi-check-circle me-1"></i>' + data.message + '</span>';
            // Reload halaman setelah singkat agar tombol "Selesai Mengantar" aktif
            setTimeout(() => location.reload(), 1200);
        } else {
            document.getElementById('cashStatus').innerHTML =
                `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>${data.message}</span>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cash-coin me-2"></i>Konfirmasi Terima Tunai';
        }
    })
    .catch(() => {
        document.getElementById('cashStatus').innerHTML = '<span class="text-danger">Gagal terhubung ke server.</span>';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cash-coin me-2"></i>Konfirmasi Terima Tunai';
    });
});
</script>
@endpush
