@extends('layouts.admin')
@section('title', isset($terms) ? 'Edit S&K' : 'Tambah S&K')
@section('page-title', isset($terms) ? 'Edit Syarat & Ketentuan' : 'Tambah Syarat & Ketentuan')

@section('content')
<a href="{{ route('admin.terms.index') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:8px;">
    <i class="bi bi-arrow-left me-1"></i>Kembali
</a>
<div class="card" style="max-width:800px;">
    <div class="card-header"><h6>{{ isset($terms) ? 'Edit' : 'Tambah' }} Syarat & Ketentuan</h6></div>
    <div class="card-body">
        @if(!isset($terms) && isset($nextVersion))
        <div class="alert alert-info py-2 mb-3" style="font-size:0.85rem;">
            <i class="bi bi-info-circle me-1"></i>Versi baru akan dibuat: <strong>v{{ $nextVersion }}</strong>
        </div>
        @endif
        <form action="{{ isset($terms) ? route('admin.terms.update', $terms->id) : route('admin.terms.store') }}" method="POST">
            @csrf @if(isset($terms)) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Judul <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $terms->title ?? '') }}">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Isi <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="12">{{ old('content', $terms->content ?? '') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $terms->is_active ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Jadikan aktif (menonaktifkan versi lain)</label>
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
