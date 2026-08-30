@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" style="color:var(--clr-magenta);">{{ $totalOrdersToday }}</div>
                    <div class="stat-label">Pesanan Hari Ini</div>
                </div>
                <span class="stat-icon" style="color:var(--clr-magenta);"><i class="bi bi-bag-check"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" style="color:#f59e0b;">{{ $pendingPayment }}</div>
                    <div class="stat-label">Menunggu Bayar</div>
                </div>
                <span class="stat-icon" style="color:#f59e0b;"><i class="bi bi-clock"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" style="color:var(--clr-cyan);">{{ $readyToDeliver }}</div>
                    <div class="stat-label">Siap Diantar</div>
                </div>
                <span class="stat-icon" style="color:var(--clr-cyan);"><i class="bi bi-truck"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" style="color:#10b981;">{{ $arrived }}</div>
                    <div class="stat-label">Sudah Sampai</div>
                </div>
                <span class="stat-icon" style="color:#10b981;"><i class="bi bi-house-check"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label mb-1">Pemasukan Hari Ini</div>
            <div class="stat-value" style="font-size:1.4rem;color:var(--clr-magenta);">Rp {{ number_format($incomeToday,0,',','.') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label mb-1">Pemasukan Bulan Ini</div>
            <div class="stat-value" style="font-size:1.4rem;color:var(--clr-magenta);">Rp {{ number_format($incomeMonth,0,',','.') }}</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-label mb-1">Unit Aktif</div>
            <div class="stat-value" style="color:#10b981;">{{ $activeUnits }}</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-label mb-1">Maintenance</div>
            <div class="stat-value" style="color:#f59e0b;">{{ $maintenanceUnits }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h6><i class="bi bi-list-ul me-2"></i>Pesanan Terbaru</h6>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.15);color:#fff;border-radius:6px;font-size:0.78rem;">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Kode</th><th>Customer</th><th>Paket</th><th>Jadwal</th>
                    <th>Total</th><th>Status Pesanan</th><th>Status Bayar</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $b)
                <tr>
                    <td><code>{{ $b->booking_code }}</code></td>
                    <td>{{ $b->customer?->full_name ?? '—' }}</td>
                    <td>{{ $b->rentalPackage?->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($b->rental_start_at)->timezone('Asia/Jakarta')->format('d/m H:i') }}</td>
                    <td>Rp {{ number_format($b->total_amount,0,',','.') }}</td>
                    <td><span class="badge bg-{{ $b->booking_status_badge }}">{{ $b->booking_status_label }}</span></td>
                    <td><span class="badge bg-{{ $b->payment_status_badge }}">{{ $b->payment_status_label }}</span></td>
                    <td><a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;border-radius:6px;">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
