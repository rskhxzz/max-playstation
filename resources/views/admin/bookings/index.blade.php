@extends('layouts.admin')
@section('title', 'Pemesanan')
@section('page-title', 'Daftar Pemesanan')

@section('content')
<div class="card mb-3 p-3">
    <form method="GET" id="filterForm" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label mb-1" style="font-size:0.8rem;">Cari</label>
            <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Kode, nama, HP...">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;">Status Pesanan</label>
            <select name="booking_status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach(['pending_payment'=>'Menunggu Bayar','delivered'=>'Siap Diantar','arrived'=>'Sudah Sampai','expired'=>'Kedaluwarsa','canceled'=>'Dibatalkan'] as $k=>$v)
                <option value="{{ $k }}" {{ request('booking_status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;">Status Bayar</label>
            <select name="payment_status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach(['unpaid'=>'Belum Bayar','partial'=>'Sudah DP','paid'=>'Lunas'] as $k=>$v)
                <option value="{{ $k }}" {{ request('payment_status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;">Dari</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;">Hingga</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary btn-sm w-100">Cari</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-bag-check me-2"></i>Pemesanan
            <span class="ms-2 badge" style="background:rgba(255,255,255,0.15);font-size:0.72rem;font-weight:400;">
                {{ $bookings->total() }} data
            </span>
        </h6>
        {{-- Tombol Export: teruskan filter yang sedang aktif --}}
        <a id="exportBtn"
           href="{{ route('admin.bookings.export', request()->query()) }}"
           class="btn btn-sm d-flex align-items-center gap-1"
           style="background:#10b981;color:#fff;border-radius:7px;font-size:0.8rem;font-weight:500;padding:0.35rem 0.9rem;">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export .xlsx
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Kode</th><th>Customer</th><th>Paket</th><th>Jadwal Sewa</th>
                    <th>Total</th><th>Status Pesanan</th><th>Status Bayar</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr>
                    <td><code>{{ $b->booking_code }}</code></td>
                    <td>
                        <div>{{ $b->customer?->full_name ?? '—' }}</div>
                        <small class="text-muted">{{ $b->customer?->phone_number }}</small>
                    </td>
                    <td>{{ $b->rentalPackage?->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($b->rental_start_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                    <td>Rp {{ number_format($b->total_amount,0,',','.') }}</td>
                    <td><span class="badge bg-{{ $b->booking_status_badge }}">{{ $b->booking_status_label }}</span></td>
                    <td><span class="badge bg-{{ $b->payment_status_badge }}">{{ $b->payment_status_label }}</span></td>
                    <td>
                        <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;border-radius:6px;">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->hasPages())
    <div class="p-3">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Pastikan tombol export selalu membawa query filter terbaru dari form
document.getElementById('filterForm')?.addEventListener('submit', function () {
    // Biarkan form submit normal (GET). URL akan diperbarui oleh browser.
    // exportBtn akan ikut saat halaman di-reload.
});

// Update href exportBtn setiap kali input filter berubah (real-time)
const filterForm = document.getElementById('filterForm');
const exportBtn  = document.getElementById('exportBtn');
const baseExport = '{{ route("admin.bookings.export") }}';

filterForm?.addEventListener('change', syncExportUrl);
filterForm?.addEventListener('keyup',  syncExportUrl);

function syncExportUrl() {
    const params = new URLSearchParams(new FormData(filterForm));
    // Hapus entri kosong
    for (const [k, v] of [...params.entries()]) {
        if (!v) params.delete(k);
    }
    exportBtn.href = baseExport + (params.toString() ? '?' + params.toString() : '');
}
syncExportUrl();
</script>
@endpush
