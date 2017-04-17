<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Common;

class AllPayPaymentGateway extends EcAllPayUtility implements Common\GatewayInterface
{

    /**
     * AllPayPaymentGateway constructor.
     * @param array $config
     * @return AllPayPaymentGateway
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (empty($this->actionUrl)) {
            $this->actionUrl = 'https://payment.allpay.com.tw/Cashier/AioCheckOut/';
        }
        if (empty($this->version)) {
            $this->version = 'V2';
        }

        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function useBarCode()
    {
        $this->order['ChoosePayment'] = 'BARCODE';
        $this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function useWebATM()
    {
        $this->order['ChoosePayment'] = 'WebATM';
        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function useCredit()
    {
        $this->order['ChoosePayment'] = 'Credit';
        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function useTenPay()
    {
        $this->order['ChoosePayment'] = 'Tenpay';
        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function useTopUp()
    {
        $this->order['ChoosePayment'] = 'TopUpUsed';
        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function useATM()
    {
        $this->order['ChoosePayment'] = 'ATM';
        $this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function useCVS()
    {
        $this->order['ChoosePayment'] = 'CVS';
        $this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function useALL()
    {
        $this->order['ChoosePayment'] = 'ALL';
        $this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
        return $this;
    }

    /**
     * @return AllPayPaymentGateway
     */
    public function needExtraPaidInfo()
    {
        $this->order['NeedExtraPaidInfo'] = 'Y';
        return $this;
    }

    /**
     * @param integer $months
     * @param integer|float $totalAmount
     * @return AllPayPaymentGateway
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
     * @return AllPayPaymentGateway
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
            case 'Tenpay':
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
     * @return AllPayPaymentGateway
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
     * @return AllPayPaymentGateway
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
