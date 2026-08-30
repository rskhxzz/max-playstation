@extends('layouts.admin')
@section('title', 'Pengaturan Usaha')
@section('page-title', 'Pengaturan Usaha')

@section('content')
@include('components.alert')
<div class="card" style="max-width:700px;">
    <div class="card-header"><h6><i class="bi bi-gear me-2"></i>Pengaturan Usaha</h6></div>
    <div class="card-body">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nama Usaha <span class="text-danger">*</span></label>
                    <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror"
                           value="{{ old('business_name', $setting->business_name ?? 'Maxibox Playstation') }}">
                    @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="phone_number" class="form-control"
                           value="{{ old('phone_number', $setting->phone_number ?? '') }}" placeholder="628xxxxxxxxxx">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $setting->address ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="any" name="latitude" class="form-control"
                           value="{{ old('latitude', $setting->latitude ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="any" name="longitude" class="form-control"
                           value="{{ old('longitude', $setting->longitude ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Google Place ID</label>
                    <input type="text" name="google_place_id" class="form-control"
                           value="{{ old('google_place_id', $setting->google_place_id ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jangkauan Maks (km)</label>
                    <input type="number" step="0.1" name="maximum_delivery_km" class="form-control"
                           value="{{ old('maximum_delivery_km', $setting->maximum_delivery_km ?? '') }}" placeholder="20">
                </div>
                <div class="col-md-6">
                    <label class="form-label">DP (Rp)</label>
                    <input type="number" name="down_payment_amount" class="form-control"
                           value="{{ old('down_payment_amount', $setting->down_payment_amount ?? 50000) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Masa Kadaluarsa Pembayaran (menit)</label>
                    <input type="number" name="payment_expiry_minutes" class="form-control"
                           value="{{ old('payment_expiry_minutes', $setting->payment_expiry_minutes ?? 60) }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
