<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Common;

class SpGatewayPaymentGateway extends Common\AbstractGateway implements Common\GatewayInterface
{

    /**
     * SpGatewayPaymentGateway constructor.
     * @param array $config
     * @return SpGatewayPaymentGateway
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (empty($this->actionUrl)) {
            $this->actionUrl = 'https://core.spgateway.com/MPG/mpg_gateway';
        }
        if (empty($this->version)) {
            $this->version = '1.2';
        }

        return $this;
    }

    /**
     * @return SpGatewayPaymentGateway
     */
    public function useBarCode()
    {
        $this->order['BARCODE'] = 1;
        return $this;
    }

    /**
     * @return SpGatewayPaymentGateway
     */
    public function useWebATM()
    {
        $this->order['WEBATM'] = 1;
        return $this;
    }

    /**
     * @return SpGatewayPaymentGateway
     */
    public function useCredit()
    {
        $this->order['CREDIT'] = 1;
        return $this;
    }

    /**
     * @return SpGatewayPaymentGateway
     */
    public function useATM()
    {
        $this->order['VACC'] = 1;
        return $this;
    }

    /**
     * @return SpGatewayPaymentGateway
     */
    public function useCVS()
    {
        $this->order['CVS'] = 1;
        return $this;
    }

    /**
     * @param bool $mode
     * @return SpGatewayPaymentGateway
     */
    public function triggerEmailModify($mode)
    {
        $this->order['EmailModify'] = (!!$mode) ? 1 : 0;
        return $this;
    }

    /**
     * @param bool $mode
     * @return SpGatewayPaymentGateway
     */
    public function onlyLoginMemberCanPay($mode)
    {
        $this->order['LoginType'] = (!!$mode) ? 1 : 0;
        return $this;
    }

    /**
     * @param integer $months
     * @return SpGatewayPaymentGateway
     */
    public function setCreditInstallment($months)
    {
        $this->order['InstFlag'] = $months;
        return $this;
    }

    /**
     * @param int|string $expireDate
     * @return SpGatewayPaymentGateway
     */
    public function setOrderExpire($expireDate)
    {
        if (is_numeric($expireDate)) {
            $expireDate = intval($expireDate);
        }
        if (is_string($expireDate)) {
            $expireDate = intval(strtotime($expireDate));
        }

        $this->order['ExpireDate'] = date('Ymd', $expireDate);
        return $this;
    }

    /**
     * @return SpGatewayPaymentGateway
     */
    public function setUnionPay()
    {
        $this->order['UNIONPAY'] = 1;
        if (isset($this->order['CREDIT'])) {
            unset($this->order['CREDIT']);
        }
        return $this;
    }

    /**
     * @param string $email
     * @throws \InvalidArgumentException
     * @return SpGatewayPaymentGateway
     */
    public function setEmail($email)
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($email === false) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        $this->order['Email'] = $email;
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
     * @return SpGatewayPaymentGateway
     */
    public function newOrder(
        $merchantOrderNo,
        $amount,
        $itemDescribe,
        $orderComment,
        $respondType = 'JSON',
        $timestamp = 0
    ) {
    
        /**
         * Argument Check
         */
        $this->argumentChecker();

        if (!isset($this->notifyUrl)) {
            throw new \InvalidArgumentException('NotifyURL not set');
        }

        $timestamp = empty($timestamp) ? time() : $timestamp;

        $this->clearOrder();

        $this->order['Amt'] = intval($amount);
        $this->order['Version'] = $this->version;
        $this->order['LangType'] = 'zh-tw';
        $this->order['TimeStamp'] = $timestamp;
        $this->order['MerchantID'] = $this->merchantId;
        $this->order['RespondType'] = $respondType;

        $this->order['MerchantOrderNo'] = $merchantOrderNo;

        $this->order['ItemDesc'] = $itemDescribe;
        $this->order['OrderComment'] = $orderComment;

        if (!empty($this->returnUrl)) {
            $this->order['ReturnURL'] = $this->returnUrl;
        }
        if (!empty($this->notifyUrl)) {
            $this->order['NotifyURL'] = $this->notifyUrl;
        }
        if (!empty($this->paymentInfoUrl)) {
            $this->order['CustomerURL'] = $this->paymentInfoUrl;
        }
        if (!empty($this->clientBackUrl)) {
            $this->order['ClientBackURL'] = $this->clientBackUrl;
        }

        return $this;
    }

    protected function isPaymentMethodSelected()
    {
        if (!isset($this->order['UNIONPAY']) &&
            !isset($this->order['BARCODE']) &&
            !isset($this->order['CREDIT']) &&
            !isset($this->order['WEBATM']) &&
            !isset($this->order['VACC']) &&
            !isset($this->order['CVS'])
        ) {
            throw new \InvalidArgumentException('Payment method not set');
        }
    }

    /**
     * @param bool $autoSubmit
     * @return string
     */
    public function genForm($autoSubmit)
    {
        $this->autoSubmit = !!$autoSubmit;

        $this->isPaymentMethodSelected();

        if (isset($this->order['BARCODE']) ||
            isset($this->order['VACC']) ||
            isset($this->order['CVS'])
        ) {
            if (empty($this->paymentInfoUrl)) {
                throw new \InvalidArgumentException('PaymentInfoURL not set');
            }
        }

        if (!isset($this->order['LoginType'])) {
            $this->order['LoginType'] = 0;
        }
        if (!isset($this->order['EmailModify'])) {
            $this->order['EmailModify'] = 0;
        }

        $this->order['CheckValue'] = $this->genCheckValue();

        $formId = sprintf("PG_SPGATEWAY_FORM_GO_%s", sha1(time()));

        $html = sprintf(
            "<form style='display: none' id='%s' method='post' action='%s'>",
            $formId,
            $this->actionUrl
        );
        foreach ($this->order as $key => $value) {
            $html .= sprintf("<input type='text' name='%s' value='%s'>", $key, $value);
        }
        $html .= "</form>";

        if ($this->autoSubmit) {
            $html .= sprintf("<script>document.getElementById('%s').submit();</script>", $formId);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function genCheckValue()
    {
        $merArray = [
            'MerchantOrderNo' => $this->order['MerchantOrderNo'],
            'MerchantID'      => $this->merchantId,
            'TimeStamp'       => $this->order['TimeStamp'],
            'Version'         => $this->version,
            'Amt'             => $this->order['Amt'],
        ];

        ksort($merArray);

        $merArray = array_merge(['HashKey' => $this->hashKey], $merArray, ['HashIV' => $this->hashIV]);

        $checkMerStr = http_build_query($merArray);

        return strtoupper(hash("sha256", $checkMerStr));
    }
}
