@extends('layouts.public')
@section('title', 'Pembayaran — ' . $booking->booking_code)

@push('styles')
<style>
.payment-card { max-width: 550px; margin: 0 auto; }
.countdown { font-size: 2rem; font-weight: 800; color: var(--clr-magenta); font-variant-numeric: tabular-nums; }
.countdown-label { font-size: 0.78rem; color: #6b7280; }
.snap-btn { background: var(--clr-magenta); color: #fff; border: none; border-radius: 10px; font-weight: 700; font-size: 1rem; padding: 0.85rem 2rem; width: 100%; }
.snap-btn:hover { background: #c4006b; }
.snap-btn:disabled { background: #9ca3af; cursor: not-allowed; }
.detail-row { display: flex; justify-content: space-between; padding: 0.4rem 0; font-size: 0.9rem; border-bottom: 1px solid #f0f0f0; }
.detail-row:last-child { border: none; }
.detail-row .label { color: #6b7280; }
.detail-row .value { font-weight: 600; }
</style>
@endpush

@section('content')
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="payment-card">
            <div class="text-center mb-4">
                <i class="bi bi-qr-code" style="font-size:3rem;color:var(--clr-cyan);"></i>
                <h2 style="font-size:1.6rem;font-weight:800;color:var(--clr-dark);" class="mt-2">Selesaikan Pembayaran</h2>
                <span class="badge text-bg-secondary" style="font-size:0.85rem;">{{ $booking->booking_code }}</span>
            </div>

            @include('components.alert')

            {{-- Status --}}
            @if($booking->booking_status !== 'pending_payment')
            <div class="alert @if($booking->booking_status === 'expired') alert-danger @else alert-success @endif">
                <i class="bi bi-info-circle me-2"></i>
                @if($booking->booking_status === 'expired')
                    Pembayaran sudah kedaluwarsa. Silakan buat pesanan baru.
                @else
                    Pembayaran berhasil! Status: {{ $booking->booking_status_label }}
                @endif
                <br><a href="{{ route('booking.status', $booking->booking_code) }}" class="fw-600">Lihat status pesanan →</a>
            </div>
            @endif

            <div class="card p-4 mb-4">
                <div class="detail-row">
                    <span class="label">Nama Customer</span>
                    <span class="value">{{ $booking->customer->full_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Paket</span>
                    <span class="value">{{ $booking->rentalPackage->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Tanggal Sewa</span>
                    <span class="value">{{ \Carbon\Carbon::parse($booking->rental_start_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Total Tagihan</span>
                    <span class="value" style="color:var(--clr-magenta);">Rp {{ number_format($booking->total_amount,0,',','.') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Pilihan Bayar</span>
                    <span class="value">{{ $booking->payment_option === 'deposit' ? 'DP' : 'Lunas' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label" style="font-weight:700;color:var(--clr-dark);">Bayar Sekarang</span>
                    <span class="value" style="font-size:1.1rem;color:var(--clr-magenta);">Rp {{ number_format($booking->initial_payment_amount,0,',','.') }}</span>
                </div>
                @if($booking->payment_option === 'deposit')
                <div class="detail-row">
                    <span class="label">Sisa Bayar (saat tiba)</span>
                    <span class="value">Rp {{ number_format($booking->remaining_amount,0,',','.') }}</span>
                </div>
                @endif
            </div>

            @if($booking->booking_status === 'pending_payment' && $payment)
                {{-- Countdown --}}
                @php $expiresAt = \Carbon\Carbon::parse($payment->expires_at); @endphp
                <div class="text-center mb-4">
                    <div class="countdown-label">Batas waktu pembayaran</div>
                    <div class="countdown" id="countdown">--:--</div>
                    <div class="countdown-label">{{ $expiresAt->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                </div>

                @if($clientKey)
                <button type="button" class="snap-btn mb-3" id="snapBtn">
                    <i class="bi bi-qr-code me-2"></i>Bayar dengan QRIS
                </button>
                @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Pembayaran belum dikonfigurasi. Isi <code>MIDTRANS_CLIENT_KEY</code> pada .env.
                </div>
                @endif
            @endif

            <div class="text-center mt-3">
                <a href="{{ route('booking.status', $booking->booking_code) }}" class="btn btn-outline-secondary" style="border-radius:8px;">
                    <i class="bi bi-arrow-clockwise me-1"></i>Cek Status Pesanan
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@if($booking->booking_status === 'pending_payment' && $payment && $clientKey)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
<script>
const snapToken   = '{{ $payment->qr_string }}';
const statusUrl   = '{{ route("booking.status", $booking->booking_code) }}';
const expiresAt   = new Date('{{ \Carbon\Carbon::parse($payment->expires_at)->toIso8601String() }}');

// Countdown
function updateCountdown() {
    const diff = Math.floor((expiresAt - Date.now()) / 1000);
    if (diff <= 0) {
        document.getElementById('countdown').textContent = '00:00';
        document.getElementById('snapBtn')?.setAttribute('disabled', 'disabled');
        return;
    }
    const m = String(Math.floor(diff / 60)).padStart(2, '0');
    const s = String(diff % 60).padStart(2, '0');
    document.getElementById('countdown').textContent = `${m}:${s}`;
}
setInterval(updateCountdown, 1000);
updateCountdown();

// Snap
document.getElementById('snapBtn')?.addEventListener('click', function() {
    if (!snapToken) { alert('Token pembayaran tidak tersedia.'); return; }
    window.snap.pay(snapToken, {
        onSuccess: function() { window.location.href = statusUrl + '?paid=1'; },
        onPending: function() { window.location.href = statusUrl; },
        onError:   function() { alert('Pembayaran gagal. Silakan coba lagi.'); },
        onClose:   function() { window.location.href = statusUrl; }
    });
});
</script>
@endif
@endpush
