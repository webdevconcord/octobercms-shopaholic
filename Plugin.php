<?php namespace ConcordPay\ConcordPayShopaholic;

use Backend;
use ConcordPay\ConcordPayShopaholic\Classes\Helper\PaymentGateway;
use Event;
use Cms\Classes\Page;
use System\Classes\PluginBase;

use ConcordPay\ConcordPayShopaholic\Classes\Event\PaymentMethodModelHandler;
use ConcordPay\ConcordPayShopaholic\Classes\Event\ExtendPaymentMethodFieldsHandler;

/**
 * WayForPayShopaholic Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = [
        'Lovata.Toolbox',
        'Lovata.Shopaholic',
        'Lovata.OrdersShopaholic',
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'concordpay.concordpayshopaholic::lang.plugin.name',
            'description' => 'concordpay.concordpayshopaholic::lang.plugin.description',
            'author'      => 'ConcordPay',
            'icon'        => 'icon-credit-card',
            'homepage'    => 'https://concordpay.concord.ua/'
        ];
    }

    /**
     * Boot plugin method
     */
    public function boot()
    {
        $this->addListeners();

        // Interception of a redirect from a payment system
        // Approved
        Event::listen(PaymentGateway::EVENT_APPROVED_URL, function () {
            return Page::url('order-approved');
        });
        // Declined
        Event::listen(PaymentGateway::EVENT_DECLINED_URL, function () {
            return Page::url('order-declined');
        });
        // Canceled
        Event::listen(PaymentGateway::EVENT_CANCELED_URL, function () {
            return Page::url('order-canceled');
        });
    }

    private function addListeners()
    {
        Event::subscribe(ExtendPaymentMethodFieldsHandler::class);
        Event::subscribe(PaymentMethodModelHandler::class);
    }
}
