<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\Driver;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home');

Route::prefix('pesan')
    ->name('booking.')
    ->group(function () {
        Route::get(
            '/',
            [BookingController::class, 'create']
        )->name('create');

        Route::post(
            '/',
            [BookingController::class, 'store']
        )
            ->name('store')
            ->middleware('throttle:10,1');
    });

Route::prefix('pesanan')
    ->name('booking.')
    ->group(function () {
        Route::get(
            '/{bookingCode}/pembayaran',
            [BookingController::class, 'payment']
        )->name('payment');

        Route::get(
            '/{bookingCode}/status',
            [BookingController::class, 'status']
        )->name('status');
    });

Route::post(
    '/api/calculate-delivery',
    [DeliveryController::class, 'calculate']
)
    ->name('api.delivery')
    ->middleware('throttle:30,1');

Route::post(
    '/webhooks/midtrans',
    [MidtransWebhookController::class, 'handle']
)
    ->name('webhook.midtrans')
    ->withoutMiddleware([
        VerifyCsrfToken::class,
    ]);

Route::get(
    '/login',
    [AuthController::class, 'showLogin']
)->name('login');

Route::post(
    '/login',
    [AuthController::class, 'login']
)->middleware('throttle:5,1');

Route::post(
    '/logout',
    [AuthController::class, 'logout']
)->name('logout');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth.admin'])
    ->group(function () {
        Route::get(
            '/dashboard',
            [Admin\DashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/pemesanan',
            [Admin\BookingController::class, 'index']
        )->name('bookings.index');

        Route::get(
            '/pemesanan/export',
            [Admin\BookingController::class, 'export']
        )->name('bookings.export');

        Route::get(
            '/pemesanan/{id}/foto',
            [Admin\BookingController::class, 'photo']
        )->name('bookings.photo');

        Route::post(
            '/pemesanan/{id}/driver',
            [Admin\BookingController::class, 'assignDriver']
        )->name('bookings.assign-driver');

        Route::post(
            '/pemesanan/{id}/batal',
            [Admin\BookingController::class, 'cancel']
        )->name('bookings.cancel');

        Route::get(
            '/pemesanan/{id}',
            [Admin\BookingController::class, 'show']
        )->name('bookings.show');

        Route::get(
            '/pembayaran',
            [Admin\PaymentController::class, 'index']
        )->name('payments.index');

        Route::get(
            '/pembayaran/export',
            [Admin\PaymentController::class, 'export']
        )->name('payments.export');

        Route::get(
            '/pembayaran/{id}',
            [Admin\PaymentController::class, 'show']
        )->name('payments.show');

        Route::resource(
            '/paket-sewa',
            Admin\RentalPackageController::class,
            [
                'names' => 'packages',
                'only' => [
                    'index',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ],
            ]
        );

        Route::resource(
            '/unit-ps',
            Admin\PlaystationUnitController::class,
            [
                'names' => 'units',
                'only' => [
                    'index',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ],
            ]
        );

        Route::resource(
            '/tarif-pengiriman',
            Admin\DeliveryRateController::class,
            [
                'names' => 'delivery-rates',
                'only' => [
                    'index',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ],
            ]
        );

        Route::resource(
            '/faq',
            Admin\FaqController::class,
            [
                'names' => 'faqs',
                'only' => [
                    'index',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ],
            ]
        );

        Route::resource(
            '/syarat-ketentuan',
            Admin\TermsConditionController::class,
            [
                'names' => 'terms',
                'only' => [
                    'index',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ],
            ]
        );

        Route::get(
            '/pengaturan',
            [Admin\BusinessSettingController::class, 'edit']
        )->name('settings.edit');

        Route::put(
            '/pengaturan',
            [Admin\BusinessSettingController::class, 'update']
        )->name('settings.update');

        Route::resource(
            '/pengguna',
            Admin\UserController::class,
            [
                'names' => 'users',
                'only' => [
                    'index',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ],
            ]
        );
    });

Route::prefix('driver')
    ->name('driver.')
    ->middleware(['auth.driver'])
    ->group(function () {
        Route::get(
            '/dashboard',
            [Driver\DashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/pesanan/{id}/foto',
            [Driver\BookingController::class, 'photo']
        )->name('bookings.photo');

        Route::post(
            '/pesanan/{id}/terima',
            [Driver\BookingController::class, 'accept']
        )->name('bookings.accept');

        Route::post(
            '/pesanan/{id}/qris-pelunasan',
            [Driver\BookingController::class, 'createRemainingPayment']
        )->name('bookings.remaining-payment');

        Route::post(
            '/pesanan/{id}/cash-pelunasan',
            [Driver\BookingController::class, 'settleRemainingCash']
        )->name('bookings.cash-settlement');

        Route::post(
            '/pesanan/{id}/selesai-pengantaran',
            [Driver\BookingController::class, 'completeDelivery']
        )->name('bookings.complete-delivery');

        Route::post(
            '/pesanan/{id}/selesai-pickup',
            [Driver\BookingController::class, 'completePickup']
        )->name('bookings.complete-pickup');

        Route::post(
            '/pesanan/{id}/selesai',
            [Driver\BookingController::class, 'markArrived']
        )->name('bookings.arrived');

        Route::get(
            '/pesanan/{id}',
            [Driver\BookingController::class, 'show']
        )->name('bookings.show');
    });
