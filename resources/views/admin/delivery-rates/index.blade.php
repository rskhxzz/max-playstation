@extends('layouts.admin')
@section('title', 'Tarif Pengiriman')
@section('page-title', 'Tarif Pengiriman')

@section('content')
<style>
    .rate-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }

    .rate-note {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <div class="small text-muted">
        Atur ongkir customer, pendapatan driver, dan potongan bensin motor kantor.
    </div>

    <a
        href="{{ route('admin.delivery-rates.create') }}"
        class="btn btn-primary"
        style="border-radius:8px;">
        <i class="bi bi-plus-circle me-1"></i>
        Tambah Tarif
    </a>
</div>

<div class="rate-note p-3 mb-3 small">
    <div class="fw-semibold mb-1">
        <i class="bi bi-info-circle me-1"></i>
        Cara kerja tarif
    </div>

    <div class="text-muted">
        Motor pribadi menerima seluruh pendapatan dasar driver.
        Motor kantor menerima pendapatan dasar dikurangi potongan bensin.
    </div>
</div>

<div class="rate-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h6
            class="mb-0"
            style="font-weight:700;">
            <i class="bi bi-truck me-2"></i>
            Daftar Tarif
        </h6>

        <span class="small text-muted">
            {{ $rates->total() }} data
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Zona</th>
                    <th>Jarak</th>
                    <th>Ongkir Customer</th>
                    <th>Pendapatan Driver</th>
                    <th>Bensin Motor Kantor</th>
                    <th>Pendapatan Driver Kantor</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rates as $rate)
                <tr>
                    <td class="fw-semibold">
                        {{ $rate->name }}
                    </td>

                    <td>
                        {{ number_format(
                            $rate->minimum_distance_km,
                            1,
                            ',',
                            '.'
                        ) }}
                        –
                        {{ number_format(
                            $rate->maximum_distance_km,
                            1,
                            ',',
                            '.'
                        ) }}
                        km
                    </td>

                    <td>
                        Rp {{ number_format(
                            $rate->delivery_fee,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="fw-semibold text-success">
                        Rp {{ number_format(
                            $rate->driver_fee,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td>
                        {{ $rate->company_fuel_deduction > 0
                            ? 'Rp '.number_format(
                                $rate->company_fuel_deduction,
                                0,
                                ',',
                                '.'
                            )
                            : '—' }}
                    </td>

                    <td class="fw-semibold">
                        Rp {{ number_format(
                            max(
                                0,
                                $rate->driver_fee
                                - $rate->company_fuel_deduction
                            ),
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td>
                        @if($rate->is_active)
                        <span class="badge bg-success">
                            Aktif
                        </span>
                        @else
                        <span class="badge bg-secondary">
                            Nonaktif
                        </span>
                        @endif
                    </td>

                    <td>
                        <div class="d-flex gap-1">
                            <a
                                href="{{ route(
                                    'admin.delivery-rates.edit',
                                    $rate->id
                                ) }}"
                                class="btn btn-sm btn-outline-primary"
                                style="border-radius:6px;"
                                title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form
                                action="{{ route(
                                    'admin.delivery-rates.destroy',
                                    $rate->id
                                ) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-outline-danger"
                                    style="border-radius:6px;"
                                    onclick="return confirm('Hapus tarif ini?')"
                                    title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td
                        colspan="8"
                        class="text-center text-muted py-5">
                        Belum ada tarif.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rates->hasPages())
    <div class="p-3">
        {{ $rates->links() }}
    </div>
    @endif
</div>
@endsection