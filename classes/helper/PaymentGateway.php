<?php namespace ConcordPay\ConcordPayShopaholic\Classes\Helper;

use Event;
use HttpException;
use Input;
use Response;
use Lang;
use October\Rain\Network\Http;
use Lovata\OrdersShopaholic\Classes\Helper\AbstractPaymentGateway;
use October\Rain\Support\Facades\Config;

/**
 * Class PaymentGateway
 * @package ConcordPay\ConcordPayShopaholic\Classes\Helper
 * @author ConcordPay
 */
class PaymentGateway extends AbstractPaymentGateway
{
    const CODE = 'concordpay';

    const APPROVED_URL = '/shopaholic/concordpay/approved';
    const DECLINED_URL = '/shopaholic/concordpay/declined';
    const CANCELED_URL = '/shopaholic/concordpay/canceled';
    const CALLBACK_URL = '/shopaholic/concordpay/callback';

    const EVENT_APPROVED_URL = 'shopaholic.payment.concordpay.approved.redirect_url';
    const EVENT_DECLINED_URL = 'shopaholic.payment.concordpay.declined.redirect_url';
    const EVENT_CANCELED_URL = 'shopaholic.payment.concordpay.canceled.redirect_url';
    const EVENT_CALLBACK_URL = 'shopaholic.payment.concordpay.callback.redirect_url';

    const EVENT_GET_PAYMENT_GATEWAY_PURCHASE_DATA = 'shopaholic.payment.concordpay.purchase_data';

    const ORDER_APPROVED = 'Approved';
    const ORDER_DECLINED = 'Declined';

    const RESPONSE_TYPE_PAYMENT = 'payment';
    const RESPONSE_TYPE_REVERSE = 'reverse';

    const URL_API = 'https://pay.concord.ua/api/';
    const URL_REDIRECT = 'https://pay.concord.ua/payment/pay?payment=';

    /** @var array - response from payment gateway */
    protected $arResponse = [];
    /** @var array - request data for payment gateway */
    protected $arRequestData = [];
    protected $sRedirectURL  = '';
    protected $sMessage      = '';
    protected $sSignature    = '';

    /** @var array - array keys for signature generator */
    protected $arKeysForResponseSignature = [
        'merchantAccount',
        'orderReference',
        'amount',
        'currency',
    ];

    /** @var array - array keys for signature generator  */
    protected $arKeysForSignature = [
        'merchant_id',
        'order_id',
        'amount',
        'currency_iso',
        'description',
    ];

    protected $obResponse;

    /**
     * Get response array
     * @return array
     */
    public function getResponse(): array
    {
        return (array)$this->arResponse;
    }

    /**
     * Get redirect URL
     * @return string
     */
    public function getRedirectURL(): string
    {
        return $this->sRedirectURL;
    }

    /**
     * Get error message from payment gateway
     * @return string
     */
    public function getMessage(): string
    {
        return $this->sMessage;
    }

    /**
     * Process answer from ConcordPay Gateway
     *
     * @param $sOrderKey
     * @return void
     */
    public function processCallback($sOrderKey)
    {
        $this->initOrderObject($sOrderKey);
        $arData = \json_decode(file_get_contents('php://input'), true);

        $sSignature = $this->createSignature($this->arKeysForResponseSignature, $arData);

        if (!isset($arData['merchantSignature']) || $arData['merchantSignature'] !== $sSignature) {
            throw new HttpException('Wrong payment signature!', 500);
        }

        if (!isset($arData['transactionStatus'])) {
            throw new HttpException('Transaction status is missing!', 404);
        }

        if (!isset($arData['type'])) {
            throw new HttpException('Transaction type is missing!', 404);
        }

        if ($arData['transactionStatus'] === self::ORDER_APPROVED) {
            if ($arData['type'] === self::RESPONSE_TYPE_REVERSE) {
                // Refunded payment callback.
                $this->setCancelStatus();
            } elseif ($arData['type'] === self::RESPONSE_TYPE_PAYMENT) {
                // Purchase callback.
                $this->setSuccessStatus();
            }
        } else {
            $this->setFailStatus();
        }

        // Save response data
        $this->obOrder->payment_response = $arData;
        $this->obOrder->save();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processApprovedURL()
    {
        return $this->returnRedirectResponse(self::EVENT_APPROVED_URL);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processDeclinedURL()
    {
        return $this->returnRedirectResponse(self::EVENT_DECLINED_URL);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processCanceledURL()
    {
        return $this->returnRedirectResponse(self::EVENT_CANCELED_URL);
    }

    /**
     * Prepare data for request in payment gateway
     */
    protected function preparePurchaseData()
    {
        $this->arRequestData = [
            'operation'    => 'Purchase',
            'merchant_id'  => $this->getGatewayProperty('merchant_id'),
            'amount'       => $this->obOrder->total_price_value,
            'order_id'     => $this->obOrder->id,
            'currency_iso' => $this->obPaymentMethod->gateway_currency,
            'description'  => $this->getOrderDescription(),
            'add_params'   => [],
            'approve_url'  => url(self::APPROVED_URL),
            'decline_url'  => url(self::DECLINED_URL),
            'cancel_url'   => url(self::CANCELED_URL),
            'callback_url' => url(self::CALLBACK_URL . '/' . $this->obOrder->secret_key),
            // Statistics.
            'client_first_name' => $this->obOrder->getProperty('name') ?? '',
            'client_last_name'  => $this->obOrder->getProperty('last_name') ?? '',
            'email'             => $this->obOrder->getProperty('email') ?? '',
            'phone'             => '',
        ];

        $this->arRequestData['signature'] = $this->createSignature($this->arKeysForSignature, $this->arRequestData);

        $this->extendPurchaseData();
    }

    /**
     * Validate request data
     * @return bool
     */
    protected function validatePurchaseData()
    {
        return true;
    }

    /**
     * Send request to payment gateway
     */
    protected function sendPurchaseData()
    {
        $this->preparePurchaseData();

        //Send request to payment gateway
        try {
            $this->obResponse = $this->sendToGateway(self::URL_API, json_encode($this->arRequestData));
        } catch (\Exception $obException) {
            $this->sMessage = $obException->getMessage();
            return;
        }
    }

    /**
     * Process response from payment gateway
     */
    protected function processPurchaseResponse()
    {
        if (empty($this->obResponse)) {
            return;
        }
        //Search in CURL response paymentid and redirect user on that page
        $pattern = '/id(\s)?=(\s)?"paymentid"(\s)?value(\s)?=(\s)?"(?<paymentId>.+)"/';
        preg_match($pattern, $this->obResponse, $matches);

        if (isset($matches['paymentId']) && !empty($matches['paymentId'])) {
            // Set waiting for payment status in order
            $this->setWaitPaymentStatus();
            $this->sRedirectURL = self::URL_REDIRECT . $matches['paymentId'] . '&lang=uk';
            $this->bIsRedirect = true;
        } else {
            // Set cancel status in order
            $this->setCancelStatus();
            $this->sMessage = sprintf("Error #%s: %s", $this->obResponse['reasonCode'], $this->obResponse['reason']);
        }

        // Save response and request data
        $this->obOrder->payment_data = $this->arRequestData;
        $this->obOrder->payment_response = $this->obResponse;
        $this->obOrder->save();
    }

    /**
     * Creating signature string
     *
     * @param array $arKeys
     * @param array $arData
     * @return string
     */
    protected function createSignature($arKeys, $arData)
    {
        $arHash = [];
        foreach ($arKeys as $sDataKey) {
            if (!isset($arData[$sDataKey])) {
                continue;
            }
            if (is_array($arData[$sDataKey])) {
                foreach ($arData[$sDataKey] as $sValue) {
                    $arHash[] = $sValue;
                }
            } else {
                $arHash[] = $arData[$sDataKey];
            }
        }

        $this->sSignature = implode(';', $arHash);
        return hash_hmac('md5', $this->sSignature, $this->getGatewayProperty('secret_key'));
    }

    /**
     * Send request to gateway
     *
     * @param $url
     * @param $data
     * @return Http|string
     */
    protected function sendToGateway($url, $data)
    {
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));

            $out = curl_exec($curl);
            curl_close($curl);

            return $out;
        }

        throw new HttpException('Can not create connection to ' . $url . ' with args ' . $data, 404);
    }

    /**
     * Fire event and extend purchase data
     */
    protected function extendPurchaseData()
    {
        //Fire event
        $arEventDataList = Event::fire(self::EVENT_GET_PAYMENT_GATEWAY_PURCHASE_DATA, [
            $this->obOrder,
            $this->obPaymentMethod,
            $this->arRequestData,
        ]);
        if (empty($arEventDataList)) {
            return;
        }

        //Process event data
        foreach ($arEventDataList as $arEventData) {
            if (empty($arEventData) || !is_array($arEventData)) {
                continue;
            }

            foreach ($arEventData as $sField => $sValue) {
                $this->arRequestData[$sField] = $sValue;
            }
        }
    }

    /**
     * Cleaning string from all special characters
     * @param $string
     * @return string
     */
    protected function cleanString($string)
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $string);
    }

    /**
     * Cleans url string for request
     *
     * @param $url
     * @return string
     */
    protected function cleanUrl($url)
    {
        if (strpos($url, 'http://') !== false) {
            return str_replace('http://', '', $url);
        }

        if (strpos($url, 'https://') !== false) {
            return str_replace('https://', '', $url);
        }

        return $url;
    }

    /**
     * Convert price to cents
     *
     * @param $price
     * @return string|string[]
     */
    protected function convertCents($price)
    {
        return str_replace('.', '', money_format('%i', $price));
    }

    /**
     * @return string
     */
    protected function getOrderDescription()
    {
        $sUrl     = '';
        $sSiteUrl = mb_split('//', Config::get('app.url'));
        if ($sSiteUrl[1]) {
            $sUrl = $sSiteUrl[1];
        }
        $sFullName = $this->obOrder->getProperty('name') . ' ' . $this->obOrder->getProperty('last_name');

        return Lang::get('concordpay.concordpayshopaholic::lang.plugin.order_description') . " $sUrl, $sFullName.";
    }
}
