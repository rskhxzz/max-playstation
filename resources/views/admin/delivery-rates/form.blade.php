@extends('layouts.admin')
@section('title', isset($rate) ? 'Edit Tarif' : 'Tambah Tarif')
@section('page-title', isset($rate) ? 'Edit Tarif Pengiriman' : 'Tambah Tarif Pengiriman')

@section('content')
<style>
    .form-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        max-width: 760px;
    }

    .calc-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
</style>

<a
    href="{{ route('admin.delivery-rates.index') }}"
    class="btn btn-sm btn-outline-secondary mb-3"
    style="border-radius:8px;">
    <i class="bi bi-arrow-left me-1"></i>
    Kembali
</a>

<div class="form-card">
    <div class="p-3 border-bottom">
        <h6
            class="mb-1"
            style="font-weight:700;">
            {{ isset($rate) ? 'Edit' : 'Tambah' }} Tarif
        </h6>

        <div class="small text-muted">
            Gunakan tarif ini untuk menentukan ongkir customer dan pendapatan driver.
        </div>
    </div>

    <div class="p-3">
        <form
            action="{{ isset($rate)
                ? route(
                    'admin.delivery-rates.update',
                    $rate->id
                )
                : route('admin.delivery-rates.store') }}"
            method="POST">
            @csrf

            @if(isset($rate))
            @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">
                        Nama Zona
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old(
                            'name',
                            $rate->name ?? ''
                        ) }}"
                        placeholder="Contoh: 3–4 km"
                        required>

                    @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Jarak Minimum (km)
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        name="minimum_distance_km"
                        class="form-control @error('minimum_distance_km') is-invalid @enderror"
                        value="{{ old(
                            'minimum_distance_km',
                            $rate->minimum_distance_km ?? ''
                        ) }}"
                        required>

                    @error('minimum_distance_km')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Jarak Maksimum (km)
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="number"
                        step="0.1"
                        min="0"
                        name="maximum_distance_km"
                        class="form-control @error('maximum_distance_km') is-invalid @enderror"
                        value="{{ old(
                            'maximum_distance_km',
                            $rate->maximum_distance_km ?? ''
                        ) }}"
                        required>

                    @error('maximum_distance_km')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Ongkir Customer (Rp)
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="number"
                        min="0"
                        name="delivery_fee"
                        class="form-control @error('delivery_fee') is-invalid @enderror"
                        value="{{ old(
                            'delivery_fee',
                            $rate->delivery_fee ?? ''
                        ) }}"
                        required>

                    @error('delivery_fee')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Pendapatan Driver (Rp)
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="number"
                        min="0"
                        name="driver_fee"
                        id="driverFee"
                        class="form-control @error('driver_fee') is-invalid @enderror"
                        value="{{ old(
                            'driver_fee',
                            $rate->driver_fee ?? ''
                        ) }}"
                        required>

                    @error('driver_fee')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror

                    <div class="form-text">
                        Nilai sebelum potongan bensin motor kantor.
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Potongan Bensin Kantor (Rp)
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="number"
                        min="0"
                        name="company_fuel_deduction"
                        id="fuelDeduction"
                        class="form-control @error('company_fuel_deduction') is-invalid @enderror"
                        value="{{ old(
                            'company_fuel_deduction',
                            $rate->company_fuel_deduction ?? 0
                        ) }}"
                        required>

                    @error('company_fuel_deduction')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="calc-box p-3">
                        <div class="small text-muted mb-2">
                            Preview Pendapatan Motor Kantor
                        </div>

                        <div
                            class="fs-5 fw-bold text-success"
                            id="companyIncomePreview">
                            Rp 0
                        </div>

                        <div class="small text-muted mt-1">
                            Pendapatan driver = pendapatan dasar − potongan bensin.
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            id="isActive"
                            {{ old(
                                'is_active',
                                $rate->is_active ?? true
                            ) ? 'checked' : '' }}>

                        <label
                            class="form-check-label"
                            for="isActive">
                            Tarif aktif
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <button
                        type="submit"
                        class="btn btn-primary"
                        style="border-radius:8px;">
                        <i class="bi bi-save me-1"></i>
                        Simpan Tarif
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const driverFeeInput =
        document.getElementById('driverFee');

    const fuelInput =
        document.getElementById('fuelDeduction');

    const preview =
        document.getElementById(
            'companyIncomePreview'
        );

    function formatRupiah(value) {
        return new Intl.NumberFormat(
            'id-ID'
        ).format(
            Math.max(0, value)
        );
    }

    function updatePreview() {
        const driverFee =
            Number(
                driverFeeInput?.value || 0
            );

        const fuel =
            Number(
                fuelInput?.value || 0
            );

        preview.textContent =
            `Rp ${formatRupiah(
            driverFee - fuel
        )}`;
    }

    driverFeeInput?.addEventListener(
        'input',
        updatePreview
    );

    fuelInput?.addEventListener(
        'input',
        updatePreview
    );

    updatePreview();
</script>
@endpush