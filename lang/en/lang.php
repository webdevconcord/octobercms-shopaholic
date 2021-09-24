<?php


return [
    'plugin' => [
        'name'              => 'ConcordPay for Shopaholic',
        'description'       => 'ConcordPay payment gateway integration for Shopaholic.',
        'order_description' => 'Payment by card on the site',
    ],
    'gateway' => [
        'name' => 'ConcordPay'
    ],
    'field' => [
        'secret_section' => 'Secret information for gateway operation',
        'merchant_id'    => 'Merchant ID',
        'secret_key'     => 'Secret key',
    ],
    'comment' => [
        'secret_section' => 'Information from the personal account ConcordPay, please pass it on to third parties.',
        'merchant_id'    => 'Provide merchant ID property',
        'secret_key'     => 'Provide Secret Key property',
    ],
    'placeholder' => [
        'merchant_id' => '',
        'secret_key'  => '',
    ]
];