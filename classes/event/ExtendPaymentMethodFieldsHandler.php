<?php namespace ConcordPay\ConcordPayShopaholic\Classes\Event;

use Config;

use Lovata\Toolbox\Classes\Event\AbstractBackendFieldHandler;

use Lovata\OrdersShopaholic\Models\PaymentMethod;
use Lovata\OrdersShopaholic\Controllers\PaymentMethods;
use ConcordPay\ConcordPayShopaholic\Classes\Helper\PaymentGateway;

/**
 * Class ExtendPaymentMethodFieldsHandler
 * @package ConcordPay\ConcordPayShopaholic\Classes\Event
 * @author ConcordPay
 */
class ExtendPaymentMethodFieldsHandler extends AbstractBackendFieldHandler
{
    /**
     * Extend backend fields
     * @param \Backend\Widgets\Form $obWidget
     */
    protected function extendFields($obWidget)
    {
        if ($obWidget->model->gateway_id != PaymentGateway::CODE) {
            return;
        }

        $obWidget->addTabFields([
            'secret_section' => [
                'label'   => 'concordpay.concordpayshopaholic::lang.field.secret_section',
                'comment' => 'concordpay.concordpayshopaholic::lang.comment.secret_section',
                'tab'     => 'lovata.ordersshopaholic::lang.tab.gateway',
                'type'    => 'section',
                'span'    => 'full'
            ],
            'gateway_property[merchant_id]' => [
                'label'       => 'concordpay.concordpayshopaholic::lang.field.merchant_id',
                'comment'     => 'concordpay.concordpayshopaholic::lang.comment.merchant_id',
                'placeholder' => 'concordpay.concordpayshopaholic::lang.placeholder.merchant_id',
                'tab'         => 'lovata.ordersshopaholic::lang.tab.gateway',
                'required'    => 'true',
                'span'        => 'left'
            ],
            'gateway_property[secret_key]' => [
                'label'       => 'concordpay.concordpayshopaholic::lang.field.secret_key',
                'comment'     => 'concordpay.concordpayshopaholic::lang.comment.secret_key',
                'placeholder' => 'concordpay.concordpayshopaholic::lang.placeholder.secret_key',
                'tab'         => 'lovata.ordersshopaholic::lang.tab.gateway',
                'required'    => 'true',
                'span'        => 'left'
            ]
        ]);
    }

    /**
     * Get model class name
     * @return string
     */
    protected function getModelClass(): string
    {
        return PaymentMethod::class;
    }

    /**
     * Get controller class name
     * @return string
     */
    protected function getControllerClass(): string
    {
        return PaymentMethods::class;
    }
}
