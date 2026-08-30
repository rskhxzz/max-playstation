@extends('layouts.admin')
@section('title', isset($package) ? 'Edit Paket' : 'Tambah Paket')
@section('page-title', isset($package) ? 'Edit Paket Sewa' : 'Tambah Paket Sewa')

@section('content')
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('admin.packages.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card" style="max-width:600px;">
    <div class="card-header"><h6>{{ isset($package) ? 'Edit' : 'Tambah' }} Paket Sewa</h6></div>
    <div class="card-body">
        @include('components.alert')
        <form action="{{ isset($package) ? route('admin.packages.update', $package->id) : route('admin.packages.store') }}" method="POST">
            @csrf
            @if(isset($package)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $package->code ?? '') }}" placeholder="6JAM">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $package->name ?? '') }}" placeholder="Paket 6 Jam">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Durasi (Jam) <span class="text-danger">*</span></label>
                    <input type="number" name="duration_hours" class="form-control @error('duration_hours') is-invalid @enderror"
                           value="{{ old('duration_hours', $package->duration_hours ?? '') }}" min="1">
                    @error('duration_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $package->price ?? '') }}" min="0">
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Blokir Jam Mulai</label>
                    <input type="time" name="blocked_start_time" class="form-control"
                           value="{{ old('blocked_start_time', $package->blocked_start_time ? substr($package->blocked_start_time,0,5) : '') }}">
                    <div class="form-text">Kosongkan jika tidak ada pembatasan</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Blokir Jam Akhir</label>
                    <input type="time" name="blocked_end_time" class="form-control"
                           value="{{ old('blocked_end_time', $package->blocked_end_time ? substr($package->blocked_end_time,0,5) : '') }}">
                    <div class="form-text">Contoh: 00:00 = sampai tengah malam</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $package->description ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                               {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Aktif</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
