<?php namespace ConcordPay\ConcordPayShopaholic\Classes\Event;

use Cms\Classes\Page;
use Lang;
use Lovata\OrdersShopaholic\Models\PaymentMethod;

use ConcordPay\ConcordPayShopaholic\Classes\Helper\PaymentGateway;

/**
 * Class PaymentMethodModelHandler
 * @package ConcordPay\ConcordPayShopaholic\Classes\Event
 * @author ConcordPay
 */
class PaymentMethodModelHandler
{
    /**
     * Add event listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        PaymentMethod::extend(function ($obElement) {
            /** @var PaymentMethod $obElement */

            $obElement->addGatewayClass(PaymentGateway::CODE, PaymentGateway::class);

            $obElement->bindEvent('model.beforeValidate', function () use ($obElement) {
                $this->addValidationRules($obElement);
            });
        });

        $obEvent->listen(PaymentMethod::EVENT_GET_GATEWAY_LIST, function () {
            $arPaymentMethodList = [
                PaymentGateway::CODE => Lang::get('concordpay.concordpayshopaholic::lang.gateway.name'),
            ];

            return $arPaymentMethodList;
        });
    }

    /**
     * Add custom validartion rules and validation messages
     * @param PaymentMethod $obElement
     */
    protected function addValidationRules($obElement)
    {
        if ($obElement->gateway_id != PaymentGateway::CODE
            || $obElement->getOriginal('gateway_id') != PaymentGateway::CODE
        ) {
            return;
        }

        //Add validation rules
        $arRules = [
            'gateway_property.merchant_id' => 'required',
            'gateway_property.secret_key'  => 'required',
        ];

        $obElement->rules = array_merge($obElement->rules, $arRules);

        //Add validation custom messages
        $arAttributeNames = [
            'gateway_property.merchant_id' => 'concordpay.concordpayshopaholic::lang.field.merchant_id',
            'gateway_property.secret_key'  => 'concordpay.concordpayshopaholic::lang.field.secret_key',
        ];

        $obElement->attributeNames = array_merge($obElement->attributeNames, $arAttributeNames);
    }
}
