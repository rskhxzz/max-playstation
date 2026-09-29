@extends('layouts.admin')

@section(
'title',
$section === 'driver-income'
? 'Pendapatan Driver'
: 'Dashboard Admin'
)

@section(
'page-title',
$section === 'driver-income'
? 'Pendapatan Driver'
: 'Dashboard'
)

@section('content')

@if($section !== 'driver-income')

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div
                        class="stat-value"
                        style="color:var(--clr-magenta);">
                        {{ $totalOrdersToday }}
                    </div>

                    <div class="stat-label">
                        Pesanan Hari Ini
                    </div>
                </div>

                <span
                    class="stat-icon"
                    style="color:var(--clr-magenta);">
                    <i class="bi bi-bag-check"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div
                        class="stat-value"
                        style="color:#f59e0b;">
                        {{ $pendingPayment }}
                    </div>

                    <div class="stat-label">
                        Menunggu Bayar
                    </div>
                </div>

                <span
                    class="stat-icon"
                    style="color:#f59e0b;">
                    <i class="bi bi-clock"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div
                        class="stat-value"
                        style="color:var(--clr-cyan);">
                        {{ $readyToDeliver }}
                    </div>

                    <div class="stat-label">
                        Siap Diantar
                    </div>
                </div>

                <span
                    class="stat-icon"
                    style="color:var(--clr-cyan);">
                    <i class="bi bi-truck"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div
                        class="stat-value"
                        style="color:#10b981;">
                        {{ $arrived }}
                    </div>

                    <div class="stat-label">
                        Sudah Sampai
                    </div>
                </div>

                <span
                    class="stat-icon"
                    style="color:#10b981;">
                    <i class="bi bi-house-check"></i>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label mb-1">
                Pemasukan Hari Ini
            </div>

            <div
                class="stat-value"
                style="font-size:1.4rem;color:var(--clr-magenta);">
                Rp {{ number_format(
                    $incomeToday,
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label mb-1">
                Pemasukan Bulan Ini
            </div>

            <div
                class="stat-value"
                style="font-size:1.4rem;color:var(--clr-magenta);">
                Rp {{ number_format(
                    $incomeMonth,
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </div>
    </div>

    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-label mb-1">
                Unit Aktif
            </div>

            <div
                class="stat-value"
                style="color:#10b981;">
                {{ $activeUnits }}
            </div>
        </div>
    </div>

    <div class="col-md-2 col-6">
        <div class="stat-card">
            <div class="stat-label mb-1">
                Maintenance
            </div>

            <div
                class="stat-value"
                style="color:#f59e0b;">
                {{ $maintenanceUnits }}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h6>
            <i class="bi bi-list-ul me-2"></i>
            Pesanan Terbaru
        </h6>

        <a
            href="{{ route('admin.bookings.index') }}"
            class="btn btn-sm"
            style="background:rgba(255,255,255,0.15);color:#fff;border-radius:6px;font-size:0.78rem;">
            Lihat Semua
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Paket</th>
                    <th>Driver</th>
                    <th>Jadwal</th>
                    <th>Total</th>
                    <th>Status Pesanan</th>
                    <th>Status Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($recentBookings as $b)
                <tr>
                    <td>
                        <code>
                            {{ $b->booking_code }}
                        </code>
                    </td>

                    <td>
                        {{ $b->customer?->full_name ?? '—' }}
                    </td>

                    <td>
                        {{ $b->rentalPackage?->name ?? '—' }}
                    </td>

                    <td>
                        {{ $b->driver?->name ?? '—' }}
                    </td>

                    <td>
                        {{ $b->rental_start_at
                                ? $b->rental_start_at
                                    ->timezone('Asia/Jakarta')
                                    ->format('d/m H:i')
                                : '—' }}
                    </td>

                    <td>
                        Rp {{ number_format(
                                $b->total_amount,
                                0,
                                ',',
                                '.'
                            ) }}
                    </td>

                    <td>
                        <span
                            class="badge bg-{{ $b->booking_status_badge }}">
                            {{ $b->booking_status_label }}
                        </span>
                    </td>

                    <td>
                        <span
                            class="badge bg-{{ $b->payment_status_badge }}">
                            {{ $b->payment_status_label }}
                        </span>
                    </td>

                    <td>
                        <a
                            href="{{ route(
                                    'admin.bookings.show',
                                    $b->id
                                ) }}"
                            class="btn btn-sm btn-outline-primary"
                            style="font-size:0.75rem;border-radius:6px;">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td
                        colspan="9"
                        class="text-center text-muted py-4">
                        Belum ada pesanan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-6">
        <div class="stat-card">
            <div class="stat-label mb-1">
                Pendapatan Kotor
            </div>

            <div
                class="stat-value"
                style="font-size:1.25rem;color:var(--clr-magenta);">
                Rp {{ number_format(
                    $driverIncomeTotals['gross'],
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-6">
        <div class="stat-card">
            <div class="stat-label mb-1">
                Potongan Bensin
            </div>

            <div
                class="stat-value"
                style="font-size:1.25rem;color:#f59e0b;">
                Rp {{ number_format(
                    $driverIncomeTotals['fuel'],
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-6">
        <div class="stat-card">
            <div class="stat-label mb-1">
                Pendapatan Bersih
            </div>

            <div
                class="stat-value"
                style="font-size:1.25rem;color:#10b981;">
                Rp {{ number_format(
                    $driverIncomeTotals['net'],
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-6">
        <div class="stat-card">
            <div class="stat-label mb-1">
                Pengantaran Selesai
            </div>

            <div
                class="stat-value"
                style="font-size:1.25rem;color:var(--clr-cyan);">
                {{ $driverIncomeTotals['deliveries'] }}
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-calendar3 me-2"></i>
            Filter Periode Pendapatan
        </h6>
    </div>

    <div class="card-body">
        <form
            method="GET"
            class="row g-3 align-items-end">
            <input
                type="hidden"
                name="section"
                value="driver-income">

            <div class="col-md-4">
                <label class="form-label">
                    Start Date
                </label>

                <input
                    type="date"
                    name="start_date"
                    value="{{ $startDate }}"
                    class="form-control"
                    required>
            </div>

            <div class="col-md-4">
                <label class="form-label">
                    End Date
                </label>

                <input
                    type="date"
                    name="end_date"
                    value="{{ $endDate }}"
                    class="form-control"
                    required>
            </div>

            <div class="col-md-2">
                <button
                    type="submit"
                    class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i>
                    Filter
                </button>
            </div>

            <div class="col-md-2">
                <a
                    href="{{ route(
                        'admin.dashboard',
                        [
                            'section' => 'driver-income',
                        ]
                    ) }}"
                    class="btn btn-outline-secondary w-100">
                    Reset
                </a>
            </div>
        </form>

        <div class="small text-muted mt-3">
            Periode laporan:
            <strong>{{ $startDate }}</strong>
            sampai
            <strong>{{ $endDate }}</strong>.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-people me-2"></i>
            Pendapatan Driver
        </h6>

        <span class="small text-muted">
            {{ $driverIncome->count() }} driver
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Driver</th>
                    <th>Pengantaran</th>
                    <th>Pendapatan Kotor</th>
                    <th>Potongan Bensin</th>
                    <th>Pendapatan Bersih</th>
                </tr>
            </thead>

            <tbody>
                @forelse($driverIncome as $index => $row)
                <tr>
                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td>
                        <div class="fw-semibold">
                            {{ $row['driver_name'] }}
                        </div>
                    </td>

                    <td>
                        {{ $row['deliveries'] }}
                        kali
                    </td>

                    <td>
                        Rp {{ number_format(
                                $row['gross'],
                                0,
                                ',',
                                '.'
                            ) }}
                    </td>

                    <td class="text-warning">
                        Rp {{ number_format(
                                $row['fuel'],
                                0,
                                ',',
                                '.'
                            ) }}
                    </td>

                    <td class="fw-semibold text-success">
                        Rp {{ number_format(
                                $row['net'],
                                0,
                                ',',
                                '.'
                            ) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td
                        colspan="6"
                        class="text-center text-muted py-5">
                        Tidak ada pendapatan driver pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>

            @if($driverIncome->count())
            <tfoot>
                <tr style="background:#f8fafc;">
                    <th colspan="2">
                        TOTAL
                    </th>

                    <th>
                        {{ $driverIncomeTotals['deliveries'] }}
                        kali
                    </th>

                    <th>
                        Rp {{ number_format(
                                $driverIncomeTotals['gross'],
                                0,
                                ',',
                                '.'
                            ) }}
                    </th>

                    <th class="text-warning">
                        Rp {{ number_format(
                                $driverIncomeTotals['fuel'],
                                0,
                                ',',
                                '.'
                            ) }}
                    </th>

                    <th class="text-success">
                        Rp {{ number_format(
                                $driverIncomeTotals['net'],
                                0,
                                ',',
                                '.'
                            ) }}
                    </th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endif

@endsection