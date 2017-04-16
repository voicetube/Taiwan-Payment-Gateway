<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Common;

class EcPayPaymentGateway extends Common\AbstractGateway implements Common\GatewayInterface
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
     * @param integer $total_amount
     * @return EcPayPaymentGateway
     */
    public function setCreditInstallment($months, $total_amount = 0)
    {
        $this->order['CreditInstallment'] = $months;
        if ($total_amount) {
            $this->order['InstallmentAmount'] = $total_amount;
        }
        return $this;
    }

    /**
     * @param int|string $expire_Date
     * @return EcPayPaymentGateway
     */
    public function setOrderExpire($expire_Date)
    {
        if (is_numeric($expire_Date)) {
            $expire_Date = intval($expire_Date);
        }

        switch ($this->order['ChoosePayment']) {
            case 'ATM':
                $this->order['ExpireDate'] = $expire_Date;
                break;
            case 'CVS':
                $this->order['StoreExpireDate'] = mktime(
                    23,
                    59,
                    59,
                    date('m'),
                    date('d') + $expire_Date,
                    date('Y')
                ) - time();
                break;
            case 'BARCODE':
                $this->order['StoreExpireDate'] = $expire_Date;
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
     * @param string $merchant_order_no
     * @param float|int $amount
     * @param string $item_describe
     * @param string $order_comment
     * @param string $respond_type
     * @param int $timestamp
     * @throws \InvalidArgumentException
     * @return EcPayPaymentGateway
     */
    public function newOrder(
        $merchant_order_no,
        $amount,
        $item_describe,
        $order_comment,
        $respond_type = 'POST',
        $timestamp = 0
    ) {
    
        /**
         * Argument Check
         */
        if (!isset($this->hashIV)) {
            throw new \InvalidArgumentException('HashIV not set');
        }
        if (!isset($this->hashKey)) {
            throw new \InvalidArgumentException('HashKey not set');
        }
        if (!isset($this->merchantId)) {
            throw new \InvalidArgumentException('MerchantID not set');
        }

        if (!isset($this->returnUrl)) {
            throw new \InvalidArgumentException('ReturnURL not set');
        }
        if (!isset($this->actionUrl)) {
            throw new \InvalidArgumentException('ActionURL not set');
        }

        $timestamp = empty($timestamp) ? time() : $timestamp;

        $this->clearOrder();

        $this->order['PaymentType'] = 'aio';
        $this->order['MerchantID'] = $this->merchantId;
        $this->order['MerchantTradeDate'] = date("Y/m/d H:i:s", $timestamp);
        $this->order['MerchantTradeNo'] = $merchant_order_no;
        $this->order['TotalAmount'] = intval($amount);
        $this->order['ItemName'] = $item_describe;
        $this->order['TradeDesc'] = $order_comment;
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
     * @param bool $auto_submit
     * @return string
     */
    public function genForm($auto_submit = true)
    {

        if (!isset($this->order['ChoosePayment'])) {
            throw new \InvalidArgumentException('Payment method not set');
        }

        if ($this->order['ChoosePayment'] == 'BARCODE' ||
            $this->order['ChoosePayment'] == 'ATM' ||
            $this->order['ChoosePayment'] == 'CVS'
        ) {
            if (!isset($this->paymentInfoUrl)) {
                throw new \InvalidArgumentException('PaymentInfoURL not set');
            }
        }

        $this->order['CheckMacValue'] = $this->genCheckValue();

        $formId = sprintf("PG_FORM_GO_%s", sha1(time()));

        $html = sprintf(
            "<form style='display: none' id='%s' method='post' action='%s'>",
            $formId,
            "{$this->actionUrl}{$this->version}"
        );
        foreach ($this->order as $key => $value) {
            $html .= sprintf("<input type='text' name='%s' value='%s'>", $key, $value);
        }
        $html .= "</form>";

        if ($auto_submit) {
            $html .= sprintf("<script>document.getElementById('%s').submit();</script>", $formId);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function genCheckValue()
    {
        uksort($this->order, 'strcasecmp');

        $mer_array = array_merge(['HashKey' => $this->hashKey], $this->order, ['HashIV' => $this->hashIV]);

        $check_mer_str = urldecode(http_build_query($mer_array));

        foreach ($this->dot_net_url_encode_mapping as $key => $value) {
            $check_mer_str = str_replace($key, $value, $check_mer_str);
        }

        $check_mer_str = strtolower(urlencode($check_mer_str));

        return strtoupper(hash('sha256', $check_mer_str));
    }
}
