@extends('layouts.admin')
@section('title', 'Detail Pembayaran')
@section('page-title', 'Detail Pembayaran')

@section('content')
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    @if($payment->booking_id)
    <a href="{{ route('admin.bookings.show', $payment->booking_id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
        <i class="bi bi-bag me-1"></i>Lihat Pesanan
    </a>
    @endif
</div>


<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Detail Pembayaran</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0" style="font-size:0.88rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width:45%;">Kode Pembayaran</td>
                            <td><code>{{ $payment->payment_code }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tipe Pembayaran</td>
                            <td>{{ $payment->payment_type === 'initial' ? 'Pembayaran Awal' : 'Pelunasan' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Metode</td>
                            <td>{{ strtoupper($payment->payment_method ?? '—') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Provider</td>
                            <td>{{ ucfirst($payment->provider ?? '—') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nominal Tagihan</td>
                            <td class="fw-600">Rp {{ number_format($payment->requested_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nominal Dibayar</td>
                            <td class="fw-600 text-success">Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge bg-{{ $payment->status === 'succeeded' ? 'success' : ($payment->status === 'pending' ? 'warning' : ($payment->status === 'expired' ? 'dark' : 'danger')) }}" style="font-size:0.82rem;">
                                    {{ $payment->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Batas Waktu</td>
                            <td>{{ $payment->expires_at ? \Carbon\Carbon::parse($payment->expires_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibayar Pada</td>
                            <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '—' }}</td>
                        </tr>
                        @if($payment->external_payment_id)
                        <tr>
                            <td class="text-muted">Transaction ID</td>
                            <td><code style="font-size:0.78rem;">{{ $payment->external_payment_id }}</code></td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Dibuat</td>
                            <td>{{ \Carbon\Carbon::parse($payment->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-bag me-2"></i>Informasi Pesanan</h6></div>
            <div class="card-body">
                @if($payment->booking)
                <table class="table table-borderless mb-0" style="font-size:0.88rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width:45%;">Kode Booking</td>
                            <td><code>{{ $payment->booking->booking_code }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Customer</td>
                            <td class="fw-600">{{ $payment->booking->customer?->full_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No HP</td>
                            <td>{{ $payment->booking->customer?->phone_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Paket</td>
                            <td>{{ $payment->booking->rentalPackage?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jadwal Sewa</td>
                            <td>{{ \Carbon\Carbon::parse($payment->booking->rental_start_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Tagihan</td>
                            <td class="fw-600" style="color:var(--clr-magenta);">Rp {{ number_format($payment->booking->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Pesanan</td>
                            <td><span class="badge bg-{{ $payment->booking->booking_status_badge }}">{{ $payment->booking->booking_status_label }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Bayar</td>
                            <td><span class="badge bg-{{ $payment->booking->payment_status_badge }}">{{ $payment->booking->payment_status_label }}</span></td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p class="text-muted">Data pesanan tidak ditemukan.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
