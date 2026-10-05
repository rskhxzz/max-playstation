<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config(
            'services.midtrans.server_key',
            ''
        );

        Config::$isProduction = (bool) config(
            'services.midtrans.is_production',
            false
        );

        Config::$isSanitized = (bool) config(
            'services.midtrans.is_sanitized',
            true
        );

        Config::$is3ds = (bool) config(
            'services.midtrans.is_3ds',
            true
        );
    }

    public function createSnapTransaction(
        string $orderId,
        float $amount,
        array $customerDetails
    ): array {
        $this->ensureConfigured();

        if ($orderId === '') {
            throw new \InvalidArgumentException(
                'Order ID Midtrans tidak boleh kosong.'
            );
        }

        $grossAmount = (int) round($amount);

        if ($grossAmount <= 0) {
            throw new \InvalidArgumentException(
                'Nominal pembayaran harus lebih dari 0.'
            );
        }

        $expiryMinutes = max(
            1,
            (int) config(
                'services.midtrans.expiry_minutes',
                60
            )
        );

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $customerDetails,
            'enabled_payments' => [
                'other_qris',
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => $expiryMinutes,
            ],
        ];

        $response = Snap::createTransaction($params);

        $token = (string) Arr::get(
            (array) $response,
            'token',
            ''
        );

        $redirectUrl = (string) Arr::get(
            (array) $response,
            'redirect_url',
            ''
        );

        if ($token === '') {
            throw new \RuntimeException(
                'Token pembayaran Midtrans tidak diterima.'
            );
        }

        return [
            'snap_token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }

    public function verifySignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signature
    ): bool {
        $serverKey = config(
            'services.midtrans.server_key',
            ''
        );

        if (
            $serverKey === ''
            || $signature === ''
        ) {
            return false;
        }

        $hash = hash(
            'sha512',
            $orderId
                . $statusCode
                . $grossAmount
                . $serverKey
        );

        return hash_equals(
            $hash,
            $signature
        );
    }

    public function isConfigured(): bool
    {
        return config(
            'services.midtrans.server_key',
            ''
        ) !== '';
    }

    private function ensureConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException(
                'Konfigurasi Midtrans belum lengkap.'
            );
        }
    }
}
