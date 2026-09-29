@extends('layouts.admin')
@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <a
        href="{{ route('admin.bookings.index') }}"
        class="btn btn-sm btn-outline-secondary"
        style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>
        Kembali
    </a>

    <div class="d-flex gap-2">
        <span class="badge bg-{{ $booking->booking_status_badge }} fs-6">
            {{ $booking->booking_status_label }}
        </span>

        <span class="badge bg-{{ $booking->payment_status_badge }} fs-6">
            {{ $booking->payment_status_label }}
        </span>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-person me-2"></i>
                    Data Customer
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">
                            Nama Lengkap
                        </div>

                        <div class="fw-semibold">
                            {{ $booking->customer?->full_name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">
                            Nomor HP
                        </div>

                        <div class="fw-semibold">
                            {{ $booking->customer?->phone_number ?? '—' }}
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-muted small">
                            Alamat Pengiriman
                        </div>

                        <div>
                            {{ $booking->delivery_address ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">
                            Koordinat
                        </div>

                        <div class="small">
                            {{ $booking->latitude ?? '—' }},
                            {{ $booking->longitude ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">
                            Jarak
                        </div>

                        <div class="fw-semibold">
                            {{ number_format(
                                (float) $booking->distance_km,
                                2,
                                ',',
                                '.'
                            ) }}
                            km
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-controller me-2"></i>
                    Detail Sewa
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">
                            Paket
                        </div>

                        <div class="fw-semibold">
                            {{ $booking->rentalPackage?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">
                            Unit PlayStation
                        </div>

                        <div class="fw-semibold">
                            {{ $booking->playstationUnit?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">
                            Mulai Sewa
                        </div>

                        <div class="fw-semibold">
                            {{ $booking->rental_start_at
                                ? $booking->rental_start_at
                                    ->timezone('Asia/Jakarta')
                                    ->format('d M Y, H:i')
                                    . ' WIB'
                                : '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">
                            Selesai Sewa
                        </div>

                        <div class="fw-semibold">
                            {{ $booking->rental_end_at
                                ? $booking->rental_end_at
                                    ->timezone('Asia/Jakarta')
                                    ->format('d M Y, H:i')
                                    . ' WIB'
                                : '—' }}
                        </div>
                    </div>

                    @if($booking->customer_notes)
                    <div class="col-12">
                        <div class="text-muted small">
                            Catatan
                        </div>

                        <div>
                            {{ $booking->customer_notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-receipt me-2"></i>
                    Riwayat Pembayaran
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tipe</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($booking->payments as $payment)
                        <tr>
                            <td>
                                <code>
                                    {{ $payment->payment_code }}
                                </code>
                            </td>

                            <td>
                                @if($payment->payment_type === 'initial')
                                Pembayaran Awal
                                @else
                                Pelunasan
                                @endif
                            </td>

                            <td>
                                Rp {{ number_format(
                                        $payment->requested_amount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                            </td>

                            <td>
                                @if($payment->status === 'succeeded')
                                <span class="badge bg-success">
                                    Berhasil
                                </span>
                                @elseif($payment->status === 'pending')
                                <span class="badge bg-warning text-dark">
                                    Menunggu
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    {{ $payment->status_label }}
                                </span>
                                @endif
                            </td>

                            <td>
                                {{ $payment->paid_at
                                        ? $payment->paid_at
                                            ->timezone('Asia/Jakarta')
                                            ->format('d/m/Y H:i')
                                        : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td
                                colspan="5"
                                class="text-center text-muted py-3">
                                Belum ada pembayaran.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-truck me-2"></i>
                    Penugasan Driver
                </h6>
            </div>

            <div class="card-body">
                @if($booking->driver)
                <div class="border rounded-3 p-3 mb-3">
                    <div class="text-muted small">
                        Driver Saat Ini
                    </div>

                    <div class="fw-semibold fs-5">
                        {{ $booking->driver->name }}
                    </div>
                </div>
                @else
                <div class="alert alert-light border small">
                    Belum ada driver yang menerima atau ditugaskan.
                </div>
                @endif

                @if(
                $booking->booking_status === 'delivered'
                && !$booking->delivery_started_at
                )
                <form
                    action="{{ route(
                            'admin.bookings.assign-driver',
                            $booking->id
                        ) }}"
                    method="POST"
                    class="border rounded-3 p-3 bg-light">
                    @csrf

                    @if($booking->driver)
                    <label class="form-label">
                        Ganti Driver
                    </label>
                    @else
                    <label class="form-label">
                        Tugaskan Driver
                    </label>
                    @endif

                    <select
                        name="driver_id"
                        class="form-select mb-3"
                        required>
                        <option value="">
                            Pilih driver...
                        </option>

                        @foreach($drivers as $driver)
                        <option
                            value="{{ $driver->id }}"
                            {{ $booking->driver_id === $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                        @endforeach
                    </select>

                    <label class="form-label">
                        Kendaraan
                    </label>

                    <select
                        name="vehicle_type"
                        class="form-select mb-3"
                        required>
                        <option
                            value="personal"
                            {{ $booking->vehicle_type === 'personal' ? 'selected' : '' }}>
                            Motor Pribadi
                        </option>

                        <option
                            value="company"
                            {{ $booking->vehicle_type === 'company' ? 'selected' : '' }}>
                            Motor Kantor
                        </option>
                    </select>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        style="border-radius:8px;">
                        <i class="bi bi-person-check me-1"></i>
                        Simpan Penugasan
                    </button>
                </form>

                <div class="small text-muted mt-2">
                    Driver yang melakukan pengantaran menjadi driver pickup.
                </div>
                @elseif($booking->driver)
                <div class="small text-muted">
                    Driver sudah menerima tugas pengantaran.
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-wallet2 me-2"></i>
                    Pendapatan Driver
                </h6>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Ongkir Customer
                    </span>

                    <strong>
                        Rp {{ number_format(
                            $booking->delivery_fee,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Pendapatan Kotor
                    </span>

                    <strong>
                        Rp {{ number_format(
                            $booking->driver_fee,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Potongan Bensin
                    </span>

                    <strong class="text-danger">
                        Rp {{ number_format(
                            $booking->fuel_deduction,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <span class="fw-semibold">
                        Pendapatan Bersih
                    </span>

                    <strong class="text-success">
                        Rp {{ number_format(
                            $booking->driver_income,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                @if($booking->vehicle_type)
                <div class="small text-muted mt-2">
                    Kendaraan:
                    @if($booking->vehicle_type === 'personal')
                    Motor Pribadi
                    @else
                    Motor Kantor
                    @endif
                </div>
                @endif
            </div>
        </div>

        @if($booking->delivery_photo_path)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-camera me-2"></i>
                    Dokumentasi Pengantaran
                </h6>
            </div>

            <div class="card-body">
                <a
                    href="{{ route(
                            'admin.bookings.photo',
                            $booking->id
                        ) }}"
                    target="_blank">
                    <img
                        src="{{ route(
                                'admin.bookings.photo',
                                $booking->id
                            ) }}"
                        alt="Dokumentasi customer dan unit PS"
                        class="img-fluid rounded border"
                        style="max-height:360px;width:100%;object-fit:cover;">
                </a>

                <div class="small text-muted mt-2">
                    Diambil
                    {{ $booking->delivery_photo_taken_at
                            ? $booking->delivery_photo_taken_at
                                ->timezone('Asia/Jakarta')
                                ->format('d M Y, H:i')
                            : '—' }}
                    WIB
                </div>
            </div>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Timeline
                </h6>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Driver Ditugaskan
                    </span>

                    <span>
                        {{ $booking->driver_assigned_at
                            ? $booking->driver_assigned_at
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '—' }}
                    </span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Mulai Pengantaran
                    </span>

                    <span>
                        {{ $booking->delivery_started_at
                            ? $booking->delivery_started_at
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '—' }}
                    </span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Selesai Antar
                    </span>

                    <span>
                        {{ $booking->delivered_at
                            ? $booking->delivered_at
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '—' }}
                    </span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Pickup Dimulai
                    </span>

                    <span>
                        {{ $booking->pickup_started_at
                            ? $booking->pickup_started_at
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '—' }}
                    </span>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">
                        PS Diambil
                    </span>

                    <span>
                        {{ $booking->picked_up_at
                            ? $booking->picked_up_at
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '—' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-cash-stack me-2"></i>
                    Ringkasan Tagihan
                </h6>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Harga Paket
                    </span>

                    <strong>
                        Rp {{ number_format(
                            $booking->package_price,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Biaya Pengiriman
                    </span>

                    <strong>
                        Rp {{ number_format(
                            $booking->delivery_fee,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">
                        Diskon
                    </span>

                    <strong>
                        Rp {{ number_format(
                            $booking->discount_amount,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold">
                        Total
                    </span>

                    <strong style="color:var(--clr-magenta);">
                        Rp {{ number_format(
                            $booking->total_amount,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">
                        Sisa
                    </span>

                    <strong class="text-danger">
                        Rp {{ number_format(
                            $booking->remaining_amount,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>
                </div>
            </div>
        </div>

        @if(in_array(
        $booking->booking_status,
        [
        'pending_payment',
        'delivered',
        'assigned',
        ],
        true
        ))
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-x-circle me-2"></i>
                    Batalkan Pesanan
                </h6>
            </div>

            <div class="card-body">
                <form
                    action="{{ route(
                            'admin.bookings.cancel',
                            $booking->id
                        ) }}"
                    method="POST">
                    @csrf

                    <div class="mb-2">
                        <input
                            type="text"
                            name="reason"
                            class="form-control"
                            placeholder="Alasan pembatalan (opsional)">
                    </div>

                    <button
                        type="submit"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Yakin membatalkan pesanan ini?')">
                        <i class="bi bi-x-circle me-1"></i>
                        Batalkan Pesanan
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection