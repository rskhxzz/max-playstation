@extends('layouts.admin')
@section('title', isset($rate) ? 'Edit Tarif' : 'Tambah Tarif')
@section('page-title', isset($rate) ? 'Edit Tarif' : 'Tambah Tarif Pengiriman')

@section('content')
<a href="{{ route('admin.delivery-rates.index') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:8px;">
    <i class="bi bi-arrow-left me-1"></i>Kembali
</a>
<div class="card" style="max-width:500px;">
    <div class="card-header"><h6>{{ isset($rate) ? 'Edit' : 'Tambah' }} Tarif Pengiriman</h6></div>
    <div class="card-body">
        <form action="{{ isset($rate) ? route('admin.delivery-rates.update', $rate->id) : route('admin.delivery-rates.store') }}" method="POST">
            @csrf @if(isset($rate)) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nama Zona <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $rate->name ?? '') }}" placeholder="Zona 1 (0–5 km)">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jarak Min (km) <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" name="minimum_distance_km" class="form-control @error('minimum_distance_km') is-invalid @enderror"
                           value="{{ old('minimum_distance_km', $rate->minimum_distance_km ?? '') }}">
                    @error('minimum_distance_km')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jarak Maks (km) <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" name="maximum_distance_km" class="form-control @error('maximum_distance_km') is-invalid @enderror"
                           value="{{ old('maximum_distance_km', $rate->maximum_distance_km ?? '') }}">
                    @error('maximum_distance_km')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Tarif (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="delivery_fee" class="form-control @error('delivery_fee') is-invalid @enderror"
                           value="{{ old('delivery_fee', $rate->delivery_fee ?? '') }}">
                    @error('delivery_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $rate->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
