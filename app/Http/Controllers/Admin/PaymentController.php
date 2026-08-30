<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ExportService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // ─── Helper: bangun query berdasarkan filter request ─────────────────────
    private function buildQuery(Request $request)
    {
        $query = Payment::withoutGlobalScope('not_deleted')
            ->with(['booking.customer', 'booking.rentalPackage'])
            ->where('is_deleted', false);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    // ─── Daftar pembayaran (dengan pagination) ────────────────────────────────
    public function index(Request $request)
    {
        $payments  = $this->buildQuery($request)->latest()->paginate(15)->withQueryString();
        $totalPaid = Payment::where('status', 'succeeded')->sum('paid_amount');

        return view('admin.payments.index', compact('payments', 'totalPaid'));
    }

    // ─── Export ke .xlsx sesuai filter aktif ─────────────────────────────────
    public function export(Request $request, ExportService $exportService)
    {
        // Ambil SEMUA data sesuai filter (tanpa pagination)
        $payments = $this->buildQuery($request)->latest()->get();

        $filters = $request->only(['status', 'date_from', 'date_to']);

        return $exportService->exportPayments($payments, $filters);
    }

    // ─── Detail pembayaran ────────────────────────────────────────────────────
    public function show(string $id)
    {
        $payment = Payment::withoutGlobalScope('not_deleted')
            ->with(['booking.customer', 'booking.rentalPackage'])
            ->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }
}
