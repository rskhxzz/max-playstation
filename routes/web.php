<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Driver;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// ─── Booking (publik, customer tidak perlu login) ────────────────────────────
Route::prefix('pesan')->name('booking.')->group(function () {
    Route::get('/',  [BookingController::class, 'create'])->name('create');
    Route::post('/', [BookingController::class, 'store'])->name('store')
        ->middleware('throttle:10,1');
});

Route::prefix('pesanan')->name('booking.')->group(function () {
    Route::get('/{bookingCode}/pembayaran', [BookingController::class, 'payment'])->name('payment');
    Route::get('/{bookingCode}/status',     [BookingController::class, 'status'])->name('status');
});

// ─── API: Delivery calculation ────────────────────────────────────────────────
Route::post('/api/calculate-delivery', [DeliveryController::class, 'calculate'])
    ->name('api.delivery')
    ->middleware('throttle:30,1');

// ─── Webhook (no CSRF) ────────────────────────────────────────────────────────
Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle'])
    ->name('webhook.midtrans')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// ─── Admin ────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Bookings
    Route::get('/pemesanan',             [Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/pemesanan/export',      [Admin\BookingController::class, 'export'])->name('bookings.export');
    Route::get('/pemesanan/{id}',        [Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/pemesanan/{id}/batal', [Admin\BookingController::class, 'cancel'])->name('bookings.cancel');

    // Payments
    Route::get('/pembayaran',            [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/pembayaran/export',     [Admin\PaymentController::class, 'export'])->name('payments.export');
    Route::get('/pembayaran/{id}',       [Admin\PaymentController::class, 'show'])->name('payments.show');

    // Packages
    Route::resource('/paket-sewa', Admin\RentalPackageController::class, [
        'names'    => 'packages',
        'only'     => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
    ]);

    // Units
    Route::resource('/unit-ps', Admin\PlaystationUnitController::class, [
        'names' => 'units',
        'only'  => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
    ]);

    // Delivery rates
    Route::resource('/tarif-pengiriman', Admin\DeliveryRateController::class, [
        'names' => 'delivery-rates',
        'only'  => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
    ]);

    // FAQ
    Route::resource('/faq', Admin\FaqController::class, [
        'names' => 'faqs',
        'only'  => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
    ]);

    // Terms
    Route::resource('/syarat-ketentuan', Admin\TermsConditionController::class, [
        'names' => 'terms',
        'only'  => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
    ]);

    // Business settings
    Route::get('/pengaturan',   [Admin\BusinessSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/pengaturan',   [Admin\BusinessSettingController::class, 'update'])->name('settings.update');

    // Users
    Route::resource('/pengguna', Admin\UserController::class, [
        'names' => 'users',
        'only'  => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
    ]);
});

// ─── Driver ───────────────────────────────────────────────────────────────────
Route::prefix('driver')->name('driver.')->middleware(['auth', 'role:Driver'])->group(function () {
    Route::get('/dashboard', [Driver\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pesanan/{id}',   [Driver\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/pesanan/{id}/qris-pelunasan', [Driver\BookingController::class, 'createRemainingPayment'])->name('bookings.remaining-payment');
    Route::post('/pesanan/{id}/selesai', [Driver\BookingController::class, 'markArrived'])->name('bookings.arrived');
});
