@extends('layouts.driver')
@section('title', 'Dashboard Driver')

@section('content')
<style>
    .driver-stat {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1.1rem 1.15rem;
        height: 100%;
    }

    .driver-stat .icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(230, 0, 126, .08);
        color: var(--clr-magenta);
        font-size: 1.2rem;
    }

    .driver-stat .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--clr-dark);
        line-height: 1.2;
    }

    .driver-stat .label {
        color: #6b7280;
        font-size: .78rem;
    }

    .section-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
    }

    .section-head {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .section-head h6 {
        margin: 0;
        font-weight: 700;
        color: var(--clr-dark);
    }

    .money-positive {
        color: #059669;
    }

    .money-warning {
        color: #d97706;
    }

    .filter-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: .9rem;
    }

    .table> :not(caption)>*>* {
        padding: .72rem .8rem;
    }

    @media (max-width: 767.98px) {
        .table {
            min-width: 900px;
        }
    }
</style>

<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="mb-1" style="font-weight:700;color:var(--clr-dark);">
            Dashboard Driver
        </h4>
        <div class="text-muted small">
            Kelola pengantaran, pickup, dan pendapatan Anda.
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="driver-stat">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="value">
                        {{ $availableBookings->count() }}
                    </div>
                    <div class="label mt-1">
                        Pesanan Siap Diantar
                    </div>
                </div>
                <div class="icon">
                    <i class="bi bi-truck"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="driver-stat">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="value">
                        {{ $myDeliveries->count() }}
                    </div>
                    <div class="label mt-1">
                        Pesanan Saya
                    </div>
                </div>
                <div class="icon" style="background:rgba(0,217,255,.1);color:#0891b2;">
                    <i class="bi bi-clipboard-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="driver-stat">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="value money-positive">
                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </div>
                    <div class="label mt-1">
                        Pendapatan Bersih
                    </div>
                </div>
                <div class="icon" style="background:rgba(16,185,129,.1);color:#059669;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="driver-stat">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="value">
                        {{ $totalDeliveries }}
                    </div>
                    <div class="label mt-1">
                        Pengantaran Selesai
                    </div>
                </div>
                <div class="icon" style="background:rgba(245,158,11,.1);color:#d97706;">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@if($pickupReady->count())
<div class="section-card mb-4" style="border-color:#f59e0b;">
    <div class="section-head d-flex justify-content-between align-items-center">
        <h6>
            <i class="bi bi-arrow-repeat me-2" style="color:#d97706;"></i>
            Pickup Perlu Dilakukan
        </h6>
        <span class="badge bg-warning text-dark">
            {{ $pickupReady->count() }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Unit</th>
                    <th>Sewa Berakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($pickupReady as $booking)
                <tr>
                    <td>
                        <code>{{ $booking->booking_code }}</code>
                    </td>

                    <td>
                        <div class="fw-semibold">
                            {{ $booking->customer?->full_name ?? '—' }}
                        </div>
                        <small class="text-muted">
                            {{ $booking->customer?->phone_number ?? '—' }}
                        </small>
                    </td>

                    <td>
                        {{ $booking->playstationUnit?->name ?? '—' }}
                    </td>

                    <td>
                        {{ $booking->rental_end_at
                            ? \Carbon\Carbon::parse($booking->rental_end_at)
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '—' }}
                    </td>

                    <td>
                        <a
                            href="{{ route('driver.bookings.show', $booking->id) }}"
                            class="btn btn-sm btn-primary"
                            style="border-radius:7px;">
                            <i class="bi bi-arrow-right-circle me-1"></i>
                            Pickup
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="section-card h-100">
            <div class="section-head d-flex justify-content-between align-items-center">
                <h6>
                    <i class="bi bi-truck me-2"></i>
                    Pesanan Siap Diantar
                </h6>

                <span
                    class="badge"
                    style="background:var(--clr-cyan);color:var(--clr-dark);">
                    {{ $availableBookings->count() }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Customer</th>
                            <th>Jadwal</th>
                            <th>Jarak</th>
                            <th>Pembayaran</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($availableBookings as $booking)
                        <tr>
                            <td>
                                <code>{{ $booking->booking_code }}</code>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $booking->customer?->full_name ?? '—' }}
                                </div>

                                <small class="text-muted">
                                    {{ \Illuminate\Support\Str::limit(
                                        $booking->delivery_address,
                                        34
                                    ) }}
                                </small>
                            </td>

                            <td>
                                {{ $booking->rental_start_at
                                    ? \Carbon\Carbon::parse($booking->rental_start_at)
                                        ->timezone('Asia/Jakarta')
                                        ->format('d/m H:i')
                                    : '—' }}
                            </td>

                            <td>
                                {{ number_format(
                                    (float) $booking->distance_km,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                                km
                            </td>

                            <td>
                                <span class="badge bg-{{ $booking->payment_status_badge }}">
                                    {{ $booking->payment_status_label }}
                                </span>
                            </td>

                            <td>
                                <a
                                    href="{{ route('driver.bookings.show', $booking->id) }}"
                                    class="btn btn-sm btn-primary"
                                    style="border-radius:7px;">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada pesanan yang tersedia.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="section-card h-100">
            <div class="section-head d-flex justify-content-between align-items-center">
                <h6>
                    <i class="bi bi-clipboard-check me-2"></i>
                    Pesanan Saya
                </h6>

                <span class="badge bg-secondary">
                    {{ $myDeliveries->count() }}
                </span>
            </div>

            <div class="p-3">
                @forelse($myDeliveries as $booking)
                <a
                    href="{{ route('driver.bookings.show', $booking->id) }}"
                    class="text-decoration-none text-reset d-block border rounded-3 p-3 mb-2">
                    <div class="d-flex justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">
                                <code>{{ $booking->booking_code }}</code>
                            </div>

                            <div class="small mt-1">
                                {{ $booking->customer?->full_name ?? '—' }}
                            </div>

                            <div class="small text-muted mt-1">
                                {{ $booking->rentalPackage?->name ?? '—' }}
                            </div>
                        </div>

                        <div class="text-end">
                            <span class="badge bg-secondary">
                                {{ $booking->vehicle_type === 'company'
                                    ? 'Motor Kantor'
                                    : 'Motor Pribadi' }}
                            </span>

                            <div class="small mt-2 money-positive">
                                Estimasi Rp {{ number_format(
                                    $booking->driver_income,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>
                        </div>
                    </div>
                </a>
                @empty
                <div class="text-center text-muted py-4">
                    Belum ada pesanan yang Anda terima.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="section-card mb-4">
    <div class="section-head">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
            <div>
                <h6>
                    <i class="bi bi-wallet2 me-2"></i>
                    Pendapatan Driver
                </h6>

                <div class="small text-muted mt-1">
                    Hanya pengantaran yang sudah berhasil dan memiliki dokumentasi.
                </div>
            </div>

            <div class="small text-muted">
                {{ $totalDeliveries }} pengantaran
            </div>
        </div>
    </div>

    <div class="p-3">
        <form method="GET" class="filter-box mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-sm-5 col-md-4">
                    <label class="form-label mb-1">
                        Start Date
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        value="{{ $startDate }}"
                        class="form-control form-control-sm">
                </div>

                <div class="col-sm-5 col-md-4">
                    <label class="form-label mb-1">
                        End Date
                    </label>

                    <input
                        type="date"
                        name="end_date"
                        value="{{ $endDate }}"
                        class="form-control form-control-sm">
                </div>

                <div class="col-sm-2 col-md-2">
                    <button
                        class="btn btn-primary btn-sm w-100"
                        type="submit">
                        <i class="bi bi-filter me-1"></i>
                        Filter
                    </button>
                </div>

                <div class="col-sm-12 col-md-2">
                    <a
                        href="{{ route('driver.dashboard') }}"
                        class="btn btn-outline-secondary btn-sm w-100">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="small text-muted">
                        Pendapatan Bersih
                    </div>

                    <div class="fs-5 fw-bold money-positive mt-1">
                        Rp {{ number_format(
                            $totalIncome,
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="small text-muted">
                        Pendapatan Kotor
                    </div>

                    <div class="fs-5 fw-bold mt-1">
                        Rp {{ number_format(
                            $totalGross,
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="small text-muted">
                        Potongan Bensin Motor Kantor
                    </div>

                    <div class="fs-5 fw-bold money-warning mt-1">
                        Rp {{ number_format(
                            $totalFuelDeduction,
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Jarak</th>
                        <th>Kendaraan</th>
                        <th>Dasar</th>
                        <th>Potongan</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($incomeRows as $booking)
                    <tr>
                        <td>
                            {{ $booking->delivered_at
                                ? \Carbon\Carbon::parse($booking->delivered_at)
                                    ->timezone('Asia/Jakarta')
                                    ->format('d/m/Y H:i')
                                : '—' }}
                        </td>

                        <td>
                            <code>{{ $booking->booking_code }}</code>
                        </td>

                        <td>
                            {{ $booking->customer?->full_name ?? '—' }}
                        </td>

                        <td>
                            {{ number_format(
                                (float) $booking->distance_km,
                                2,
                                ',',
                                '.'
                            ) }}
                            km
                        </td>

                        <td>
                            {{ $booking->vehicle_type === 'company'
                                ? 'Motor Kantor'
                                : 'Motor Pribadi' }}
                        </td>

                        <td>
                            Rp {{ number_format(
                                $booking->driver_fee,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>

                        <td>
                            {{ $booking->fuel_deduction > 0
                                ? 'Rp '.number_format(
                                    $booking->fuel_deduction,
                                    0,
                                    ',',
                                    '.'
                                )
                                : '—' }}
                        </td>

                        <td class="fw-semibold money-positive">
                            Rp {{ number_format(
                                $booking->driver_income,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada pendapatan pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="section-card">
    <div class="section-head d-flex justify-content-between align-items-center">
        <h6>
            <i class="bi bi-clock-history me-2"></i>
            Riwayat Pengantaran
        </h6>

        <span class="small text-muted">
            {{ $history->count() }} data
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Unit</th>
                    <th>Pengantaran</th>
                    <th>Pickup</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @forelse($history as $booking)
                <tr>
                    <td>
                        <code>{{ $booking->booking_code }}</code>
                    </td>

                    <td>
                        {{ $booking->customer?->full_name ?? '—' }}
                    </td>

                    <td>
                        {{ $booking->playstationUnit?->name ?? '—' }}
                    </td>

                    <td>
                        {{ $booking->delivered_at
                            ? \Carbon\Carbon::parse($booking->delivered_at)
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '—' }}
                    </td>

                    <td>
                        {{ $booking->picked_up_at
                            ? \Carbon\Carbon::parse($booking->picked_up_at)
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '—' }}
                    </td>

                    <td>
                        <span class="badge bg-{{ $booking->booking_status_badge }}">
                            {{ $booking->booking_status_label }}
                        </span>
                    </td>

                    <td>
                        <a
                            href="{{ route('driver.bookings.show', $booking->id) }}"
                            class="btn btn-sm btn-outline-secondary"
                            style="border-radius:7px;">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada riwayat pengantaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection