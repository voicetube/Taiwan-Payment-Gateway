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

    public function needExtraPaidInfo() { return true; }

    public function setCreditInstallment($months, $total_amount = 0)
    {
        $this->order['InstFlag'] = $months;
        return true;
    }

    public function setOrderExpire($expire_Date)
    {
        if (is_numeric($expire_Date)) $expire_Date = intval($expire_Date);
        if (is_string($expire_Date)) $expire_Date = intval(strtotime($expire_Date));

        $this->order['ExpireDate'] = date('Ymd', $expire_Date);
	    return true;
    }

    public function setUnionPay()
    {
        $this->order['UNIONPAY'] = 1;
        if (isset($this->order[PG_PAY_METHOD_CREDIT])) unset($this->order[PG_PAY_METHOD_CREDIT]);
        return true;
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

    public function matchCheckCode(array $payload = [])
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

        return true;
    }

	public function processOrder($type = 'JSON')
    {
        switch($type) {
            case 'JSON':
                return $this->processOrderJson();
            break;
            case 'POST':
            default:
	            return $this->processOrderPost();
            break;
        }
    }

	/**
	 * @return bool|array
	 */
	public function processOrderJson()
    {
        if (!isset($_POST['JSONData'])) return false;

        $post = json_decode($_POST['JSONData'], true);

        if ($post['Status'] !== 'SUCCESS') return false;

        $result = json_decode($post['Result'], true);

        $result['matched'] = $this->matchCheckCode($result);

        return $result;
    }

	/**
	 * @return bool|array
	 */
	public function processOrderPost()
    {
        if (!isset($_POST)) return false;
        if (empty($_POST)) return false;

	    $post = filter_var_array($_POST, [
		    'Status'            => FILTER_SANITIZE_STRING,
		    'Message'           => FILTER_SANITIZE_STRING,
		    'MerchantID'        => FILTER_SANITIZE_STRING,
		    'Amt'               => FILTER_VALIDATE_INT,
		    'TradeNo'           => FILTER_SANITIZE_STRING,
		    'MerchantOrderNo'   => FILTER_SANITIZE_STRING,
		    'PaymentType'       => FILTER_SANITIZE_STRING,
		    'RespondType'       => FILTER_SANITIZE_STRING,
		    'CheckCode'         => FILTER_SANITIZE_STRING,
		    'PayTime'           => FILTER_SANITIZE_STRING,
		    'IP'                => FILTER_VALIDATE_IP,
		    'EscrowBank'        => FILTER_SANITIZE_STRING,
		    'TokenUseStatus'    => FILTER_VALIDATE_INT,
		    'RespondCode'       => FILTER_SANITIZE_STRING,
		    'Auth'              => FILTER_SANITIZE_STRING,
		    'Card6No'           => FILTER_SANITIZE_STRING,
		    'Card4No'           => FILTER_SANITIZE_STRING,
		    'Inst'              => FILTER_VALIDATE_INT,
		    'InstFirst'         => FILTER_VALIDATE_INT,
		    'InstEach'          => FILTER_VALIDATE_INT,
		    'ECI'               => FILTER_SANITIZE_STRING,
		    'PayBankCode'       => FILTER_SANITIZE_STRING,
		    'PayerAccount5Code' => FILTER_SANITIZE_STRING,
		    'CodeNo'            => FILTER_SANITIZE_STRING,
		    'Barcode_1'         => FILTER_SANITIZE_STRING,
		    'Barcode_2'         => FILTER_SANITIZE_STRING,
		    'Barcode_3'         => FILTER_SANITIZE_STRING,
		    'PayStore'          => FILTER_SANITIZE_STRING
	    ], false);

        if ($post['Status'] !== 'SUCCESS') return false;

	    $post['matched'] = $this->matchCheckCode($post);

        return $post;
    }

    public function genForm($auto_submit = true)
    {
        $this->order['CheckValue'] = $this->genCheckValue();

        $formId = sprintf("PG_FORM_GO_%s", sha1(time()));

        $html = sprintf("<form style='display: none' id='%s' method='post' action='%s'>", $formId, $this->actionUrl);
        foreach ($this->order as $key => $value) {
            $html .= sprintf("<input type='text' name='%s' value='%s'>", $key, $value);
        }
        $html .= "</form>";

        if ($auto_submit) $html .= sprintf("<script>document.getElementById('%s').submit();</script>", $formId);

        return $html;
    }

}