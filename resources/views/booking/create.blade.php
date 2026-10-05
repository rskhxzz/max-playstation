@extends('layouts.public')

@section('title', 'Form Pemesanan — Maxibox Playstation')

@push('styles')
<style>
    .booking-section {
        background: #fff;
    }

    .form-step-title {
        color: var(--clr-dark);
        font-weight: 700;
        font-size: 1.1rem;
        border-bottom: 2px solid var(--clr-cyan);
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .map-container {
        width: 100%;
        height: 350px;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        overflow: hidden;
    }

    .price-summary {
        background: var(--clr-dark);
        color: #fff;
        border-radius: 12px;
        padding: 1.5rem;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .price-row.total {
        font-size: 1.1rem;
        font-weight: 700;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        padding-top: 0.75rem;
        margin-top: 0.75rem;
        color: var(--clr-cyan);
    }

    .price-row.pay-now {
        font-size: 1rem;
        font-weight: 700;
        color: var(--clr-magenta);
    }

    .btn-book {
        background: var(--clr-magenta);
        border: none;
        font-weight: 700;
        font-size: 1rem;
        border-radius: 10px;
        padding: 0.85rem 2rem;
        width: 100%;
        color: #fff;
    }

    .btn-book:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    .btn-book:not(:disabled):hover {
        background: #c4006b;
    }

    .terms-box {
        max-height: 180px;
        overflow-y: auto;
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .payment-opt {
        cursor: pointer;
        transition: 0.2s ease;
    }

    .payment-opt:hover {
        border-color: var(--clr-magenta) !important;
    }
</style>
@endpush

@section('content')
<section class="booking-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="text-center mb-4">
                    <h2 style="font-size:1.8rem;font-weight:800;color:var(--clr-dark);">
                        Form Pemesanan
                    </h2>

                    <div
                        style="width:50px;height:4px;background:var(--clr-magenta);border-radius:2px;margin:0.5rem auto;"></div>
                </div>

                @include('components.alert')

                <div class="card p-4">
                    <form
                        action="{{ route('booking.store') }}"
                        method="POST"
                        id="bookingForm">
                        @csrf

                        <input
                            type="hidden"
                            name="booking_token"
                            value="{{ old('booking_token', $bookingToken) }}">

                        {{-- Data Diri --}}
                        <div class="form-step-title">
                            <i
                                class="bi bi-person-fill me-2"
                                style="color:var(--clr-magenta);"></i>
                            Data Pemesan
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Nama Lengkap
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="full_name"
                                    class="form-control @error('full_name') is-invalid @enderror"
                                    value="{{ old('full_name') }}"
                                    placeholder="Nama lengkap Anda">

                                @error('full_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Nomor HP / WhatsApp
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="phone_number"
                                    class="form-control @error('phone_number') is-invalid @enderror"
                                    value="{{ old('phone_number') }}"
                                    placeholder="08xxxxxxxxxx">

                                @error('phone_number')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        {{-- Jadwal --}}
                        <div class="form-step-title">
                            <i
                                class="bi bi-calendar-event me-2"
                                style="color:var(--clr-magenta);"></i>
                            Jadwal Penyewaan
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">
                                    Tanggal Sewa
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="date"
                                    name="rental_date"
                                    id="rental_date"
                                    class="form-control @error('rental_date') is-invalid @enderror"
                                    value="{{ old('rental_date') }}"
                                    min="{{ date('Y-m-d') }}">

                                @error('rental_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Paket Sewa
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="rental_package_id"
                                    id="packageSelect"
                                    class="form-select @error('rental_package_id') is-invalid @enderror">
                                    <option value="">
                                        -- Pilih Paket --
                                    </option>

                                    @foreach($packages as $pkg)
                                    <option
                                        value="{{ $pkg->id }}"
                                        data-price="{{ $pkg->price }}"
                                        data-duration="{{ $pkg->duration_hours }}"
                                        data-blocked-start="{{ $pkg->blocked_start_time ? substr((string) $pkg->blocked_start_time, 0, 5) : '' }}"
                                        data-blocked-end="{{ $pkg->blocked_end_time ? ($pkg->blocked_end_time === '00:00:00' ? '24:00' : substr((string) $pkg->blocked_end_time, 0, 5)) : '' }}"
                                        {{ old('rental_package_id', request('package')) == $pkg->id ? 'selected' : '' }}>
                                        {{ $pkg->name }}
                                        —
                                        {{ $pkg->duration_hours }} jam
                                        —
                                        Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                    </option>
                                    @endforeach
                                </select>

                                @error('rental_package_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Jam Mulai
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="time"
                                    name="rental_time"
                                    id="rental_time"
                                    class="form-control @error('rental_time') is-invalid @enderror"
                                    value="{{ old('rental_time') }}">

                                @error('rental_time')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                                <div
                                    id="timeWarning"
                                    class="text-danger small mt-1 d-none"></div>
                            </div>
                        </div>

                        {{-- Lokasi --}}
                        <div class="form-step-title">
                            <i
                                class="bi bi-geo-alt-fill me-2"
                                style="color:var(--clr-magenta);"></i>
                            Lokasi Pengiriman
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Cari Alamat
                            </label>

                            <input
                                type="text"
                                id="addressSearch"
                                class="form-control"
                                placeholder="Ketik dan pilih alamat dari daftar..."
                                value="{{ old('delivery_address') }}">
                        </div>

                        <div
                            class="map-container mb-3"
                            id="map"
                            data-default-lat="{{ $setting?->latitude ?? -6.2 }}"
                            data-default-lng="{{ $setting?->longitude ?? 106.8 }}"
                            data-dp-amount="{{ $setting?->down_payment_amount ?? 50000 }}"></div>

                        <div
                            class="alert alert-info py-2 px-3 mb-3"
                            style="font-size:0.82rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Seret pin pada peta untuk memperbarui lokasi Anda secara tepat.
                        </div>

                        <input
                            type="hidden"
                            name="delivery_address"
                            id="deliveryAddress"
                            value="{{ old('delivery_address') }}">

                        <input
                            type="hidden"
                            name="google_place_id"
                            id="googlePlaceId"
                            value="{{ old('google_place_id') }}">

                        <input
                            type="hidden"
                            name="latitude"
                            id="latInput"
                            value="{{ old('latitude') }}">

                        <input
                            type="hidden"
                            name="longitude"
                            id="lngInput"
                            value="{{ old('longitude') }}">

                        <input
                            type="hidden"
                            id="dpAmount"
                            value="{{ $setting?->down_payment_amount ?? 50000 }}">

                        @error('latitude')
                        <div class="text-danger small mb-2">
                            {{ $message }}
                        </div>
                        @enderror

                        @error('delivery_address')
                        <div class="text-danger small mb-2">
                            {{ $message }}
                        </div>
                        @enderror

                        {{-- Pembayaran --}}
                        <div class="form-step-title mt-4">
                            <i
                                class="bi bi-credit-card me-2"
                                style="color:var(--clr-magenta);"></i>
                            Pilihan Pembayaran
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div
                                    class="form-check border rounded-3 p-3 payment-opt"
                                    id="optFull">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="payment_option"
                                        id="payFull"
                                        value="full"
                                        {{ old('payment_option', 'full') === 'full' ? 'checked' : '' }}>

                                    <label
                                        class="form-check-label fw-600 w-100"
                                        for="payFull">
                                        <i
                                            class="bi bi-check-circle me-2"
                                            style="color:var(--clr-magenta);"></i>
                                        Bayar Lunas

                                        <div class="text-muted small fw-normal">
                                            Bayar total tagihan sekarang
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div
                                    class="form-check border rounded-3 p-3 payment-opt"
                                    id="optDP">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="payment_option"
                                        id="payDP"
                                        value="deposit"
                                        {{ old('payment_option') === 'deposit' ? 'checked' : '' }}>

                                    <label
                                        class="form-check-label fw-600 w-100"
                                        for="payDP">
                                        <i
                                            class="bi bi-cash-coin me-2"
                                            style="color:var(--clr-cyan);"></i>
                                        Bayar DP Rp50.000

                                        <div class="text-muted small fw-normal">
                                            Sisanya dibayar saat PlayStation tiba
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan Harga --}}
                        <div
                            class="price-summary mb-4"
                            id="priceSummary">
                            <div class="price-row">
                                <span>Harga Paket</span>
                                <span id="pricePackage">Rp 0</span>
                            </div>

                            <div class="price-row">
                                <span>Biaya Pengiriman</span>
                                <span id="priceDelivery">Rp 0</span>
                            </div>

                            <div class="price-row">
                                <span>Diskon</span>
                                <span>Rp 0</span>
                            </div>

                            <div class="price-row total">
                                <span>Total Tagihan</span>
                                <span id="priceTotal">Rp 0</span>
                            </div>

                            <div class="price-row pay-now">
                                <span id="payNowLabel">
                                    Dibayar Sekarang
                                </span>

                                <span id="pricePayNow">
                                    Rp 0
                                </span>
                            </div>

                            <div
                                class="price-row"
                                id="remainingRow"
                                style="display:none!important;">
                                <span>
                                    Sisa Bayar (saat tiba)
                                </span>

                                <span id="priceRemaining">
                                    Rp 0
                                </span>
                            </div>

                            <div
                                id="deliveryStatus"
                                class="mt-2"></div>
                        </div>

                        {{-- Syarat dan Ketentuan --}}
                        @if($terms)
                        <div class="form-step-title">
                            <i
                                class="bi bi-file-earmark-text me-2"
                                style="color:var(--clr-magenta);"></i>
                            Syarat & Ketentuan
                        </div>

                        <div class="terms-box">
                            {!! nl2br(e($terms->content)) !!}
                        </div>
                        @endif

                        <div class="form-check mb-4">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="termsAgreed"
                                name="terms_agreed"
                                value="1"
                                {{ old('terms_agreed') ? 'checked' : '' }}>

                            <label
                                class="form-check-label"
                                for="termsAgreed">
                                Saya menyetujui syarat dan ketentuan yang berlaku
                            </label>

                            @error('terms_agreed')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-4">
                            <label class="form-label">
                                Catatan Tambahan (opsional)
                            </label>

                            <textarea
                                name="customer_notes"
                                class="form-control"
                                rows="2"
                                placeholder="Petunjuk alamat, dll.">{{ old('customer_notes') }}</textarea>
                        </div>

                        <button
                            type="submit"
                            class="btn-book"
                            id="submitBtn"
                            disabled>
                            <i class="bi bi-qr-code me-2"></i>
                            Pesan dan Bayar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const bookingForm = document.getElementById('bookingForm');
    const packageSelect = document.getElementById('packageSelect');
    const rentalTime = document.getElementById('rental_time');
    const termsAgreed = document.getElementById('termsAgreed');
    const submitBtn = document.getElementById('submitBtn');
    const timeWarning = document.getElementById('timeWarning');

    const pricePackage = document.getElementById('pricePackage');
    const priceDelivery = document.getElementById('priceDelivery');
    const priceTotal = document.getElementById('priceTotal');
    const pricePayNow = document.getElementById('pricePayNow');
    const payNowLabel = document.getElementById('payNowLabel');
    const remainingRow = document.getElementById('remainingRow');
    const priceRemaining = document.getElementById('priceRemaining');
    const deliveryStatus = document.getElementById('deliveryStatus');

    const latInput = document.getElementById('latInput');
    const lngInput = document.getElementById('lngInput');
    const deliveryAddress = document.getElementById('deliveryAddress');
    const googlePlaceId = document.getElementById('googlePlaceId');
    const addressSearch = document.getElementById('addressSearch');

    const mapElement = document.getElementById('map');

    const csrfMeta = document.querySelector(
        'meta[name="csrf-token"]'
    );

    const csrfToken = csrfMeta ?
        csrfMeta.getAttribute('content') :
        '';

    const dpAmount = Number(
        document.getElementById('dpAmount')?.value || 50000
    );

    let map = null;
    let marker = null;
    let autocomplete = null;

    let packagePrice = 0;
    let deliveryFee = 0;
    let isServiceable = false;

    let locationSet = !!latInput.value &&
        !!lngInput.value;

    function formatRp(value) {
        return 'Rp ' + Math.round(
            Number(value) || 0
        ).toLocaleString('id-ID');
    }

    function getSelectedPackage() {
        const option =
            packageSelect.options[
                packageSelect.selectedIndex
            ];

        if (
            !option ||
            !option.value
        ) {
            return null;
        }

        return {
            id: option.value,
            price: Number(
                option.dataset.price || 0
            ),
            duration: Number(
                option.dataset.duration || 0
            ),
            blockedStart: option.dataset.blockedStart || '',
            blockedEnd: option.dataset.blockedEnd || ''
        };
    }

    function updatePrices() {
        const total =
            packagePrice +
            deliveryFee;

        const isDP =
            document.getElementById('payDP').checked;

        const payNow =
            isDP ?
            dpAmount :
            total;

        const remaining =
            isDP ?
            Math.max(
                total - dpAmount,
                0
            ) :
            0;

        pricePackage.textContent =
            formatRp(packagePrice);

        priceDelivery.textContent =
            formatRp(deliveryFee);

        priceTotal.textContent =
            formatRp(total);

        pricePayNow.textContent =
            formatRp(payNow);

        payNowLabel.textContent =
            isDP ?
            'DP Sekarang' :
            'Dibayar Sekarang';

        if (
            isDP &&
            remaining > 0
        ) {
            remainingRow.style.removeProperty(
                'display'
            );

            priceRemaining.textContent =
                formatRp(remaining);
        } else {
            remainingRow.style.setProperty(
                'display',
                'none',
                'important'
            );
        }

        validateForm();
    }

    function timeToMinutes(time) {
        const parts =
            String(time).split(':');

        const hours =
            Number(parts[0]) || 0;

        const minutes =
            Number(parts[1]) || 0;

        return (
            hours * 60 +
            minutes
        );
    }

    function checkTimeBlock() {
        const pkg =
            getSelectedPackage();

        const timeValue =
            rentalTime.value;

        if (
            !pkg ||
            !timeValue
        ) {
            timeWarning.textContent = '';
            timeWarning.classList.add(
                'd-none'
            );

            validateForm();

            return;
        }

        if (
            !pkg.blockedStart ||
            !pkg.blockedEnd
        ) {
            timeWarning.textContent = '';
            timeWarning.classList.add(
                'd-none'
            );

            validateForm();

            return;
        }

        const startMinutes =
            timeToMinutes(timeValue);

        const blockedStart =
            timeToMinutes(
                pkg.blockedStart
            );

        const blockedEnd =
            pkg.blockedEnd === '24:00' ?
            1440 :
            timeToMinutes(
                pkg.blockedEnd
            );

        const blocked =
            startMinutes >= blockedStart &&
            startMinutes < blockedEnd;

        if (blocked) {
            timeWarning.textContent =
                `Paket ini tidak tersedia pukul ${pkg.blockedStart}–${pkg.blockedEnd}. Pilih jam lain.`;

            timeWarning.classList.remove(
                'd-none'
            );
        } else {
            timeWarning.textContent = '';
            timeWarning.classList.add(
                'd-none'
            );
        }

        validateForm();
    }

    function validateForm() {
        if (
            !termsAgreed ||
            !packageSelect ||
            !timeWarning ||
            !submitBtn
        ) {
            return;
        }

        const termsOk =
            termsAgreed.checked;

        const locationOk =
            locationSet &&
            isServiceable;

        const packageOk = !!packageSelect.value;

        const noBlock =
            timeWarning.classList.contains(
                'd-none'
            );

        submitBtn.disabled = !(
            termsOk &&
            locationOk &&
            packageOk &&
            noBlock
        );
    }

    packageSelect.addEventListener(
        'change',
        function() {
            const pkg =
                getSelectedPackage();

            packagePrice =
                pkg ?
                pkg.price :
                0;

            checkTimeBlock();
            updatePrices();
        }
    );

    rentalTime.addEventListener(
        'change',
        checkTimeBlock
    );

    document.querySelectorAll(
        'input[name="payment_option"]'
    ).forEach(
        function(radio) {
            radio.addEventListener(
                'change',
                updatePrices
            );
        }
    );

    termsAgreed.addEventListener(
        'change',
        validateForm
    );

    function initMap() {
        const defaultLat =
            Number(
                mapElement.dataset.defaultLat
            ) || -6.2;

        const defaultLng =
            Number(
                mapElement.dataset.defaultLng
            ) || 106.8;

        map =
            new google.maps.Map(
                mapElement, {
                    center: {
                        lat: defaultLat,
                        lng: defaultLng
                    },

                    zoom: 14,

                    styles: [{
                        elementType: 'labels.icon',

                        stylers: [{
                            visibility: 'off'
                        }]
                    }]
                }
            );

        marker =
            new google.maps.Marker({
                position: {
                    lat: defaultLat,
                    lng: defaultLng
                },

                map: map,

                draggable: true,

                title: 'Lokasi Anda'
            });

        const savedLat =
            Number(
                latInput.value
            );

        const savedLng =
            Number(
                lngInput.value
            );

        if (
            savedLat &&
            savedLng
        ) {
            map.setCenter({
                lat: savedLat,
                lng: savedLng
            });

            marker.setPosition({
                lat: savedLat,
                lng: savedLng
            });

            locationSet = true;

            calculateDelivery(
                savedLat,
                savedLng
            );
        }

        marker.addListener(
            'dragend',
            function() {
                const position =
                    marker.getPosition();

                if (!position) {
                    return;
                }

                setLocation(
                    position.lat(),
                    position.lng(),
                    ''
                );
            }
        );

        if (
            addressSearch &&
            google.maps.places
        ) {
            autocomplete =
                new google.maps.places.Autocomplete(
                    addressSearch, {
                        componentRestrictions: {
                            country: 'id'
                        }
                    }
                );

            autocomplete.addListener(
                'place_changed',
                function() {
                    const place =
                        autocomplete.getPlace();

                    if (
                        !place.geometry
                    ) {
                        return;
                    }

                    const lat =
                        place.geometry.location.lat();

                    const lng =
                        place.geometry.location.lng();

                    const placeId =
                        place.place_id || '';

                    map.setCenter({
                        lat: lat,
                        lng: lng
                    });

                    map.setZoom(16);

                    marker.setPosition({
                        lat: lat,
                        lng: lng
                    });

                    setLocation(
                        lat,
                        lng,
                        place.formatted_address ||
                        addressSearch.value,
                        placeId
                    );
                }
            );
        }

        map.addListener(
            'click',
            function(event) {
                marker.setPosition(
                    event.latLng
                );

                setLocation(
                    event.latLng.lat(),
                    event.latLng.lng(),
                    ''
                );
            }
        );
    }

    function setLocation(
        lat,
        lng,
        address,
        placeId = ''
    ) {
        latInput.value = lat;
        lngInput.value = lng;

        if (placeId) {
            googlePlaceId.value =
                placeId;
        }

        if (address) {
            deliveryAddress.value =
                address;

            addressSearch.value =
                address;
        }

        locationSet = true;

        if (!address) {
            const geocoder =
                new google.maps.Geocoder();

            geocoder.geocode({
                    location: {
                        lat: lat,
                        lng: lng
                    }
                },
                function(
                    results,
                    status
                ) {
                    if (
                        status === 'OK' &&
                        results &&
                        results[0]
                    ) {
                        const formattedAddress =
                            results[0]
                            .formatted_address;

                        deliveryAddress.value =
                            formattedAddress;

                        addressSearch.value =
                            formattedAddress;
                    }
                }
            );
        }

        calculateDelivery(
            lat,
            lng
        );
    }

    function calculateDelivery(
        lat,
        lng
    ) {
        deliveryStatus.innerHTML =
            '<span class="text-warning small">' +
            '<i class="bi bi-arrow-repeat me-1"></i>' +
            'Menghitung jarak...' +
            '</span>';

        fetch(
                '/api/calculate-delivery', {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',

                        'X-CSRF-TOKEN': csrfToken,

                        'Accept': 'application/json'
                    },

                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng
                    })
                }
            )
            .then(
                function(response) {
                    if (!response.ok) {
                        throw new Error(
                            'HTTP ' +
                            response.status
                        );
                    }

                    return response.json();
                }
            )
            .then(
                function(data) {
                    if (
                        data.is_serviceable
                    ) {
                        deliveryFee =
                            Number(
                                data.delivery_fee
                            ) || 0;

                        isServiceable =
                            true;

                        deliveryStatus.innerHTML =
                            '<span class="text-success small">' +
                            '<i class="bi bi-check-circle me-1"></i>' +
                            `${data.distance_km} km — ${data.message}` +
                            '</span>';
                    } else {
                        deliveryFee = 0;
                        isServiceable = false;

                        deliveryStatus.innerHTML =
                            '<span class="text-danger small">' +
                            '<i class="bi bi-x-circle me-1"></i>' +
                            `${data.message}` +
                            '</span>';
                    }

                    updatePrices();
                }
            )
            .catch(
                function() {
                    deliveryStatus.innerHTML =
                        '<span class="text-danger small">' +
                        'Gagal menghitung jarak. Coba lagi.' +
                        '</span>';

                    isServiceable = false;

                    validateForm();
                }
            );
    }

    window.initMap = initMap;

    packageSelect.dispatchEvent(
        new Event('change')
    );

    checkTimeBlock();
    updatePrices();
</script>

@if(config('services.google_maps.browser_key'))
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.browser_key') }}&libraries=places&loading=async&callback=initMap"
    async
    defer></script>
@else
<script>
    if (mapElement) {
        mapElement.innerHTML =
            '<div style="display:flex;align-items:center;justify-content:center;height:100%;background:#f3f4f6;flex-direction:column;gap:8px;">' +
            '<i class="bi bi-map" style="font-size:3rem;color:#9ca3af;"></i>' +
            '<p class="text-muted small">Konfigurasi GOOGLE_MAPS_BROWSER_KEY pada .env</p>' +
            '</div>';
    }

    locationSet = true;
    isServiceable = true;

    latInput.value = -6.2;
    lngInput.value = 106.8;
    deliveryAddress.value = 'Alamat pengujian';

    updatePrices();
</script>
@endif
@endpush