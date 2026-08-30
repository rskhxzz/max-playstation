@extends('layouts.admin')
@section('title', 'Tarif Pengiriman')
@section('page-title', 'Tarif Pengiriman')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.delivery-rates.create') }}" class="btn btn-primary" style="border-radius:8px;">
        <i class="bi bi-plus-circle me-1"></i>Tambah Tarif
    </a>
</div>
@include('components.alert')
<div class="card">
    <div class="card-header"><h6><i class="bi bi-truck me-2"></i>Tarif Pengiriman</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Nama Zona</th><th>Min Jarak</th><th>Maks Jarak</th><th>Tarif</th><th>Aktif</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($rates as $r)
                <tr>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->minimum_distance_km }} km</td>
                    <td>{{ $r->maximum_distance_km }} km</td>
                    <td>Rp {{ number_format($r->delivery_fee,0,',','.') }}</td>
                    <td>@if($r->is_active)<span class="badge bg-success">Ya</span>@else<span class="badge bg-secondary">Tidak</span>@endif</td>
                    <td>
                        <a href="{{ route('admin.delivery-rates.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;border-radius:6px;">Edit</a>
                        <form action="{{ route('admin.delivery-rates.destroy', $r->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;border-radius:6px;"
                                    onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada tarif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rates->hasPages())<div class="p-3">{{ $rates->links() }}</div>@endif
</div>
@endsection
