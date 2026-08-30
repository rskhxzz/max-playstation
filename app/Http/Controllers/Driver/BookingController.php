<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function show(string $id)
    {
        $booking = Booking::with(['customer', 'rentalPackage', 'playstationUnit', 'payments'])
            ->where('booking_status', 'delivered')
            ->findOrFail($id);

        $snapToken = null;
        if ($booking->payment_status === 'partial') {
            $pending = $booking->payments()
                ->where('payment_type', 'remaining')
                ->where('status', 'pending')
                ->first();
            $snapToken = $pending?->qr_string;
        }

        $clientKey = config('services.midtrans.client_key');

        return view('driver.bookings.show', compact('booking', 'snapToken', 'clientKey'));
    }

    public function createRemainingPayment(string $id)
    {
        $booking = Booking::where('booking_status', 'delivered')
            ->where('payment_status', 'partial')
            ->findOrFail($id);

        try {
            $payment = $this->paymentService->createRemainingPayment($booking, auth()->id());
            return response()->json([
                'success'    => true,
                'snap_token' => $payment->qr_string,
                'message'    => 'QRIS berhasil dibuat.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function markArrived(Request $request, string $id)
    {
        $booking = Booking::where('booking_status', 'delivered')->findOrFail($id);

        // Driver hanya boleh selesai jika sudah lunas
        if ($booking->payment_status !== 'paid') {
            return redirect()->back()->withErrors(['error' => 'Pelunasan belum selesai. Selesaikan pembayaran terlebih dahulu.'])
                ->withInput();
        }

        $booking->booking_status = 'arrived';
        $booking->arrived_at     = now();
        $booking->updated_by     = auth()->id();
        $booking->save();

        return redirect()->route('driver.dashboard')
            ->with('success', 'Pesanan ditandai sudah sampai.');
    }
}
