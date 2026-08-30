<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RentalPackage;
use App\Services\ExportService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // ─── Helper: bangun query berdasarkan filter request ─────────────────────
    private function buildQuery(Request $request)
    {
        $query = Booking::withoutGlobalScope('not_deleted')
            ->with(['customer', 'rentalPackage', 'playstationUnit'])
            ->where('is_deleted', false);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('booking_code', 'ilike', "%{$s}%")
                  ->orWhereHas('customer', fn($q2) => $q2->where('full_name', 'ilike', "%{$s}%")
                      ->orWhere('phone_number', 'ilike', "%{$s}%"));
            });
        }

        if ($request->filled('booking_status')) {
            $query->where('booking_status', $request->booking_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('package_id')) {
            $query->where('rental_package_id', $request->package_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    // ─── Daftar pemesanan (dengan pagination) ────────────────────────────────
    public function index(Request $request)
    {
        $bookings = $this->buildQuery($request)->latest()->paginate(15)->withQueryString();
        $packages = RentalPackage::where('is_active', true)->get();

        return view('admin.bookings.index', compact('bookings', 'packages'));
    }

    // ─── Export ke .xlsx sesuai filter aktif ─────────────────────────────────
    public function export(Request $request, ExportService $exportService)
    {
        // Ambil SEMUA data sesuai filter (tanpa pagination)
        $bookings = $this->buildQuery($request)->latest()->get();

        $filters = $request->only(['search', 'booking_status', 'payment_status', 'package_id', 'date_from', 'date_to']);

        // Resolusi nama paket pada label filter
        if (!empty($filters['package_id'])) {
            $pkg = RentalPackage::find($filters['package_id']);
            $filters['package_id'] = $pkg?->name ?? $filters['package_id'];
        }

        return $exportService->exportBookings($bookings, $filters);
    }

    // ─── Detail pesanan ───────────────────────────────────────────────────────
    public function show(string $id)
    {
        $booking = Booking::withoutGlobalScope('not_deleted')
            ->with(['customer', 'rentalPackage', 'playstationUnit', 'payments', 'termsCondition'])
            ->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    // ─── Batalkan pesanan ─────────────────────────────────────────────────────
    public function cancel(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        if (!in_array($booking->booking_status, ['pending_payment', 'delivered'])) {
            return back()->withErrors(['error' => 'Booking tidak dapat dibatalkan pada status ini.']);
        }

        $booking->booking_status      = 'canceled';
        $booking->cancellation_reason = $request->reason;
        $booking->updated_by          = auth()->id();
        $booking->save();

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
