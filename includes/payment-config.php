<?php
declare(strict_types=1);

$safepayApiKey = trim((string)(getenv('SAFEPAY_API_KEY') ?: ''));
$safepaySecret = trim((string)(getenv('SAFEPAY_V1_SECRET') ?: ''));

return [
    'cod' => [
        'label' => 'Cash on Delivery',
        'mode' => 'offline',
        'enabled' => true,
        'logo' => 'public/assets/images/payments/cod.svg',
        'instructions' => 'Collect payment from the customer at delivery.',
    ],
    'safepay' => [
        'label' => 'Pay Online with Safepay',
        'mode' => getenv('SAFEPAY_ENVIRONMENT') ?: 'sandbox',
        'enabled' => $safepayApiKey !== '' && $safepaySecret !== '',
        'logo' => 'public/assets/images/payments/debit-card.svg',
        'public_key' => $safepayApiKey,
    ],
];
