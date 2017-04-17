<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Common;

class EcPayPaymentGateway extends EcAllPayUtility implements Common\GatewayInterface
{

    /**
     * EcPayPaymentGateway constructor.
     * @param array $config
     * @return EcPayPaymentGateway
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (empty($this->actionUrl)) {
            $this->actionUrl = 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/';
        }
        if (empty($this->version)) {
            $this->version = 'V4';
        }

        return $this;
    }

    /**
     * @return EcPayPaymentGateway
     */
    public function useBarCode()
    {
        $this->order['ChoosePayment'] = 'BARCODE';
        $this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
        return $this;
    }

    /**
     * @return EcPayPaymentGateway
     */
    public function useWebATM()
    {
        $this->order['ChoosePayment'] = 'WebATM';
        return $this;
    }

    /**
     * @return EcPayPaymentGateway
     */
    public function useCredit()
    {
        $this->order['ChoosePayment'] = 'Credit';
        return $this;
    }

    /**
     * @return EcPayPaymentGateway
     */
    public function useATM()
    {
        $this->order['ChoosePayment'] = 'ATM';
        $this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
        return $this;
    }

    /**
     * @return EcPayPaymentGateway
     */
    public function useCVS()
    {
        $this->order['ChoosePayment'] = 'CVS';
        $this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
        return $this;
    }

    /**
     * @return EcPayPaymentGateway
     */
    public function useALL()
    {
        $this->order['ChoosePayment'] = 'ALL';
        $this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
        return $this;
    }

    /**
     * @return EcPayPaymentGateway
     */
    public function needExtraPaidInfo()
    {
        $this->order['NeedExtraPaidInfo'] = 'Y';
        return $this;
    }

    /**
     * @param integer $months
     * @param integer|float $totalAmount
     * @return EcPayPaymentGateway
     */
    public function setCreditInstallment($months, $totalAmount = 0)
    {
        $this->order['CreditInstallment'] = $months;
        if ($totalAmount) {
            $this->order['InstallmentAmount'] = $totalAmount;
        }
        return $this;
    }

    /**
     * @param int|string $expireDate
     * @return EcPayPaymentGateway
     */
    public function setOrderExpire($expireDate)
    {
        if (is_numeric($expireDate)) {
            $expireDate = intval($expireDate);
        }

        switch ($this->order['ChoosePayment']) {
            case 'ATM':
                $this->order['ExpireDate'] = $expireDate;
                break;
            case 'CVS':
                $this->order['StoreExpireDate'] = mktime(
                    23,
                    59,
                    59,
                    date('m'),
                    date('d') + $expireDate,
                    date('Y')
                ) - time();
                break;
            case 'BARCODE':
                $this->order['StoreExpireDate'] = $expireDate;
                break;
        }
        return $this;
    }

    /**
     * @return EcPayPaymentGateway
     */
    public function setUnionPay()
    {
        $this->order['UnionPay'] = 1;
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
     * @param string $merchantOrderNo
     * @param float|int $amount
     * @param string $itemDescribe
     * @param string $orderComment
     * @param string $respondType
     * @param int $timestamp
     * @throws \InvalidArgumentException
     * @return EcPayPaymentGateway
     */
    public function newOrder(
        $merchantOrderNo,
        $amount,
        $itemDescribe,
        $orderComment,
        $respondType = 'POST',
        $timestamp = 0
    ) {

        $this->argumentChecker();

        $timestamp = empty($timestamp) ? time() : $timestamp;

        $this->clearOrder();

        $this->order['PaymentType'] = 'aio';
        $this->order['MerchantID'] = $this->merchantId;
        $this->order['MerchantTradeDate'] = date("Y/m/d H:i:s", $timestamp);
        $this->order['MerchantTradeNo'] = $merchantOrderNo;
        $this->order['TotalAmount'] = intval($amount);
        $this->order['ItemName'] = $itemDescribe;
        $this->order['TradeDesc'] = $orderComment;
        $this->order['EncryptType'] = 1;

        if (!empty($this->returnUrl)) {
            $this->order['ReturnURL'] = $this->returnUrl;
        }
        if (!empty($this->clientBackUrl)) {
            $this->order['ClientBackURL'] = $this->clientBackUrl;
        }

        return $this;
    }

    public function genForm($autoSubmit)
    {
        return parent::genForm($autoSubmit);
    }

    /**
     * @return string
     */
    public function genCheckValue()
    {
        uksort($this->order, 'strcasecmp');

        $merArray = array_merge(['HashKey' => $this->hashKey], $this->order, ['HashIV' => $this->hashIV]);

        $checkMerStr = urldecode(http_build_query($merArray));

        foreach ($this->urlEncodeMapping as $key => $value) {
            $checkMerStr = str_replace($key, $value, $checkMerStr);
        }

        $checkMerStr = strtolower(urlencode($checkMerStr));

        return strtoupper(hash('sha256', $checkMerStr));
    }
}
