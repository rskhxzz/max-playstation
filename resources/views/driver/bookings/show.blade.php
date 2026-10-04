@extends('layouts.driver')
@section('title', 'Detail Pesanan')

@section('content')
@php
$isMyBooking = $booking->driver_id
&& (string) $booking->driver_id === (string) auth('driver')->id();

$isPickupDue = $booking->booking_status === 'arrived'
&& $booking->rental_end_at
&& now()->gte($booking->rental_end_at);
@endphp

<div
    id="driverBookingContext"
    data-booking-id="{{ $booking->id }}"
    data-snap-token="{{ $snapToken ?? '' }}"
    data-csrf-token="{{ csrf_token() }}"></div>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <a
        href="{{ route('driver.dashboard') }}"
        class="btn btn-sm btn-outline-secondary"
        style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>
        Kembali
    </a>

    <div class="d-flex flex-wrap gap-2">
        @if($booking->latitude && $booking->longitude)
        <a
            href="https://www.google.com/maps/dir/?api=1&destination={{ $booking->latitude }},{{ $booking->longitude }}"
            target="_blank"
            class="btn btn-sm btn-success"
            style="border-radius:8px;">
            <i class="bi bi-map me-1"></i>
            Buka Rute
        </a>
        @endif

        <span class="badge bg-{{ $booking->booking_status_badge }} d-flex align-items-center">
            {{ $booking->booking_status_label }}
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
                            Nama
                        </div>

                        <div class="fw-semibold fs-5">
                            {{ $booking->customer?->full_name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">
                            WhatsApp
                        </div>

                        @if($booking->customer?->phone_number)
                        <a
                            href="https://wa.me/{{ preg_replace('/\D/', '', $booking->customer->phone_number) }}"
                            target="_blank"
                            class="btn btn-sm btn-success mt-1"
                            style="border-radius:6px;">
                            <i class="bi bi-whatsapp me-1"></i>
                            {{ $booking->customer->phone_number }}
                        </a>
                        @else
                        <div>—</div>
                        @endif
                    </div>

                    <div class="col-12">
                        <div class="text-muted small">
                            Alamat Pengiriman
                        </div>

                        <div>
                            {{ $booking->delivery_address ?? '—' }}
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
                            Unit
                        </div>

                        <div class="fw-semibold">
                            {{ $booking->playstationUnit?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">
                            Mulai
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
                            Selesai
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

                    <div class="col-md-6">
                        <div class="text-muted small">
                            Kode Booking
                        </div>

                        <code>
                            {{ $booking->booking_code }}
                        </code>
                    </div>
                </div>
            </div>
        </div>

        @if(
        $booking->booking_status === 'delivered'
        && !$booking->driver_id
        )
        <div class="card mb-3 border-primary">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-check2-square me-2"></i>
                    Terima Pesanan
                </h6>
            </div>

            <div class="card-body">
                <p class="small text-muted mb-3">
                    Pilih kendaraan yang Anda gunakan.
                    Pilihan ini menentukan pendapatan driver.
                </p>

                <form
                    action="{{ route(
                            'driver.bookings.accept',
                            $booking->id
                        ) }}"
                    method="POST">
                    @csrf

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="vehicle-option">
                                <input
                                    type="radio"
                                    name="vehicle_type"
                                    value="personal"
                                    required>

                                <span>
                                    <strong>
                                        Motor Pribadi
                                    </strong>

                                    <small>
                                        Pendapatan:
                                        <b>
                                            Rp {{ number_format(
                                                    $booking->driver_fee,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                        </b>
                                    </small>
                                </span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="vehicle-option">
                                <input
                                    type="radio"
                                    name="vehicle_type"
                                    value="company"
                                    required>

                                <span>
                                    <strong>
                                        Motor Kantor
                                    </strong>

                                    <small>
                                        Pendapatan bersih:
                                        <b>
                                            Rp {{ number_format(
                                                    max(
                                                        0,
                                                        $booking->driver_fee
                                                        - $booking->fuel_deduction
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                        </b>
                                    </small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        style="border-radius:8px;">
                        <i class="bi bi-hand-thumbs-up me-2"></i>
                        Terima Pesanan
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if(
        $isMyBooking
        && $booking->booking_status === 'assigned'
        )
        <div class="card mb-3 border-primary">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-person-check me-2"></i>
                    Pesanan Ditugaskan ke Anda
                </h6>
            </div>

            <div class="card-body">
                <p class="small text-muted mb-3">
                    Admin menugaskan pesanan ini kepada Anda.
                    Konfirmasi penerimaan dan pilih kendaraan.
                </p>

                <form
                    action="{{ route(
                            'driver.bookings.accept',
                            $booking->id
                        ) }}"
                    method="POST">
                    @csrf

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="vehicle-option">
                                <input
                                    type="radio"
                                    name="vehicle_type"
                                    value="personal"
                                    required>

                                <span>
                                    <strong>
                                        Motor Pribadi
                                    </strong>

                                    <small>
                                        Pendapatan:
                                        <b>
                                            Rp {{ number_format(
                                                    $booking->driver_fee,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                        </b>
                                    </small>
                                </span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="vehicle-option">
                                <input
                                    type="radio"
                                    name="vehicle_type"
                                    value="company"
                                    required>

                                <span>
                                    <strong>
                                        Motor Kantor
                                    </strong>

                                    <small>
                                        Pendapatan bersih:
                                        <b>
                                            Rp {{ number_format(
                                                    max(
                                                        0,
                                                        $booking->driver_fee
                                                        - $booking->fuel_deduction
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                        </b>
                                    </small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        style="border-radius:8px;">
                        <i class="bi bi-play-circle me-2"></i>
                        Konfirmasi Terima
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if(
        $isMyBooking
        && $booking->booking_status === 'on_delivery'
        )
        <div class="card mb-3 border-success">
            <div
                class="card-header"
                style="background:#198754;color:#fff;">
                <h6 class="mb-0">
                    <i class="bi bi-camera me-2"></i>
                    Dokumentasi Pengantaran
                </h6>
            </div>

            <div class="card-body">
                @if($booking->delivery_photo_path)
                <img
                    src="{{ route(
                                'driver.bookings.photo',
                                $booking->id
                            ) }}"
                    alt="Dokumentasi customer dan unit PS"
                    class="img-fluid rounded border mb-3"
                    style="width:100%;max-height:360px;object-fit:cover;">
                @endif

                @if($booking->payment_status !== 'paid')
                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Pelunasan harus selesai sebelum pengantaran dikonfirmasi.
                </div>
                @else
                <div class="alert alert-light border small">
                    <strong>Wajib:</strong>
                    upload satu foto yang memperlihatkan
                    customer dan unit PlayStation dalam dokumentasi yang sama.
                </div>

                <form
                    action="{{ route(
                                'driver.bookings.complete-delivery',
                                $booking->id
                            ) }}"
                    method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <label class="form-label">
                        Foto Customer + Unit PS
                    </label>

                    <input
                        type="file"
                        name="delivery_photo"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                        required>

                    <div class="form-text">
                        Upload dari galeri HP. Maksimal 5 MB.
                    </div>

                    @error('delivery_photo')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                    @enderror

                    <button
                        type="submit"
                        class="btn btn-success w-100 mt-3"
                        onclick="return confirm('Pastikan foto customer + unit PS sudah benar. Selesaikan pengantaran?')">
                        <i class="bi bi-check2-circle me-2"></i>
                        Selesaikan Pengantaran
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif

        @if(
        $booking->booking_status === 'arrived'
        && $isMyBooking
        )
        <div class="card mb-3 border-success">
            <div
                class="card-header"
                style="background:#198754;color:#fff;">
                <h6 class="mb-0">
                    <i class="bi bi-house-check me-2"></i>
                    Pengantaran Selesai
                </h6>
            </div>

            <div class="card-body">
                <div class="alert alert-success py-2 small">
                    <i class="bi bi-check-circle me-1"></i>
                    Pengantaran sudah dikonfirmasi pada
                    {{ $booking->delivered_at
                            ? $booking->delivered_at
                                ->timezone('Asia/Jakarta')
                                ->format('d M Y, H:i')
                            : '—' }}
                    WIB.
                </div>

                @if($booking->delivery_photo_path)
                <img
                    src="{{ route(
                                'driver.bookings.photo',
                                $booking->id
                            ) }}"
                    alt="Dokumentasi customer dan unit PS"
                    class="img-fluid rounded border"
                    style="width:100%;max-height:360px;object-fit:cover;">
                @endif
            </div>
        </div>

        <div class="card border-warning">
            <div
                class="card-header"
                style="background:#f59e0b;color:#212529;">
                <h6 class="mb-0">
                    <i class="bi bi-arrow-return-left me-2"></i>
                    Pengambilan Unit
                </h6>
            </div>

            <div class="card-body">
                @if($isPickupDue)
                <p class="small mb-3">
                    Waktu sewa sudah selesai.
                    Silakan ambil kembali unit PlayStation dari customer.
                </p>

                <form
                    action="{{ route(
                                'driver.bookings.complete-pickup',
                                $booking->id
                            ) }}"
                    method="POST">
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-warning w-100 text-dark"
                        onclick="return confirm('Konfirmasi bahwa unit PlayStation sudah benar-benar diambil dari customer?')">
                        <i class="bi bi-check2-square me-2"></i>
                        Konfirmasi PS Sudah Diambil
                    </button>
                </form>
                @else
                <div class="alert alert-warning py-2 small mb-0">
                    Belum waktunya pengambilan.
                    Driver yang sama tetap bertanggung jawab mengambil unit setelah waktu sewa selesai.
                </div>
                @endif
            </div>
        </div>
        @endif

        @if(
        $booking->booking_status === 'completed'
        && $isMyBooking
        )
        <div class="card border-success">
            <div
                class="card-header"
                style="background:#198754;color:#fff;">
                <h6 class="mb-0">
                    <i class="bi bi-check-all me-2"></i>
                    Pesanan Selesai
                </h6>
            </div>

            <div class="card-body">
                <div class="alert alert-success py-2 small mb-2">
                    Unit sudah dikonfirmasi kembali dan pesanan telah selesai.
                </div>

                <div class="small text-muted">
                    Diambil pada
                    {{ $booking->picked_up_at
                            ? $booking->picked_up_at
                                ->timezone('Asia/Jakarta')
                                ->format('d M Y, H:i')
                            : '—' }}
                    WIB.
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-5">
        @if(
        $isMyBooking
        && $booking->booking_status === 'on_delivery'
        )
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-truck me-2"></i>
                    Penugasan Driver
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-muted small">
                            Kendaraan
                        </div>

                        <strong>
                            @if($booking->vehicle_type === 'personal')
                            Motor Pribadi
                            @else
                            Motor Kantor
                            @endif
                        </strong>
                    </div>

                    <div class="col-6">
                        <div class="text-muted small">
                            Pendapatan Bersih
                        </div>

                        <strong style="color:var(--clr-magenta);">
                            Rp {{ number_format(
                                    $booking->driver_income,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                        </strong>
                    </div>

                    <div class="col-6">
                        <div class="text-muted small">
                            Pendapatan Dasar
                        </div>

                        <strong>
                            Rp {{ number_format(
                                    $booking->driver_fee,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                        </strong>
                    </div>

                    <div class="col-6">
                        <div class="text-muted small">
                            Potongan Bensin
                        </div>

                        <strong class="text-danger">
                            Rp {{ number_format(
                                    $booking->fuel_deduction,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(
        $booking->payment_status === 'partial'
        && $isMyBooking
        && $booking->booking_status === 'on_delivery'
        )
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-wallet2 me-2"></i>
                    Pelunasan
                </h6>
            </div>

            <div class="card-body">
                <div class="small text-muted mb-3">
                    Sisa tagihan:
                    <strong>
                        Rp {{ number_format(
                                $booking->remaining_amount,
                                0,
                                ',',
                                '.'
                            ) }}
                    </strong>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button
                        type="button"
                        class="btn btn-sm flex-fill method-tab active"
                        id="tabQris">
                        <i class="bi bi-qr-code me-1"></i>
                        QRIS
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm flex-fill method-tab"
                        id="tabCash">
                        <i class="bi bi-cash-coin me-1"></i>
                        Tunai
                    </button>
                </div>

                <div id="panelQris">
                    @if($snapToken && $clientKey)
                    <button
                        type="button"
                        class="btn btn-primary w-100"
                        id="snapRemBtn">
                        <i class="bi bi-qr-code me-2"></i>
                        Tampilkan QRIS
                    </button>
                    @else
                    <button
                        type="button"
                        class="btn btn-primary w-100"
                        id="createRemBtn">
                        <i class="bi bi-qr-code me-2"></i>
                        Buat QRIS Pelunasan
                    </button>
                    @endif

                    <div
                        id="remStatus"
                        class="small mt-2"></div>
                </div>

                <div
                    id="panelCash"
                    style="display:none;">
                    <div class="alert alert-warning py-2 small">
                        Pastikan uang tunai sebesar
                        <strong>
                            Rp {{ number_format(
                                    $booking->remaining_amount,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                        </strong>
                        sudah diterima.
                    </div>

                    <button
                        type="button"
                        class="btn btn-warning w-100"
                        id="cashSettleBtn">
                        <i class="bi bi-cash-coin me-2"></i>
                        Konfirmasi Terima Tunai
                    </button>

                    <div
                        id="cashStatus"
                        class="small mt-2"></div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .vehicle-option {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: .9rem;
        cursor: pointer;
        height: 100%;
    }

    .vehicle-option:has(input:checked) {
        border-color: var(--clr-cyan);
        box-shadow: 0 0 0 3px rgba(0, 217, 255, .12);
        background: rgba(0, 217, 255, .04);
    }

    .vehicle-option input {
        margin-top: .25rem;
    }

    .vehicle-option strong,
    .vehicle-option small {
        display: block;
    }

    .vehicle-option small {
        color: #6b7280;
        margin-top: .15rem;
    }

    .method-tab {
        border-radius: 8px;
        border: 2px solid #d1d5db;
        background: #fff;
        font-weight: 600;
    }

    .method-tab.active {
        border-color: var(--clr-cyan);
        background: rgba(0, 217, 255, .1);
        color: var(--clr-dark);
    }
</style>
@endpush

@push('scripts')
@if($clientKey)
<script
    src="{{ config('services.midtrans.snap_js_url') }}"
    data-client-key="{{ $clientKey }}">
</script>
@endif

<script>
    (function() {
        const context =
            document.getElementById(
                'driverBookingContext'
            );

        if (!context) {
            return;
        }

        const bookingId =
            context.dataset.bookingId;

        const snapToken =
            context.dataset.snapToken;

        const csrfToken =
            context.dataset.csrfToken;

        const qrisTab =
            document.getElementById('tabQris');

        const cashTab =
            document.getElementById('tabCash');

        const qrisPanel =
            document.getElementById('panelQris');

        const cashPanel =
            document.getElementById('panelCash');

        qrisTab?.addEventListener(
            'click',
            function() {
                if (qrisPanel) {
                    qrisPanel.style.display = '';
                }

                if (cashPanel) {
                    cashPanel.style.display = 'none';
                }

                qrisTab.classList.add(
                    'active'
                );

                cashTab?.classList.remove(
                    'active'
                );
            }
        );

        cashTab?.addEventListener(
            'click',
            function() {
                if (cashPanel) {
                    cashPanel.style.display = '';
                }

                if (qrisPanel) {
                    qrisPanel.style.display = 'none';
                }

                cashTab.classList.add(
                    'active'
                );

                qrisTab?.classList.remove(
                    'active'
                );
            }
        );

        document
            .getElementById('snapRemBtn')
            ?.addEventListener(
                'click',
                function() {
                    if (
                        !snapToken ||
                        !window.snap
                    ) {
                        return;
                    }

                    window.snap.pay(
                        snapToken, {
                            onSuccess: function() {
                                window.location.reload();
                            },

                            onPending: function() {
                                window.location.reload();
                            }
                        }
                    );
                }
            );

        document
            .getElementById('createRemBtn')
            ?.addEventListener(
                'click',
                async function() {
                    const button = this;

                    const status =
                        document.getElementById(
                            'remStatus'
                        );

                    button.disabled = true;

                    button.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Membuat QRIS...';

                    try {
                        const response =
                            await fetch(
                                '/driver/pesanan/' +
                                bookingId +
                                '/qris-pelunasan', {
                                    method: 'POST',

                                    headers: {
                                        'Content-Type': 'application/json',

                                        'X-CSRF-TOKEN': csrfToken,

                                        'Accept': 'application/json'
                                    }
                                }
                            );

                        const data =
                            await response.json();

                        if (
                            !data.success ||
                            !data.snap_token ||
                            !window.snap
                        ) {
                            throw new Error(
                                data.message ||
                                'Gagal membuat QRIS.'
                            );
                        }

                        window.snap.pay(
                            data.snap_token, {
                                onSuccess: function() {
                                    window.location.reload();
                                },

                                onPending: function() {
                                    window.location.reload();
                                },

                                onClose: function() {
                                    button.disabled = false;

                                    button.innerHTML =
                                        '<i class="bi bi-qr-code me-2"></i>Buat QRIS Pelunasan';
                                }
                            }
                        );
                    } catch (error) {
                        if (status) {
                            status.innerHTML =
                                '<span class="text-danger">' +
                                String(
                                    error.message ||
                                    'Gagal membuat QRIS.'
                                ) +
                                '</span>';
                        }

                        button.disabled = false;

                        button.innerHTML =
                            '<i class="bi bi-qr-code me-2"></i>Buat QRIS Pelunasan';
                    }
                }
            );

        document
            .getElementById('cashSettleBtn')
            ?.addEventListener(
                'click',
                async function() {
                    const button = this;

                    const status =
                        document.getElementById(
                            'cashStatus'
                        );

                    if (
                        !window.confirm(
                            'Pastikan uang tunai sudah diterima dari customer. Lanjutkan?'
                        )
                    ) {
                        return;
                    }

                    button.disabled = true;

                    button.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

                    try {
                        const response =
                            await fetch(
                                '/driver/pesanan/' +
                                bookingId +
                                '/cash-pelunasan', {
                                    method: 'POST',

                                    headers: {
                                        'Content-Type': 'application/json',

                                        'X-CSRF-TOKEN': csrfToken,

                                        'Accept': 'application/json'
                                    }
                                }
                            );

                        const data =
                            await response.json();

                        if (data.success) {
                            window.location.reload();
                            return;
                        }

                        throw new Error(
                            data.message ||
                            'Gagal mencatat pembayaran.'
                        );
                    } catch (error) {
                        if (status) {
                            status.innerHTML =
                                '<span class="text-danger">' +
                                String(
                                    error.message ||
                                    'Gagal mencatat pembayaran.'
                                ) +
                                '</span>';
                        }

                        button.disabled = false;

                        button.innerHTML =
                            '<i class="bi bi-cash-coin me-2"></i>Konfirmasi Terima Tunai';
                    }
                }
            );
    })();
</script>
@endpush