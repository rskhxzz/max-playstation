@extends('layouts.driver')
@section('title', 'Dashboard Driver')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div style="font-size:2rem;font-weight:800;color:#00D9FF;">{{ $readyToDeliver }}</div>
            <div class="text-muted small">Siap Diantar</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div style="font-size:2rem;font-weight:800;color:#10b981;">{{ $arrivedToday }}</div>
            <div class="text-muted small">Selesai Hari Ini</div>
        </div>
    </div>
</div>

{{-- Siap Diantar --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-truck me-2"></i>Siap Diantar</h6>
        <span class="badge" style="background:#00D9FF;color:#24252A;">{{ $deliveries->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Kode</th><th>Customer</th><th>Alamat</th><th>Jadwal</th><th>Paket</th><th>Bayar</th><th>Sisa</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($deliveries as $b)
                <tr>
                    <td><code>{{ $b->booking_code }}</code></td>
                    <td>
                        <div>{{ $b->customer?->full_name }}</div>
                        <small class="text-muted">{{ $b->customer?->phone_number }}</small>
                    </td>
                    <td style="max-width:180px;font-size:0.8rem;">{{ Str::limit($b->delivery_address, 60) }}</td>
                    <td style="font-size:0.82rem;">{{ \Carbon\Carbon::parse($b->rental_start_at)->timezone('Asia/Jakarta')->format('d/m H:i') }}</td>
                    <td>{{ $b->rentalPackage?->name }}</td>
                    <td><span class="badge bg-{{ $b->payment_status_badge }}">{{ $b->payment_status_label }}</span></td>
                    <td>
                        @if($b->remaining_amount > 0)
                            <span class="text-danger fw-600">Rp {{ number_format($b->remaining_amount,0,',','.') }}</span>
                        @else
                            <span class="text-success">Lunas</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('driver.bookings.show', $b->id) }}" class="btn btn-sm btn-primary" style="font-size:0.75rem;border-radius:6px;">Detail</a>
                            @if($b->latitude && $b->longitude)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $b->latitude }},{{ $b->longitude }}"
                               target="_blank" class="btn btn-sm btn-outline-success" style="font-size:0.75rem;border-radius:6px;" title="Buka Rute">
                                <i class="bi bi-map"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pesanan siap diantar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Riwayat --}}
<div class="card">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Pengantaran</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Kode</th><th>Customer</th><th>Paket</th><th>Selesai</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($history as $b)
                <tr>
                    <td><code>{{ $b->booking_code }}</code></td>
                    <td>{{ $b->customer?->full_name }}</td>
                    <td>{{ $b->rentalPackage?->name }}</td>
                    <td>{{ $b->arrived_at ? \Carbon\Carbon::parse($b->arrived_at)->timezone('Asia/Jakarta')->format('d/m H:i') : '—' }}</td>
                    <td><span class="badge bg-{{ $b->booking_status_badge }}">{{ $b->booking_status_label }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
