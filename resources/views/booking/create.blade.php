@extends('layouts.public')
@section('title', 'Form Pemesanan — Maxibox Playstation')

@push('styles')
<style>
.booking-section { background: #fff; }
.form-step-title { color: var(--clr-dark); font-weight: 700; font-size: 1.1rem; border-bottom: 2px solid var(--clr-cyan); padding-bottom: 0.5rem; margin-bottom: 1.5rem; }
.map-container { width: 100%; height: 350px; border-radius: 10px; border: 2px solid #e5e7eb; overflow: hidden; }
.price-summary { background: var(--clr-dark); color: #fff; border-radius: 12px; padding: 1.5rem; }
.price-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem; }
.price-row.total { font-size: 1.1rem; font-weight: 700; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 0.75rem; margin-top: 0.75rem; color: var(--clr-cyan); }
.price-row.pay-now { font-size: 1rem; font-weight: 700; color: var(--clr-magenta); }
.btn-book { background: var(--clr-magenta); border: none; font-weight: 700; font-size: 1rem; border-radius: 10px; padding: 0.85rem 2rem; width: 100%; color: #fff; }
.btn-book:disabled { background: #9ca3af; cursor: not-allowed; }
.btn-book:not(:disabled):hover { background: #c4006b; }
.terms-box { max-height: 180px; overflow-y: auto; background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; font-size: 0.85rem; margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<section class="booking-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h2 style="font-size:1.8rem;font-weight:800;color:var(--clr-dark);">Form Pemesanan</h2>
                    <div style="width:50px;height:4px;background:var(--clr-magenta);border-radius:2px;margin:0.5rem auto;"></div>
                </div>

                @include('components.alert')

                <div class="card p-4">
                    <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
                        @csrf

                        {{-- Data Diri --}}
                        <div class="form-step-title"><i class="bi bi-person-fill me-2" style="color:var(--clr-magenta);"></i>Data Pemesan</div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                       value="{{ old('full_name') }}" placeholder="Nama lengkap Anda">
                                @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                                       value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx">
                                @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Jadwal --}}
                        <div class="form-step-title"><i class="bi bi-calendar-event me-2" style="color:var(--clr-magenta);"></i>Jadwal Penyewaan</div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Sewa <span class="text-danger">*</span></label>
                                <input type="date" name="rental_date" id="rental_date" class="form-control @error('rental_date') is-invalid @enderror"
                                       value="{{ old('rental_date') }}" min="{{ date('Y-m-d') }}">
                                @error('rental_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Paket Sewa <span class="text-danger">*</span></label>
                                <select name="rental_package_id" id="packageSelect" class="form-select @error('rental_package_id') is-invalid @enderror">
                                    <option value="">-- Pilih Paket --</option>
                                    @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}"
                                        data-price="{{ $pkg->price }}"
                                        data-duration="{{ $pkg->duration_hours }}"
                                        data-blocked-start="{{ $pkg->blocked_start_time ? substr($pkg->blocked_start_time,0,5) : '' }}"
                                        data-blocked-end="{{ $pkg->blocked_end_time ? ($pkg->blocked_end_time === '00:00:00' ? '24:00' : substr($pkg->blocked_end_time,0,5)) : '' }}"
                                        {{ old('rental_package_id', request('package')) == $pkg->id ? 'selected' : '' }}>
                                        {{ $pkg->name }} — {{ $pkg->duration_hours }} jam — Rp {{ number_format($pkg->price,0,',','.') }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('rental_package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="rental_time" id="rental_time" class="form-control @error('rental_time') is-invalid @enderror"
                                       value="{{ old('rental_time') }}">
                                @error('rental_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="timeWarning" class="text-danger small mt-1 d-none"></div>
                            </div>
                        </div>

                        {{-- Lokasi --}}
                        <div class="form-step-title"><i class="bi bi-geo-alt-fill me-2" style="color:var(--clr-magenta);"></i>Lokasi Pengiriman</div>
                        <div class="mb-3">
                            <label class="form-label">Cari Alamat</label>
                            <input type="text" id="addressSearch" class="form-control" placeholder="Ketik dan pilih alamat dari daftar...">
                        </div>
                        <div class="map-container mb-3" id="map"></div>
                        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:0.82rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Seret pin pada peta untuk memperbarui lokasi Anda secara tepat.
                        </div>
                        <input type="hidden" name="delivery_address" id="deliveryAddress" value="{{ old('delivery_address') }}">
                        <input type="hidden" name="google_place_id" id="googlePlaceId" value="{{ old('google_place_id') }}">
                        <input type="hidden" name="latitude" id="latInput" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="lngInput" value="{{ old('longitude') }}">

                        @error('latitude')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                        @error('delivery_address')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                        {{-- Pilihan Pembayaran --}}
                        <div class="form-step-title mt-4"><i class="bi bi-credit-card me-2" style="color:var(--clr-magenta);"></i>Pilihan Pembayaran</div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-check border rounded-3 p-3 payment-opt" id="optFull">
                                    <input class="form-check-input" type="radio" name="payment_option" id="payFull" value="full"
                                           {{ old('payment_option','full') === 'full' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-600 w-100" for="payFull">
                                        <i class="bi bi-check-circle me-2" style="color:var(--clr-magenta);"></i>Bayar Lunas
                                        <div class="text-muted small fw-normal">Bayar total tagihan sekarang</div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check border rounded-3 p-3 payment-opt" id="optDP">
                                    <input class="form-check-input" type="radio" name="payment_option" id="payDP" value="deposit"
                                           {{ old('payment_option') === 'deposit' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-600 w-100" for="payDP">
                                        <i class="bi bi-cash-coin me-2" style="color:var(--clr-cyan);"></i>Bayar DP Rp50.000
                                        <div class="text-muted small fw-normal">Sisanya dibayar saat PlayStation tiba</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan Harga --}}
                        <div class="price-summary mb-4" id="priceSummary">
                            <div class="price-row"><span>Harga Paket</span><span id="pricePackage">Rp 0</span></div>
                            <div class="price-row"><span>Biaya Pengiriman</span><span id="priceDelivery">Rp 0</span></div>
                            <div class="price-row"><span>Diskon</span><span>Rp 0</span></div>
                            <div class="price-row total"><span>Total Tagihan</span><span id="priceTotal">Rp 0</span></div>
                            <div class="price-row pay-now"><span id="payNowLabel">Dibayar Sekarang</span><span id="pricePayNow">Rp 0</span></div>
                            <div class="price-row" id="remainingRow" style="display:none!important;">
                                <span>Sisa Bayar (saat tiba)</span><span id="priceRemaining">Rp 0</span>
                            </div>
                            <div id="deliveryStatus" class="mt-2"></div>
                        </div>

                        {{-- Syarat dan Ketentuan --}}
                        @if($terms)
                        <div class="form-step-title"><i class="bi bi-file-earmark-text me-2" style="color:var(--clr-magenta);"></i>Syarat & Ketentuan</div>
                        <div class="terms-box">{!! nl2br(e($terms->content)) !!}</div>
                        @endif

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="termsAgreed" name="terms_agreed" value="1"
                                   {{ old('terms_agreed') ? 'checked' : '' }}>
                            <label class="form-check-label" for="termsAgreed">
                                Saya menyetujui syarat dan ketentuan yang berlaku
                            </label>
                            @error('terms_agreed')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-4">
                            <label class="form-label">Catatan Tambahan (opsional)</label>
                            <textarea name="customer_notes" class="form-control" rows="2" placeholder="Petunjuk alamat, dll.">{{ old('customer_notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn-book" id="submitBtn" disabled>
                            <i class="bi bi-qr-code me-2"></i>Pesan dan Bayar
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
// ─── Package data ─────────────────────────────────────────────────────────────
const packages = {
    @foreach($packages as $pkg)
    '{{ $pkg->id }}': {
        price: {{ $pkg->price }},
        duration: {{ $pkg->duration_hours }},
        blockedStart: '{{ $pkg->blocked_start_time ? substr($pkg->blocked_start_time,0,5) : '' }}',
        blockedEnd: '{{ $pkg->blocked_end_time ? ($pkg->blocked_end_time === "00:00:00" ? "24:00" : substr($pkg->blocked_end_time,0,5)) : "" }}'
    },
    @endforeach
};

const dpAmount  = {{ optional($setting)->down_payment_amount ?? 50000 }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ─── State ────────────────────────────────────────────────────────────────────
let packagePrice  = 0;
let deliveryFee   = 0;
let isServiceable = false;
let locationSet   = {{ old('latitude') ? 'true' : 'false' }};

// ─── Format currency ──────────────────────────────────────────────────────────
function formatRp(v) {
    return 'Rp ' + Math.round(v).toLocaleString('id-ID');
}

// ─── Update price summary ─────────────────────────────────────────────────────
function updatePrices() {
    const total    = packagePrice + deliveryFee;
    const isDP     = document.getElementById('payDP').checked;
    const payNow   = isDP ? dpAmount : total;
    const remaining = isDP ? total - dpAmount : 0;

    document.getElementById('pricePackage').textContent  = formatRp(packagePrice);
    document.getElementById('priceDelivery').textContent = formatRp(deliveryFee);
    document.getElementById('priceTotal').textContent    = formatRp(total);
    document.getElementById('pricePayNow').textContent   = formatRp(payNow);
    document.getElementById('payNowLabel').textContent   = isDP ? 'DP Sekarang' : 'Dibayar Sekarang';

    const remRow = document.getElementById('remainingRow');
    if (isDP && remaining > 0) {
        remRow.style.removeProperty('display');
        document.getElementById('priceRemaining').textContent = formatRp(remaining);
    } else {
        remRow.style.setProperty('display', 'none', 'important');
    }

    validateForm();
}

// ─── Package select ───────────────────────────────────────────────────────────
document.getElementById('packageSelect').addEventListener('change', function() {
    const pkg = packages[this.value];
    if (pkg) {
        packagePrice = pkg.price;
    } else {
        packagePrice = 0;
    }
    checkTimeBlock();
    updatePrices();
});

// ─── Time block check ─────────────────────────────────────────────────────────
function checkTimeBlock() {
    const packageId  = document.getElementById('packageSelect').value;
    const timeVal    = document.getElementById('rental_time').value;
    const warning    = document.getElementById('timeWarning');

    if (!packageId || !timeVal) { warning.classList.add('d-none'); return; }
    const pkg = packages[packageId];
    if (!pkg || !pkg.blockedStart || !pkg.blockedEnd) { warning.classList.add('d-none'); return; }

    const startMins = timeToMins(timeVal);
    const blockSt   = timeToMins(pkg.blockedStart);
    const blockEn   = pkg.blockedEnd === '24:00' ? 1440 : timeToMins(pkg.blockedEnd);
    const blocked   = startMins >= blockSt && startMins < blockEn;

    if (blocked) {
        warning.textContent = `Paket ini tidak tersedia pukul ${pkg.blockedStart}–${pkg.blockedEnd}. Pilih jam lain.`;
        warning.classList.remove('d-none');
    } else {
        warning.classList.add('d-none');
    }
    validateForm();
}

function timeToMins(t) {
    const [h, m] = t.split(':').map(Number);
    return h * 60 + m;
}

document.getElementById('rental_time').addEventListener('change', checkTimeBlock);
document.getElementById('packageSelect').addEventListener('change', checkTimeBlock);

// ─── Payment option toggle ────────────────────────────────────────────────────
document.querySelectorAll('input[name="payment_option"]').forEach(r => r.addEventListener('change', updatePrices));

// ─── Terms checkbox ───────────────────────────────────────────────────────────
document.getElementById('termsAgreed').addEventListener('change', validateForm);

// ─── Form validation ──────────────────────────────────────────────────────────
function validateForm() {
    const termsOk    = document.getElementById('termsAgreed').checked;
    const locationOk = locationSet && isServiceable;
    const packageOk  = !!document.getElementById('packageSelect').value;
    const noBlock    = document.getElementById('timeWarning').classList.contains('d-none');
    document.getElementById('submitBtn').disabled = !(termsOk && locationOk && packageOk && noBlock);
}

// ─── Google Maps ──────────────────────────────────────────────────────────────
let map, marker, autocomplete;

function initMap() {
    const defaultLat = {{ optional($setting)->latitude ?? -6.2 }};
    const defaultLng = {{ optional($setting)->longitude ?? 106.8 }};

    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: defaultLat, lng: defaultLng },
        zoom: 14,
        styles: [{ elementType: 'labels.icon', stylers: [{ visibility: 'off' }] }]
    });

    marker = new google.maps.Marker({
        position: { lat: defaultLat, lng: defaultLng },
        map,
        draggable: true,
        title: 'Lokasi Anda'
    });

    @if(old('latitude') && old('longitude'))
    const savedLat = {{ old('latitude') }};
    const savedLng = {{ old('longitude') }};
    map.setCenter({ lat: savedLat, lng: savedLng });
    marker.setPosition({ lat: savedLat, lng: savedLng });
    document.getElementById('latInput').value  = savedLat;
    document.getElementById('lngInput').value  = savedLng;
    calculateDelivery(savedLat, savedLng);
    @endif

    marker.addListener('dragend', function() {
        const pos = marker.getPosition();
        setLocation(pos.lat(), pos.lng(), '');
    });

    // Autocomplete
    const input = document.getElementById('addressSearch');
    autocomplete = new google.maps.places.Autocomplete(input, { componentRestrictions: { country: 'id' } });
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (!place.geometry) return;
        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();
        const placeId = place.place_id || '';
        map.setCenter({ lat, lng }); map.setZoom(16);
        marker.setPosition({ lat, lng });
        setLocation(lat, lng, place.formatted_address || input.value, placeId);
    });

    // map click
    map.addListener('click', function(e) {
        marker.setPosition(e.latLng);
        setLocation(e.latLng.lat(), e.latLng.lng(), '');
    });
}

function setLocation(lat, lng, address, placeId = '') {
    document.getElementById('latInput').value  = lat;
    document.getElementById('lngInput').value  = lng;
    if (placeId) document.getElementById('googlePlaceId').value = placeId;
    if (address) document.getElementById('deliveryAddress').value = address;
    locationSet = true;

    // Reverse geocode if no address
    if (!address) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
            if (status === 'OK' && results[0]) {
                document.getElementById('deliveryAddress').value = results[0].formatted_address;
                document.getElementById('addressSearch').value   = results[0].formatted_address;
            }
        });
    }
    calculateDelivery(lat, lng);
}

function calculateDelivery(lat, lng) {
    const statusEl = document.getElementById('deliveryStatus');
    statusEl.innerHTML = '<span class="text-warning small"><i class="bi bi-arrow-repeat me-1"></i>Menghitung jarak...</span>';

    fetch('/api/calculate-delivery', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ latitude: lat, longitude: lng })
    })
    .then(r => r.json())
    .then(data => {
        if (data.is_serviceable) {
            deliveryFee   = data.delivery_fee;
            isServiceable = true;
            statusEl.innerHTML = `<span class="text-success small"><i class="bi bi-check-circle me-1"></i>${data.distance_km} km — ${data.message}</span>`;
        } else {
            deliveryFee   = 0;
            isServiceable = false;
            statusEl.innerHTML = `<span class="text-danger small"><i class="bi bi-x-circle me-1"></i>${data.message}</span>`;
        }
        updatePrices();
    })
    .catch(() => {
        statusEl.innerHTML = '<span class="text-danger small">Gagal menghitung jarak. Coba lagi.</span>';
        isServiceable = false;
        validateForm();
    });
}

// Init on page load
window.initMap = initMap;
document.getElementById('packageSelect').dispatchEvent(new Event('change'));
</script>

@if(config('services.google_maps.browser_key'))
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.browser_key') }}&libraries=places&callback=initMap" async defer></script>
@else
<script>
// Google Maps key belum dikonfigurasi — tampilkan placeholder
document.getElementById('map').innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;background:#f3f4f6;flex-direction:column;gap:8px;"><i class="bi bi-map" style="font-size:3rem;color:#9ca3af;"></i><p class="text-muted small">Konfigurasi GOOGLE_MAPS_BROWSER_KEY pada .env</p></div>';
// Enable submit without maps for development
locationSet   = true;
isServiceable = true;
document.getElementById('latInput').value  = -6.2;
document.getElementById('lngInput').value  = 106.8;
document.getElementById('deliveryAddress').value = 'Alamat pengujian';
updatePrices();
</script>
@endif
@endpush
