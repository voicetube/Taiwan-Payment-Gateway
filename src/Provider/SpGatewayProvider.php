<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 20/03/2017
 * Time: 4:55 PM
 */

namespace VoiceTube\TaiwanPaymentGateway\Provider;


class SpGatewayProvider extends Provider implements ProviderInterface
{

    public function __construct(array $config = [])
    {
        parent::__construct();

        define('PG_PAY_METHOD_BARCODE', 'BARCODE');
        define('PG_PAY_METHOD_WEB_ATM', 'WEBATM');
        define('PG_PAY_METHOD_CREDIT', 'CREDIT');
        define('PG_PAY_METHOD_ATM', 'VACC');
        define('PG_PAY_METHOD_CVS', 'CVS');

        $this->setArrayConfig($config);

        if (empty($this->actionUrl)) $this->actionUrl = 'https://core.spgateway.com/MPG/mpg_gateway';
        if (empty($this->version)) $this->version = '1.2';
    }

    public function needExtraPaidInfo() {}

    public function setCreditInstallment($months, $total_amount = 0)
    {
        $this->order['InstFlag'] = $months;
    }

    public function setOrderExpire($expire_Date)
    {
        if (is_numeric($expire_Date)) $expire_Date = intval($expire_Date);
        if (is_string($expire_Date)) $expire_Date = intval(strtotime($expire_Date));

        $this->order['ExpireDate'] = date('Ymd', $expire_Date);
    }

    public function setUnionPay()
    {
        $this->order['UNIONPAY'] = 1;
        if (isset($this->order[PG_PAY_METHOD_CREDIT])) unset($this->order[PG_PAY_METHOD_CREDIT]);
    }

    public function genCheckValue()
    {
        $mer_array = [
            'MerchantOrderNo'   => $this->order['MerchantOrderNo'],
            'MerchantID'        => $this->merchantId,
            'TimeStamp'         => $this->order['TimeStamp'],
            'Version'           => $this->version,
            'Amt'               => $this->order['Amt'],
        ];

        ksort($mer_array);

        $mer_array = array_merge(['HashKey' => $this->hashKey], $mer_array, ['HashIV' => $this->hashIV]);

        $check_mer_str = http_build_query($mer_array);

        return strtoupper(hash("sha256", $check_mer_str));
    }

    public function matchCheckCode($payload)
    {
        $matched_code = $payload['CheckCode'];

        $check_code = [
            "Amt" => $payload['Amt'],
            "TradeNo" => $payload['TradeNo'],
            "MerchantID" => $payload['MerchantID'],
            "MerchantOrderNo" => $payload['MerchantOrderNo'],
        ];

        ksort($check_code);

        $check_code = array_merge(['HashIV' => $this->hashIV], $check_code, ['HashKey' => $this->hashKey]);

        $check_str = http_build_query($check_code);

        return $matched_code == strtoupper(hash("sha256", $check_str));
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function newOrder(
        $type = PG_PAY_METHOD_CREDIT,
        $respond_type = 'JSON',
        $merchant_order_no,
        $amount,
        $item_describe,
        $email,
        $order_comment,
        $timestamp = 0
    ) {
        /**
         * Argument Check
         */
        if (!isset($this->hashIV)) throw new \InvalidArgumentException('HashIV not set');
        if (!isset($this->hashKey)) throw new \InvalidArgumentException('HashKey not set');
        if (!isset($this->merchantId)) throw new \InvalidArgumentException('MerchantID not set');

        if (!isset($this->returnUrl)) throw new \InvalidArgumentException('ReturnURL not set');
        if (!isset($this->notifyUrl)) throw new \InvalidArgumentException('NotifyURL not set');
        if (!isset($this->actionUrl)) throw new \InvalidArgumentException('ActionURL not set');

        if (($type == PG_PAY_METHOD_ATM) || ($type == PG_PAY_METHOD_BARCODE) || ($type == PG_PAY_METHOD_CVS)) {
            if (!isset($this->paymentInfoUrl)) throw new \InvalidArgumentException('PaymentInfoURL not set');
        }

        $timestamp = empty($timestamp) ? time() : $timestamp;

        $this->clearOrder();

        $this->order[$type] = 1;
        $this->order['MerchantID'] = $this->merchantId;
        $this->order['RespondType'] = $respond_type;
        $this->order['TimeStamp'] = $timestamp;
        $this->order['Version'] = $this->version;
        $this->order['LangType'] = 'zh-tw';
        $this->order['MerchantOrderNo'] = $merchant_order_no;
        $this->order['Amt'] = intval($amount);
        $this->order['ItemDesc'] = $item_describe;
        $this->order['Email'] = $email;
        $this->order['EmailModify'] = 0;
        $this->order['LoginType'] = 0;
        $this->order['OrderComment'] = $order_comment;

        if (!empty($this->returnUrl)) $this->order['ReturnURL'] = $this->returnUrl;
        if (!empty($this->notifyUrl)) $this->order['NotifyURL'] = $this->notifyUrl;
        if (!empty($this->paymentInfoUrl)) $this->order['CustomerURL'] = $this->paymentInfoUrl;
        if (!empty($this->clientBackUrl)) $this->order['ClientBackURL'] = $this->clientBackUrl;
    }

    public function processOrder($payload)
    {
        if (empty($payload["Result"]['CheckCode'])) return false;

        if ($this->matchCheckCode($payload["Result"]) == false) return false;

        return $payload["Result"];
    }

    public function genForm()
    {
        $this->order['CheckValue'] = $this->genCheckValue();

        $formId = sprintf("PG_FORM_GO_%s", sha1(time()));

        $html = sprintf("<form style='display: none' id='%s' method='post' action='%s'>", $formId, $this->actionUrl);
        foreach ($this->order as $key => $value) {
            $html .= sprintf("<input type='text' name='%s' value='%s'>", $key, $value);
        }
        $html .= "</form>";

        $html .= sprintf("<script>document.getElementById('%s').submit();</script>", $formId);
        return $html;
    }

}