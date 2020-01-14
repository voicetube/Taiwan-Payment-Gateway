<?php

namespace VoiceTube\TaiwanPaymentGateway;

use GuzzleHttp\Client;
use VoiceTube\TaiwanPaymentGateway\Common;

class TapPayPaymentGateway
{

    // partnerKey & merchantId defined on Portal
    protected $prime;
    protected $cardholder = [
        'name' => '',
        'email' => '',
        'phone_number' => '',
    ];
    protected $partnerKey;
    protected $merchantId;

    protected $order;
    protected $instalment = 0;
    protected $currency = 'TWD';
    protected $cardholderVerify = [];

    /**
     * TapPayPaymentGateway constructor.
     *
     * @param  array $config
     * @return TapPayPaymentGateway
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {

            $key = trim($key);

            if (property_exists(self::class, $key)) {

                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * @param integer $months
     * @return TapPayPaymentGateway
     */
    public function setCreditInstallment($months)
    {
        $this->instalment = $months;
        return $this;
    }

    /**
     * @param string $frontendRedirectUrl
     * @param string $backendNotifyUrl
     * @return TapPayPaymentGateway
     */
    public function setResultUrl($frontendRedirectUrl, $backendNotifyUrl)
    {
        $this->resultUrl = [
            'frontend_redirect_url' => $frontendRedirectUrl,
            'backend_notify_url' => $backendNotifyUrl,
        ];

        return $this;
    }

    /**
     * @param bool $checkPhoneNumber
     * @param bool $checkNationalId
     * @return TapPayPaymentGateway
     */
    public function setCardholderVerify($checkPhoneNumber, $checkNationalId)
    {
        $this->cardholderVerify = [
            'phone_number' => $checkPhoneNumber,
            'national_id' => $checkNationalId,
        ];

        return $this;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return TapPayPaymentGateway
     */
    public function setOpenThreeDomainSecure()
    {
        $this->three_domain_secure = true;

        return $this;
    }

    /**
     * @return TapPayPaymentGateway
     */
    public function setRemember()
    {
        $this->remember = true;

        return $this;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string    $merchantOrderNo
     * @param float|int $amount
     * @param string    $itemDescribe
     * @param string    $orderComment
     * @param string    $respondType
     * @param int       $timestamp
     * @return TapPayPaymentGateway
     */
    public function newOrder(
        $merchantOrderNo,
        $amount,
        $itemDescribe,
        $orderComment = '',
        $respondType = 'POST',
        $timestamp = 0
    ) {

        if (!isset($this->prime)) {
            throw new \InvalidArgumentException('prime not set');
        }

        if (!isset($this->cardholder) ||
            !isset($this->cardholder['phone_number']) ||
            !isset($this->cardholder['name']) ||
            !isset($this->cardholder['email'])
        ) {
            throw new \InvalidArgumentException('cardholder not set');
        }

        $timestamp = empty($timestamp) ? time() : $timestamp;

        $this->order = [];
        $this->order['prime'] = $this->prime;
        $this->order['amount'] = intval($amount);
        $this->order['bank_transaction_id'] = $merchantOrderNo;
        $this->order['details'] = $itemDescribe;

        $this->order['cardholder']['phone_number'] = $this->cardholder['phone_number'];
        $this->order['cardholder']['name'] = $this->cardholder['name'];
        $this->order['cardholder']['email'] = $this->cardholder['email'];
        $this->order['cardholder']['zip_code'] = $this->cardholder['zip_code'] ?? '';
        $this->order['cardholder']['address'] = $this->cardholder['address'] ?? '';
        $this->order['cardholder']['national_id'] = $this->cardholder['national_id'] ?? '';
        $this->order['cardholder']['member_id'] = $this->cardholder['member_id'] ?? '';

        $this->order['partner_key'] = $this->partnerKey;
        $this->order['merchant_id'] = $this->merchantId;
        $this->order['currency'] = $this->currency;

        $this->order['instalment'] = $this->instalment;

        if (!empty($this->orderNumber)) {
            $this->order['order_number'] = $this->orderNumber;
        } else {
            $this->order['order_number'] = $this->order['bank_transaction_id'];
        }

        if (!empty($this->linePayImageUrl)) {
            $this->order['line_pay_product_image_url'] = $this->linePayImageUrl;
        }

        if (!empty($this->resultUrl)) {
            $this->order['result_url'] = $this->resultUrl;
        }

        if (!empty($this->cardholderVerify)) {
            $this->order['cardholder_verify'] = $this->cardholderVerify;
        }

        if (!empty($this->remember)) {
            $this->order['remember'] = $this->remember;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function sendPrimeOrder($url, $order)
    {
        $headers = [
            'x-api-key' => $order['partner_key'],
        ];

        $dataToSend = $order;

        $client = new Client();

        return $client->post($url, [
            'headers' => $headers,
            'json' => $dataToSend,
        ]);
    }
}
