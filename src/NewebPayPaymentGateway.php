<?php

namespace VoiceTube\TaiwanPaymentGateway;

use GuzzleHttp\Client;
use VoiceTube\TaiwanPaymentGateway\Common;

class NewebPayPaymentGateway extends Common\AbstractGateway implements Common\GatewayInterface
{
    private $aesPayload;
    const TYPE_CODE_FOR_MERCHANTORDERNO = 1;
    const TYPE_CODE_FOR_TRADNO = 2; 

    /**
     * SpGatewayPaymentGateway constructor.
     *
     * @param  array $config
     * @return NewebPayPaymentGateway
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (empty($this->actionUrl)) {
            $this->actionUrl = 'https://core.newebpay.com/MPG/mpg_gateway';
        }
        if (empty($this->version)) {
            $this->version = '1.5';
        }

        return $this;
    }

    /**
     * @return NewebPayPaymentGateway
     */
    public function useBarCode()
    {
        $this->order['BARCODE'] = 1;
        return $this;
    }

    /**
     * @return NewebPayPaymentGateway
     */
    public function useWebATM()
    {
        $this->order['WEBATM'] = 1;
        return $this;
    }

    /**
     * @return NewebPayPaymentGateway
     */
    public function useCredit()
    {
        $this->order['CREDIT'] = 1;
        return $this;
    }

    /**
     * @return NewebPayPaymentGateway
     */
    public function useATM()
    {
        $this->order['VACC'] = 1;
        return $this;
    }

    /**
     * @return NewebPayPaymentGateway
     */
    public function useCVS()
    {
        $this->order['CVS'] = 1;
        return $this;
    }

    /**
     * @param bool $mode
     * @return NewebPayPaymentGateway
     */
    public function triggerEmailModify($mode)
    {
        $this->order['EmailModify'] = (!!$mode) ? 1 : 0;
        return $this;
    }

    /**
     * @param bool $mode
     * @return NewebPayPaymentGateway
     */
    public function onlyLoginMemberCanPay($mode)
    {
        $this->order['LoginType'] = (!!$mode) ? 1 : 0;
        return $this;
    }

    /**
     * @param integer $months
     * @return NewebPayPaymentGateway
     */
    public function setCreditInstallment($months)
    {
        $this->order['InstFlag'] = $months;
        return $this;
    }

    /**
     * @param int|string $expireDate
     * @return NewebPayPaymentGateway
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
     * @return NewebPayPaymentGateway
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
     * @return NewebPayPaymentGateway
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
     * @return NewebPayPaymentGateway
     */
    public function newOrder(
        $merchantOrderNo,
        $amount,
        $itemDescribe,
        $orderComment = '',
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

    protected function validateOrder()
    {
        if (!isset($this->order['UNIONPAY'])
            && !isset($this->order['BARCODE'])
            && !isset($this->order['CREDIT'])
            && !isset($this->order['WEBATM'])
            && !isset($this->order['VACC'])
            && !isset($this->order['CVS'])
        ) {
            throw new \InvalidArgumentException('Payment method not set');
        }

        if (isset($this->order['BARCODE'])
            || isset($this->order['VACC'])
            || isset($this->order['CVS'])
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
    }

    public function newRefund(
        $merchantOrderNo,
        $amount,
        $respondType = 'JSON',
        $timestamp = 0) 
    {
        /**
         * Argument Check
         */

        if (!isset($this->notifyUrl)) {
            throw new \InvalidArgumentException('NotifyURL not set');
        }

        $timestamp = empty($timestamp) ? time() : $timestamp;

        $this->clearOrder();

        $this->order['Amt'] = intval($amount);
        $this->order['Version'] = $this->version;
        $this->order['TimeStamp'] = $timestamp;
        $this->order['MerchantID'] = $this->merchantId;
        $this->order['RespondType'] = $respondType;
        $this->order['MerchantOrderNo'] = $merchantOrderNo;
        $this->order['NotifyURL'] = $this->notifyUrl;

        if (!empty($this->order['MerchantOrderNo'])) {
            $this->order['IndexType'] = self::TYPE_CODE_FOR_MERCHANTORDERNO;
        }

        return $this;
    }

    protected function validateRefund()
    {
        if (!isset($this->order['Amt'])) {
            throw new \InvalidArgumentException('Amt not set');
        }

        if (!isset($this->order['Version'])) {
            throw new \InvalidArgumentException('API Version not set');
        }

        if (!isset($this->order['MerchantOrderNo'])) {
            throw new \InvalidArgumentException('MerchantOrderNo not set');
        }

        if (!isset($this->order['IndexType'])) {
            throw new \InvalidArgumentException('IndexType not set');
        }

        if (!isset($this->order['TimeStamp'])) {
            throw new \InvalidArgumentException('TimeStamp not set');
        }

        if (!isset($this->order['NotifyURL'])) {
            throw new \InvalidArgumentException('NotifyURL not set');
        }
    }

    /**
     * @param bool $autoSubmit
     * @return string
     */
    public function genForm($autoSubmit, $type = 'payment')
    {
        $this->autoSubmit = !!$autoSubmit;
        if ($type === 'refund') {
            $this->validateRefund();
            $this->genAesEncryptedPayment();

            $payment = [
                'MerchantID_' => $this->merchantId,
                'PostData_'   => $this->aesPayload,
            ];

            $this->order = $payment;
        } elseif ($type === 'payment') {
            $this->validateOrder();
            if ($this->version >= 1.4) {
                $this->genAesEncryptedPayment();

                $payment = [
                    'MerchantID' => $this->merchantId,
                    'TradeInfo'  => $this->aesPayload,
                    'TradeSha'   => $this->genCheckValue(),
                    'Version'    => $this->version,
                ];

                $this->order = $payment;
            } else {
                $this->order['CheckValue'] = $this->genCheckValue();
            }
        } else {
            throw new \InvalidArgumentException('unkown payment type');
        }

        $formId = sprintf("PG_NEWEBPAY_FORM_GO_%s", sha1(time()));

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
     * @return array
     */
    public function genFormPostParams($type = 'payment')
    {
        $this->genAesEncryptedPayment();

        if ($type === 'refund') {
            $this->validateRefund();

            $this->parameters = [
                'MerchantID_' => $this->merchantId,
                'PostData_'   => $this->aesPayload,
            ];
        } elseif ($type === 'payment') {
            $this->validateOrder();

            $this->parameters = [
                'MerchantID' => $this->merchantId,
                'TradeInfo'  => $this->aesPayload,
                'TradeSha'   => $this->genCheckValue(),
                'Version'    => $this->version,
            ];
        } else {
            throw new \InvalidArgumentException('unkown payment type');
        }

        $formPost = [
            'endpoint' => $this->actionUrl,
            'params'   => $this->parameters,
        ];

        return $formPost;
    }

    /**
     * @param string $type
     * @return string
     */
    public function genCheckValue($type = 'payment')
    {

        if (($this->version >= 1.4) && ($type === 'payment')) {
            $checkMerStr = sprintf(
                "HashKey=%s&%s&HashIV=%s",
                $this->hashKey,
                $this->aesPayload,
                $this->hashIV
            );
        } else {
            $merArray = [
                'MerchantOrderNo' => $this->order['MerchantOrderNo'],
                'MerchantID'      => $this->merchantId,
            ];

            switch ($type) {
                case 'status':
                    $merArray['Amt'] = $this->order['Amt'];
                    break;
                case 'statusCheck':
                    $merArray['Amt'] = $this->order['Amt'];
                    $merArray['TradeNo'] = $this->order['TradeNo'];
                    break;
                case 'payment':
                default:
                    $merArray['Amt'] = $this->order['Amt'];
                    $merArray['Version'] = $this->version;
                    $merArray['TimeStamp'] = $this->order['TimeStamp'];
                    break;
            }

            ksort($merArray);

            switch ($type) {
                case 'status':
                    $merArray = array_merge(['IV' => $this->hashIV], $merArray, ['Key' => $this->hashKey]);
                    break;
                case 'statusCheck':
                    $merArray = array_merge(['HashIV' => $this->hashIV], $merArray, ['HashKey' => $this->hashKey]);
                    break;
                case 'payment':
                default:
                    $merArray = array_merge(['HashKey' => $this->hashKey], $merArray, ['HashIV' => $this->hashIV]);
                    break;
            }

            $checkMerStr = http_build_query($merArray);
        }

        return strtoupper($this->hashMaker($checkMerStr));
    }

    /**
     * @param null|string $merchantOrderNo
     * @param null|integer|float $amount
     * @param null|boolean $sandbox
     * @return bool
     */
    public function getPaymentStatus($merchantOrderNo = null, $amount = null, $sandbox = null)
    {
        $sandbox = !!$sandbox;

        $endpoint = $sandbox ?
            'https://ccore.newebpay.com/API/QueryTradeInfo' :
            'https://core.newebpay.com/API/QueryTradeInfo';

        $client = new Client();

        $this->clearOrder();

        $this->order['Amt'] = $amount;
        $this->order['MerchantOrderNo'] = $merchantOrderNo;

        $code = $this->genCheckValue('status');

        $result = $client->post(
            $endpoint,
            [
                'form_params' => [
                    'Amt'             => $amount,
                    'Version'         => '1.1',
                    'TimeStamp'       => time(),
                    'MerchantID'      => $this->merchantId,
                    'CheckValue'      => $code,
                    'RespondType'     => 'JSON',
                    'MerchantOrderNo' => $merchantOrderNo,
                ],
            ]
        );

        if ($result->getStatusCode() != 200) {
            return false;
        }

        $response = json_decode($result->getBody()->getContents(), true);

        if ($response['Status'] != 'SUCCESS') {
            return false;
        }

        $this->clearOrder();

        $this->order['Amt'] = $response['Result']['Amt'];
        $this->order['TradeNo'] = $response['Result']['TradeNo'];
        $this->order['MerchantOrderNo'] = $response['Result']['MerchantOrderNo'];

        $rspChkCode = $this->genCheckValue('statusCheck');

        $this->clearOrder();

        if ($response['Result']['CheckCode'] != $rspChkCode) {
            return false;
        }

        return $response['Result'];
    }

    public function genAesEncryptedPayment()
    {
        ksort($this->order);

        $payloadQuery = http_build_query($this->order);

        $rawEncrypted = openssl_encrypt(
            $payloadQuery,
            'aes-256-cbc',
            $this->hashKey,
            OPENSSL_RAW_DATA,
            $this->hashIV
        );

        $this->aesPayload = bin2hex($rawEncrypted);

        return $this->aesPayload;
    }
}
