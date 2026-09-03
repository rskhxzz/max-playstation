@extends('layouts.admin')
@section('title', 'Syarat & Ketentuan')
@section('page-title', 'Syarat & Ketentuan')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.terms.create') }}" class="btn btn-primary" style="border-radius:8px;">
        <i class="bi bi-plus-circle me-1"></i>Tambah Versi Baru
    </a>
</div>
<div class="card">
    <div class="card-header"><h6><i class="bi bi-file-text me-2"></i>Syarat & Ketentuan</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Versi</th><th>Judul</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($terms as $t)
                <tr>
                    <td><span class="badge bg-secondary">v{{ $t->version }}</span></td>
                    <td>{{ $t->title }}</td>
                    <td>@if($t->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Nonaktif</span>@endif</td>
                    <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.terms.edit', $t->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;border-radius:6px;">Edit</a>
                        <form action="{{ route('admin.terms.destroy', $t->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;border-radius:6px;"
                                    onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada syarat dan ketentuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($terms->hasPages())<div class="p-3">{{ $terms->links() }}</div>@endif
</div>
@endsection
