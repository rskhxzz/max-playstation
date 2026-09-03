@extends('layouts.admin')
@section('title', 'FAQ')
@section('page-title', 'FAQ')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary" style="border-radius:8px;">
        <i class="bi bi-plus-circle me-1"></i>Tambah FAQ
    </a>
</div>
<div class="card">
    <div class="card-header"><h6><i class="bi bi-question-circle me-2"></i>Daftar FAQ</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Urutan</th><th>Pertanyaan</th><th>Dipublish</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($faqs as $f)
                <tr>
                    <td>{{ $f->seq }}</td>
                    <td>{{ Str::limit($f->question, 80) }}</td>
                    <td>@if($f->is_published)<span class="badge bg-success">Ya</span>@else<span class="badge bg-secondary">Tidak</span>@endif</td>
                    <td>
                        <a href="{{ route('admin.faqs.edit', $f->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;border-radius:6px;">Edit</a>
                        <form action="{{ route('admin.faqs.destroy', $f->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;border-radius:6px;"
                                    onclick="return confirm('Hapus FAQ ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada FAQ.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($faqs->hasPages())<div class="p-3">{{ $faqs->links() }}</div>@endif
</div>
@endsection
