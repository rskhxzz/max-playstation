@extends('layouts.admin')
@section('title', 'Pemesanan')
@section('page-title', 'Daftar Pemesanan')

@section('content')
<div class="card mb-3 p-3">
    <form
        method="GET"
        id="filterForm"
        class="row g-2 align-items-end">
        <div class="col-lg-2 col-md-4">
            <label class="form-label mb-1">
                Cari
            </label>

            <input
                type="text"
                name="search"
                class="form-control form-control-sm"
                value="{{ request('search') }}"
                placeholder="Kode, nama, HP...">
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label mb-1">
                Status Pesanan
            </label>

            <select
                name="booking_status"
                class="form-select form-select-sm">
                <option value="">
                    Semua
                </option>

                @foreach([
                'pending_payment' => 'Menunggu Bayar',
                'delivered' => 'Siap Diantar',
                'assigned' => 'Ditugaskan',
                'on_delivery' => 'Sedang Diantar',
                'arrived' => 'Sudah Sampai',
                'completed' => 'Selesai',
                'expired' => 'Kedaluwarsa',
                'canceled' => 'Dibatalkan',
                ] as $key => $label)
                <option
                    value="{{ $key }}"
                    {{ request('booking_status') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label mb-1">
                Pembayaran
            </label>

            <select
                name="payment_status"
                class="form-select form-select-sm">
                <option value="">
                    Semua
                </option>

                @foreach([
                'unpaid' => 'Belum Bayar',
                'partial' => 'Sudah DP',
                'paid' => 'Lunas',
                ] as $key => $label)
                <option
                    value="{{ $key }}"
                    {{ request('payment_status') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label mb-1">
                Driver
            </label>

            <select
                name="driver_id"
                class="form-select form-select-sm">
                <option value="">
                    Semua Driver
                </option>

                @foreach($drivers as $driver)
                <option
                    value="{{ $driver->id }}"
                    {{ request('driver_id') === $driver->id ? 'selected' : '' }}>
                    {{ $driver->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-1 col-md-4">
            <label class="form-label mb-1">
                Dari
            </label>

            <input
                type="date"
                name="date_from"
                class="form-control form-control-sm"
                value="{{ request('date_from') }}">
        </div>

        <div class="col-lg-1 col-md-4">
            <label class="form-label mb-1">
                Sampai
            </label>

            <input
                type="date"
                name="date_to"
                class="form-control form-control-sm"
                value="{{ request('date_to') }}">
        </div>

        <div class="col-lg-2 col-md-4 d-flex gap-2">
            <button
                type="submit"
                class="btn btn-primary btn-sm flex-fill">
                <i class="bi bi-filter me-1"></i>
                Filter
            </button>

            <a
                href="{{ route('admin.bookings.index') }}"
                class="btn btn-outline-secondary btn-sm"
                title="Reset">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h6 class="mb-0">
            <i class="bi bi-bag-check me-2"></i>
            Pemesanan

            <span
                class="ms-2 badge"
                style="background:rgba(255,255,255,.15);font-size:.72rem;font-weight:400;">
                {{ $bookings->total() }} data
            </span>
        </h6>

        <a
            id="exportBtn"
            href="{{ route('admin.bookings.export', request()->query()) }}"
            data-export-base="{{ route('admin.bookings.export') }}"
            class="btn btn-sm d-flex align-items-center gap-1"
            style="background:#10b981;color:#fff;border-radius:7px;font-size:.8rem;font-weight:500;padding:.35rem .9rem;">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            Export .xlsx
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Driver</th>
                    <th>Kendaraan</th>
                    <th>Jadwal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td>
                        <code>
                            {{ $booking->booking_code }}
                        </code>
                    </td>

                    <td>
                        <div class="fw-semibold">
                            {{ $booking->customer?->full_name ?? '—' }}
                        </div>

                        <small class="text-muted">
                            {{ $booking->customer?->phone_number ?? '—' }}
                        </small>
                    </td>

                    <td>
                        {{ $booking->driver?->name ?? 'Belum ada' }}
                    </td>

                    <td>
                        @if($booking->vehicle_type === 'personal')
                        Motor Pribadi
                        @elseif($booking->vehicle_type === 'company')
                        Motor Kantor
                        @else
                        —
                        @endif
                    </td>

                    <td>
                        {{ $booking->rental_start_at
                                ? $booking->rental_start_at
                                    ->timezone('Asia/Jakarta')
                                    ->format('d/m/Y H:i')
                                : '—' }}
                    </td>

                    <td>
                        Rp {{ number_format(
                                $booking->total_amount,
                                0,
                                ',',
                                '.'
                            ) }}
                    </td>

                    <td>
                        <span class="badge bg-{{ $booking->booking_status_badge }}">
                            {{ $booking->booking_status_label }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-{{ $booking->payment_status_badge }}">
                            {{ $booking->payment_status_label }}
                        </span>
                    </td>

                    <td>
                        <a
                            href="{{ route(
                                    'admin.bookings.show',
                                    $booking->id
                                ) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td
                        colspan="9"
                        class="text-center text-muted py-4">
                        Tidak ada data.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
    <div class="p-3">
        {{ $bookings->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const filterForm =
            document.getElementById('filterForm');

        const exportBtn =
            document.getElementById('exportBtn');

        if (!filterForm || !exportBtn) {
            return;
        }

        const exportBase =
            exportBtn.dataset.exportBase;

        function syncExportUrl() {
            const params =
                new URLSearchParams(
                    new FormData(filterForm)
                );

            for (const [key, value] of params.entries()) {
                if (!value) {
                    params.delete(key);
                }
            }

            const query =
                params.toString();

            exportBtn.href =
                query ?
                exportBase + '?' + query :
                exportBase;
        }

        filterForm.addEventListener(
            'change',
            syncExportUrl
        );

        filterForm.addEventListener(
            'input',
            syncExportUrl
        );

        syncExportUrl();
    })();
</script>
@endpush