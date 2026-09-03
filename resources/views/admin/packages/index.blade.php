@extends('layouts.admin')
@section('title', 'Paket Sewa')
@section('page-title', 'Paket Sewa')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div></div>
    <a href="{{ route('admin.packages.create') }}" class="btn btn-primary" style="border-radius:8px;">
        <i class="bi bi-plus-circle me-1"></i>Tambah Paket
    </a>
</div>


<div class="card">
    <div class="card-header"><h6><i class="bi bi-box me-2"></i>Daftar Paket</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Kode</th><th>Nama</th><th>Durasi</th><th>Harga</th><th>Blokir Jam</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($packages as $p)
                <tr>
                    <td><code>{{ $p->code }}</code></td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->duration_hours }} Jam</td>
                    <td>Rp {{ number_format($p->price,0,',','.') }}</td>
                    <td>
                        @if($p->blocked_start_time)
                            {{ substr($p->blocked_start_time,0,5) }}–{{ $p->blocked_end_time === '00:00:00' ? '24:00' : substr($p->blocked_end_time,0,5) }}
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td>
                        @if($p->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.packages.edit', $p->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:6px;font-size:0.75rem;">Edit</a>
                        <form action="{{ route('admin.packages.destroy', $p->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:6px;font-size:0.75rem;"
                                    onclick="return confirm('Hapus paket ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada paket.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($packages->hasPages())
    <div class="p-3">{{ $packages->links() }}</div>
    @endif
</div>
@endsection
