@extends('layouts.admin')
@section('title', 'Pengguna')
@section('page-title', 'Pengguna')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="border-radius:8px;">
        <i class="bi bi-person-plus me-1"></i>Tambah Pengguna
    </a>
</div>
<div class="card">
    <div class="card-header"><h6><i class="bi bi-people me-2"></i>Pengguna Admin / Driver</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Aktif</th><th>Login Terakhir</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->username }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                        @if(strtolower($u->role?->name ?? '') === 'admin')
                            <span class="badge" style="background:var(--clr-magenta);">Admin</span>
                        @else
                            <span class="badge bg-info text-dark">{{ $u->role?->name ?? '—' }}</span>
                        @endif
                    </td>
                    <td>@if($u->active)<span class="badge bg-success">Ya</span>@else<span class="badge bg-danger">Tidak</span>@endif</td>
                    <td>{{ $u->last_login ? \Carbon\Carbon::parse($u->last_login)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '—' }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;border-radius:6px;">Edit</a>
                        @if($u->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;border-radius:6px;"
                                    onclick="return confirm('Hapus pengguna ini?')">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="p-3">{{ $users->links() }}</div>@endif
</div>
@endsection
