<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Str;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey       = config('services.midtrans.server_key');
        Config::$isProduction    = config('services.midtrans.is_production', false);
        Config::$isSanitized     = config('services.midtrans.is_sanitized', true);
        Config::$is3ds           = config('services.midtrans.is_3ds', true);
    }

    /**
     * Create a Snap payment transaction and return token + redirect URL.
     */
    public function createSnapTransaction(
    string $orderId,
    float $amount,
    array $customerDetails
): array {
    $params = [
        'transaction_details' => [
            'order_id'     => $orderId,
            'gross_amount' => (int) $amount,
        ],
        'customer_details' => $customerDetails,
        'enabled_payments' => ['other_qris'],
        'expiry' => [
            'unit'     => 'minutes',
            'duration' => 60,
        ],
    ];

    $response = Snap::createTransaction($params);

    return [
        'snap_token'   => $response->token,
        'redirect_url' => $response->redirect_url ?? '',
    ];
}

    /**
     * Verify webhook signature from Midtrans.
     */
    public function verifySignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signature
    ): bool {
        $serverKey = config('services.midtrans.server_key');
        $hash      = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($hash, $signature);
    }
}
