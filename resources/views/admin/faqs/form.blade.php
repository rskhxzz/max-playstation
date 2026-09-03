@extends('layouts.admin')
@section('title', isset($faq) ? 'Edit FAQ' : 'Tambah FAQ')
@section('page-title', isset($faq) ? 'Edit FAQ' : 'Tambah FAQ')

@section('content')
<a href="{{ route('admin.faqs.index') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:8px;">
    <i class="bi bi-arrow-left me-1"></i>Kembali
</a>
<div class="card" style="max-width:700px;">
    <div class="card-header"><h6>{{ isset($faq) ? 'Edit' : 'Tambah' }} FAQ</h6></div>
    <div class="card-body">
        <form action="{{ isset($faq) ? route('admin.faqs.update', $faq->id) : route('admin.faqs.store') }}" method="POST">
            @csrf @if(isset($faq)) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-10">
                    <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                    <input type="text" name="question" class="form-control @error('question') is-invalid @enderror"
                           value="{{ old('question', $faq->question ?? '') }}">
                    @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="seq" class="form-control" value="{{ old('seq', $faq->seq ?? 0) }}" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Jawaban <span class="text-danger">*</span></label>
                    <textarea name="answer" class="form-control @error('answer') is-invalid @enderror" rows="5">{{ old('answer', $faq->answer ?? '') }}</textarea>
                    @error('answer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1"
                               {{ old('is_published', $faq->is_published ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Dipublish di halaman utama</label>
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
