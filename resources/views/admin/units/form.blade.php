@extends('layouts.admin')
@section('title', isset($unit) ? 'Edit Unit' : 'Tambah Unit')
@section('page-title', isset($unit) ? 'Edit Unit' : 'Tambah Unit')

@section('content')
<a href="{{ route('admin.units.index') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:8px;">
    <i class="bi bi-arrow-left me-1"></i>Kembali
</a>
<div class="card" style="max-width:600px;">
    <div class="card-header"><h6>{{ isset($unit) ? 'Edit' : 'Tambah' }} Unit PlayStation</h6></div>
    <div class="card-body">
        <form action="{{ isset($unit) ? route('admin.units.update', $unit->id) : route('admin.units.store') }}" method="POST">
            @csrf @if(isset($unit)) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kode Unit <span class="text-danger">*</span></label>
                    <input type="text" name="unit_code" class="form-control @error('unit_code') is-invalid @enderror"
                           value="{{ old('unit_code', $unit->unit_code ?? '') }}" placeholder="PS4-01">
                    @error('unit_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $unit->name ?? '') }}" placeholder="PlayStation 4 Unit 1">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipe Konsol</label>
                    <input type="text" name="console_type" class="form-control"
                           value="{{ old('console_type', $unit->console_type ?? '') }}" placeholder="PS4">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor Serial</label>
                    <input type="text" name="serial_number" class="form-control"
                           value="{{ old('serial_number', $unit->serial_number ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select">
                        @foreach(['available','maintenance','inactive'] as $s)
                        <option value="{{ $s }}" {{ old('status', $unit->status ?? 'available') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $unit->notes ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $unit->is_active ?? true) ? 'checked' : '' }}>
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
