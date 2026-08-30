@extends('layouts.admin')
@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')

@section('content')
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@include('components.alert')

<div class="row g-3">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-person me-2"></i>Data Customer</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="text-muted small">Nama Lengkap</div>
                        <div class="fw-600">{{ $booking->customer?->full_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Nomor HP</div>
                        <div class="fw-600">{{ $booking->customer?->phone_number }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Alamat Pengiriman</div>
                        <div class="fw-600">{{ $booking->delivery_address }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Koordinat</div>
                        <div class="small text-muted">{{ $booking->latitude }}, {{ $booking->longitude }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Jarak</div>
                        <div class="fw-600">{{ $booking->distance_km }} km</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-controller me-2"></i>Detail Sewa</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="text-muted small">Paket</div>
                        <div class="fw-600">{{ $booking->rentalPackage?->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Unit PlayStation</div>
                        <div class="fw-600">{{ $booking->playstationUnit?->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Mulai Sewa</div>
                        <div class="fw-600">{{ \Carbon\Carbon::parse($booking->rental_start_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Selesai Sewa</div>
                        <div class="fw-600">{{ \Carbon\Carbon::parse($booking->rental_end_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</div>
                    </div>
                    @if($booking->customer_notes)
                    <div class="col-12">
                        <div class="text-muted small">Catatan</div>
                        <div>{{ $booking->customer_notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payments --}}
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-receipt me-2"></i>Riwayat Pembayaran</h6></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Kode</th><th>Tipe</th><th>Nominal</th><th>Status</th><th>Tanggal</th></tr>
                    </thead>
                    <tbody>
                        @forelse($booking->payments as $p)
                        <tr>
                            <td><code style="font-size:0.78rem;">{{ $p->payment_code }}</code></td>
                            <td>{{ $p->payment_type === 'initial' ? 'Awal' : 'Pelunasan' }}</td>
                            <td>Rp {{ number_format($p->requested_amount,0,',','.') }}</td>
                            <td><span class="badge bg-{{ $p->status === 'succeeded' ? 'success' : ($p->status === 'pending' ? 'warning' : 'danger') }}">{{ $p->status_label }}</span></td>
                            <td>{{ $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->timezone('Asia/Jakarta')->format('d/m H:i') : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-cash-stack me-2"></i>Ringkasan Harga</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Harga Paket</span><strong>Rp {{ number_format($booking->package_price,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Biaya Pengiriman</span><strong>Rp {{ number_format($booking->delivery_fee,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Diskon</span><strong>Rp {{ number_format($booking->discount_amount,0,',','.') }}</strong></div>
                <hr>
                <div class="d-flex justify-content-between mb-2"><span class="fw-700">Total Tagihan</span><strong style="color:var(--clr-magenta);">Rp {{ number_format($booking->total_amount,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Sudah Dibayar</span><strong class="text-success">Rp {{ number_format($booking->total_paid,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Sisa Tagihan</span><strong class="text-danger">Rp {{ number_format($booking->remaining_amount,0,',','.') }}</strong></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-info-circle me-2"></i>Status</h6></div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted small">Status Pesanan</span><br>
                    <span class="badge bg-{{ $booking->booking_status_badge }}" style="font-size:0.9rem;">{{ $booking->booking_status_label }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small">Status Pembayaran</span><br>
                    <span class="badge bg-{{ $booking->payment_status_badge }}" style="font-size:0.9rem;">{{ $booking->payment_status_label }}</span>
                </div>
                <div>
                    <span class="text-muted small">Dibuat</span><br>
                    <span>{{ \Carbon\Carbon::parse($booking->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        @if(in_array($booking->booking_status, ['pending_payment','delivered']))
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-x-circle me-2"></i>Batalkan Pesanan</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" style="font-size:0.85rem;">Alasan Pembatalan</label>
                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Opsional">
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm" style="border-radius:6px;"
                            onclick="return confirm('Yakin batalkan pesanan ini?')">
                        <i class="bi bi-x-circle me-1"></i>Batalkan Pesanan
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
