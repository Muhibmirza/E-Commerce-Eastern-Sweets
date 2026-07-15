<?php
declare(strict_types=1);

return [
    'cod' => [
        'label' => 'Cash on Delivery',
        'mode' => 'offline',
        'enabled' => true,
        'logo' => 'public/assets/images/payments/cod.svg',
        'instructions' => 'Collect payment from the customer at delivery.',
    ],
    'visa' => [
        'label' => 'Visa',
        'mode' => 'sandbox',
        'enabled' => true,
        'logo' => 'public/assets/images/payments/visa.svg',
        'public_key' => 'pk_test_replace_after_gateway_signup',
        'secret_key' => 'sk_test_replace_after_gateway_signup',
    ],
    'mastercard' => [
        'label' => 'Mastercard',
        'mode' => 'sandbox',
        'enabled' => true,
        'logo' => 'public/assets/images/payments/mastercard.svg',
        'public_key' => 'pk_test_replace_after_gateway_signup',
        'secret_key' => 'sk_test_replace_after_gateway_signup',
    ],
    'debit_card' => [
        'label' => 'Debit Card',
        'mode' => 'sandbox',
        'enabled' => true,
        'logo' => 'public/assets/images/payments/debit-card.svg',
        'public_key' => 'pk_test_replace_after_gateway_signup',
        'secret_key' => 'sk_test_replace_after_gateway_signup',
    ],
    'jazzcash' => [
        'label' => 'JazzCash',
        'mode' => 'sandbox',
        'enabled' => true,
        'logo' => 'public/assets/images/payments/jazzcash.svg',
        'merchant_id' => 'MC_TEST_REPLACE',
        'password' => 'TEST_PASSWORD_REPLACE',
        'integrity_salt' => 'TEST_SALT_REPLACE',
    ],
    'easypaisa' => [
        'label' => 'Easypaisa',
        'mode' => 'sandbox',
        'enabled' => true,
        'logo' => 'public/assets/images/payments/easypaisa.svg',
        'store_id' => 'EP_TEST_REPLACE',
        'hash_key' => 'TEST_HASH_REPLACE',
    ],
];
