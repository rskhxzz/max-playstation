@extends('layouts.admin')
@section('title', 'Pembayaran')
@section('page-title', 'Daftar Pembayaran')

@section('content')
<div class="card mb-3 p-3">
    <form method="GET" id="filterForm" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label mb-1" style="font-size:0.8rem;">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach(['pending'=>'Menunggu','succeeded'=>'Berhasil','expired'=>'Kedaluwarsa','failed'=>'Gagal'] as $k=>$v)
                <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
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
        <div class="col-auto ms-auto">
            <div class="stat-card py-2 px-3 d-inline-block">
                <div class="text-muted small">Total Pemasukan (filter ini)</div>
                <div style="font-weight:700;color:var(--clr-magenta);">Rp {{ number_format($totalPaid,0,',','.') }}</div>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Pembayaran
            <span class="ms-2 badge" style="background:rgba(255,255,255,0.15);font-size:0.72rem;font-weight:400;">
                {{ $payments->total() }} data
            </span>
        </h6>
        {{-- Tombol Export: teruskan filter yang sedang aktif --}}
        <a id="exportBtn"
           href="{{ route('admin.payments.export', request()->query()) }}"
           class="btn btn-sm d-flex align-items-center gap-1"
           style="background:#10b981;color:#fff;border-radius:7px;font-size:0.8rem;font-weight:500;padding:0.35rem 0.9rem;">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export .xlsx
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Kode</th><th>Booking</th><th>Customer</th><th>Tipe</th><th>Nominal</th><th>Dibayar</th><th>Status</th><th>Dibayar Pada</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td><code style="font-size:0.78rem;">{{ $p->payment_code }}</code></td>
                    <td>
                        <a href="{{ route('admin.bookings.show', $p->booking_id) }}" style="font-size:0.82rem;">
                            {{ $p->booking?->booking_code ?? '—' }}
                        </a>
                    </td>
                    <td style="font-size:0.82rem;">{{ $p->booking?->customer?->full_name ?? '—' }}</td>
                    <td>{{ $p->payment_type === 'initial' ? 'Awal' : 'Pelunasan' }}</td>
                    <td>Rp {{ number_format($p->requested_amount,0,',','.') }}</td>
                    <td>Rp {{ number_format($p->paid_amount,0,',','.') }}</td>
                    <td>
                        <span class="badge bg-{{ $p->status === 'succeeded' ? 'success' : ($p->status === 'pending' ? 'warning' : 'danger') }}">
                            {{ $p->status_label }}
                        </span>
                    </td>
                    <td>{{ $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->timezone('Asia/Jakarta')->format('d/m H:i') : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())<div class="p-3">{{ $payments->links() }}</div>@endif
</div>
@endsection

@push('scripts')
<script>
// Update href exportBtn setiap kali input filter berubah (real-time)
const filterForm = document.getElementById('filterForm');
const exportBtn  = document.getElementById('exportBtn');
const baseExport = '{{ route("admin.payments.export") }}';

filterForm?.addEventListener('change', syncExportUrl);
filterForm?.addEventListener('keyup',  syncExportUrl);

function syncExportUrl() {
    const params = new URLSearchParams(new FormData(filterForm));
    for (const [k, v] of [...params.entries()]) {
        if (!v) params.delete(k);
    }
    exportBtn.href = baseExport + (params.toString() ? '?' + params.toString() : '');
}
syncExportUrl();
</script>
@endpush
