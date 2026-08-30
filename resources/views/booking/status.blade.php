@extends('layouts.public')
@section('title', 'Status Pesanan — ' . $booking->booking_code)

@section('content')
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div style="max-width:600px;margin:0 auto;">
            <div class="text-center mb-4">
                <i class="bi bi-receipt" style="font-size:3rem;color:var(--clr-cyan);"></i>
                <h2 style="font-size:1.6rem;font-weight:800;color:var(--clr-dark);" class="mt-2">Status Pesanan</h2>
                <code style="font-size:1.1rem;color:var(--clr-magenta);">{{ $booking->booking_code }}</code>
            </div>

            <div class="card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Nama Customer</div>
                        <div class="fw-600">{{ $booking->customer->full_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Nomor HP</div>
                        <div class="fw-600">{{ $booking->customer->phone_number }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Paket</div>
                        <div class="fw-600">{{ $booking->rentalPackage->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Tanggal & Jam Sewa</div>
                        <div class="fw-600">{{ \Carbon\Carbon::parse($booking->rental_start_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Alamat Pengiriman</div>
                        <div class="fw-600">{{ $booking->delivery_address }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Total Tagihan</div>
                        <div class="fw-600" style="color:var(--clr-magenta);">Rp {{ number_format($booking->total_amount,0,',','.') }}</div>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Status Pesanan</div>
                        <span class="badge bg-{{ $booking->booking_status_badge }}" style="font-size:0.85rem;">
                            {{ $booking->booking_status_label }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Status Pembayaran</div>
                        <span class="badge bg-{{ $booking->payment_status_badge }}" style="font-size:0.85rem;">
                            {{ $booking->payment_status_label }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Sudah Dibayar</div>
                        <div class="fw-600">Rp {{ number_format($booking->total_paid,0,',','.') }}</div>
                    </div>
                    @if($booking->remaining_amount > 0)
                    <div class="col-md-6">
                        <div class="text-muted small">Sisa Tagihan</div>
                        <div class="fw-600 text-danger">Rp {{ number_format($booking->remaining_amount,0,',','.') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3 justify-content-center">
                @if($booking->booking_status === 'pending_payment')
                <a href="{{ route('booking.payment', $booking->booking_code) }}" class="btn btn-primary-custom">
                    <i class="bi bi-credit-card me-1"></i>Bayar Sekarang
                </a>
                @endif
                <a href="{{ route('booking.status', $booking->booking_code) }}" class="btn btn-cyan-outline">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Status
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-house me-1"></i>Beranda
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
