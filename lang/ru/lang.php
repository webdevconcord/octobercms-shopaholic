<?php

return [
    'plugin' => [
        'name'              => 'ConcordPay для Shopaholic',
        'description'       => 'Интеграция платежного шлюза ConcordPay для Shopaholic.',
        'order_description' => 'Оплата картой на сайте',
    ],
    'gateway' => [
        'name' => 'ConcordPay'
    ],
    'field' => [
        'secret_section' => 'Секретная информация для работы шлюза',
        'merchant_id'    => 'Идентификатор продавца',
        'secret_key'     => 'Секретный ключ',
    ],
    'comment' => [
        'secret_section' => 'Информация из личного кабинета ConcordPay, пожалуйста не передавайте ее третьим лицам.',
        'merchant_id'    => 'Укажите ID вашего продавца',
        'secret_key'     => 'Укажите секретный ключ вашего продавца',
    ],
    'placeholder' => [
        'merchant_id' => '',
        'secret_key'  => '',
    ]
];