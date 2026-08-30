<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BusinessSetting;
use App\Models\RentalPackage;
use App\Models\TermsCondition;
use App\Services\BookingService;
use App\Http\Requests\BookingRequest;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function create()
    {
        $setting  = BusinessSetting::active();
        $packages = RentalPackage::where('is_active', true)->get();
        $terms    = TermsCondition::where('is_active', true)
            ->orderByDesc('version')
            ->first();

        return view('booking.create', compact('setting', 'packages', 'terms'));
    }

    public function store(BookingRequest $request)
    {
        try {
            $result = $this->bookingService->createBooking($request->validated());
            return redirect()->route('booking.payment', $result['booking']->booking_code)
                ->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            \Log::error('BookingController@store: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan sistem. Silakan coba lagi.']);
        }
    }

    public function payment(string $bookingCode)
    {
        $booking = Booking::withoutGlobalScope('not_deleted')
            ->where('booking_code', $bookingCode)
            ->with(['customer', 'rentalPackage', 'payments'])
            ->firstOrFail();

        $payment = $booking->payments()
            ->where('payment_type', 'initial')
            ->latest()
            ->first();

        $clientKey = config('services.midtrans.client_key');

        return view('booking.payment', compact('booking', 'payment', 'clientKey'));
    }

    public function status(string $bookingCode)
    {
        $booking = Booking::withoutGlobalScope('not_deleted')
            ->where('booking_code', $bookingCode)
            ->with(['customer', 'rentalPackage', 'payments'])
            ->firstOrFail();

        return view('booking.status', compact('booking'));
    }
}
