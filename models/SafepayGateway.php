<?php
declare(strict_types=1);

use Composer\CaBundle\CaBundle;

final class SafepayGateway
{
    private string $apiKey;
    private string $v1Secret;

    public function __construct()
    {
        if ((getenv('SAFEPAY_ENVIRONMENT') ?: 'sandbox') !== 'sandbox') {
            throw new RuntimeException('Only Safepay sandbox is enabled in this build.');
        }
        $this->apiKey = trim((string)(getenv('SAFEPAY_API_KEY') ?: ''));
        $this->v1Secret = trim((string)(getenv('SAFEPAY_V1_SECRET') ?: ''));
        if ($this->apiKey === '' || $this->v1Secret === '') {
            throw new RuntimeException('Safepay sandbox credentials are not configured.');
        }
    }

    public function createTracker(float $amount, string $orderNumber): string
    {
        $payload = [
            'merchant_api_key' => $this->apiKey,
            'intent' => 'CYBERSOURCE',
            'mode' => 'payment',
            'currency' => 'PKR',
            'amount' => (int)round($amount * 100),
            'metadata' => [
                'source' => 'eastern-sweets',
                'order_id' => $orderNumber,
            ],
        ];

        $ch = curl_init('https://sandbox.api.getsafepay.com/order/payments/v3/');
        if ($ch === false) {
            throw new RuntimeException('Safepay connection could not be initialized.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => CaBundle::getBundledCaBundlePath(),
        ]);
        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!is_string($raw) || $raw === '') {
            throw new RuntimeException('Safepay is unreachable right now.' . ($curlError !== '' ? ' ' . $curlError : ''));
        }
        $response = json_decode($raw, true);
        $tracker = trim((string)($response['data']['tracker']['token'] ?? ''));
        if ($httpCode !== 201 || $tracker === '') {
            $message = trim((string)($response['status']['message'] ?? ''));
            throw new RuntimeException($message !== '' ? 'Safepay: ' . $message : 'Safepay rejected the payment request.');
        }

        return $tracker;
    }

    public function checkoutUrl(string $tracker): string
    {
        return 'https://sandbox.api.getsafepay.com/checkout/pay?' . http_build_query([
            'env' => 'sandbox',
            'tracker' => $tracker,
            'source' => 'hosted',
            'redirect_url' => absolute_url('payment/safepay/return'),
            'cancel_url' => absolute_url('payment/safepay/cancel'),
        ]);
    }

    public function verifyReturn(string $tracker, string $signature): bool
    {
        if ($tracker === '' || $signature === '') {
            return false;
        }
        $expected = hash_hmac('sha256', $tracker, $this->v1Secret);
        return hash_equals($expected, strtolower($signature));
    }
}
