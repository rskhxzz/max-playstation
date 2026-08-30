@extends('layouts.admin')
@section('title', 'Unit PlayStation')
@section('page-title', 'Unit PlayStation')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.units.create') }}" class="btn btn-primary" style="border-radius:8px;">
        <i class="bi bi-plus-circle me-1"></i>Tambah Unit
    </a>
</div>
@include('components.alert')
<div class="card">
    <div class="card-header"><h6><i class="bi bi-controller me-2"></i>Daftar Unit</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Serial</th><th>Status</th><th>Aktif</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($units as $u)
                <tr>
                    <td><code>{{ $u->unit_code }}</code></td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->console_type ?? '—' }}</td>
                    <td>{{ $u->serial_number ?? '—' }}</td>
                    <td>
                        @php $stColors = ['available'=>'success','maintenance'=>'warning','inactive'=>'secondary'] @endphp
                        <span class="badge bg-{{ $stColors[$u->status] ?? 'secondary' }}">{{ ucfirst($u->status) }}</span>
                    </td>
                    <td>
                        @if($u->is_active) <span class="badge bg-success">Ya</span> @else <span class="badge bg-secondary">Tidak</span> @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.units.edit', $u->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;border-radius:6px;">Edit</a>
                        <form action="{{ route('admin.units.destroy', $u->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;border-radius:6px;"
                                    onclick="return confirm('Hapus unit ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada unit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($units->hasPages())<div class="p-3">{{ $units->links() }}</div>@endif
</div>
@endsection
