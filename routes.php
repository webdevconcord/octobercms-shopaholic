<?php

use ConcordPay\ConcordPayShopaholic\Classes\Helper\PaymentGateway;

Route::get(PaymentGateway::APPROVED_URL, function () {
    $obPaymentGateway = new PaymentGateway();
    return $obPaymentGateway->processApprovedURL();
});

Route::get(PaymentGateway::DECLINED_URL, function () {
    $obPaymentGateway = new PaymentGateway();
    return $obPaymentGateway->processDeclinedURL();
});

Route::get(PaymentGateway::CANCELED_URL, function () {
    $obPaymentGateway = new PaymentGateway();
    return $obPaymentGateway->processCanceledURL();
});

Route::post(PaymentGateway::CALLBACK_URL . '/{slug}', function ($sOrderKey) {
    $obPaymentGateway = new PaymentGateway();
    return $obPaymentGateway->processCallback($sOrderKey);
});