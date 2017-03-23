<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 23/03/2017
 * Time: 12:21 PM
 */

namespace VoiceTube\TaiwanPaymentGateway\Provider;


class EcPayProvider extends Provider implements ProviderInterface
{

	protected $url_encode_mapping;

	public function __construct(array $config = [])
	{
		parent::__construct();

		define('PG_PAY_METHOD_BARCODE', 'BARCODE');
		define('PG_PAY_METHOD_WEB_ATM', 'WebATM');
		define('PG_PAY_METHOD_CREDIT', 'Credit');
		define('PG_PAY_METHOD_ATM', 'ATM');
		define('PG_PAY_METHOD_ALL', 'ALL');
		define('PG_PAY_METHOD_CVS', 'CVS');

		$this->setArrayConfig($config);

		if (empty($this->actionUrl)) $this->actionUrl = 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/';
		if (empty($this->version)) $this->version = 'V4';

		$this->url_encode_mapping = [
			'%2D' => '-',
			'%5F' => '_',
			'%2E' => '.',
			'%21' => '!',
			'%2A' => '*',
			'%2d' => '-',
			'%5f' => '_',
			'%2e' => '.',
			'%2a' => '*',
			'%28' => '(',
			'%29' => ')',
			'%20' => '+'];
	}

	public function needExtraPaidInfo() {
		$this->order['NeedExtraPaidInfo'] = 'Y';
		return true;
	}

	public function setCreditInstallment($months, $total_amount = 0)
	{
		$this->order['CreditInstallment'] = $months;
		$this->order['InstallmentAmount'] = $total_amount ? $total_amount : $this->order['TotalAmount'];
		return true;
	}

	/**
	 * @param int $expire_Date
	 * @return bool
	 */
	public function setOrderExpire($expire_Date)
	{
		if (is_numeric($expire_Date)) $expire_Date = intval($expire_Date);

		switch ($this->order['ChoosePayment']) {
			case PG_PAY_METHOD_ATM:
				$this->order['ExpireDate'] = $expire_Date;
				break;
			case PG_PAY_METHOD_CVS:
				$this->order['StoreExpireDate'] = mktime(
					23, 59, 59, date('m'),
					date('d') + $expire_Date, date('Y')
				) - time();
				break;
			case PG_PAY_METHOD_BARCODE:
				$this->order['StoreExpireDate'] = $expire_Date;
				break;
		}

		return true;
	}

	public function setUnionPay()
	{
		$this->order['UnionPay'] = 1;
		return true;
	}

	public function genCheckValue()
	{
		uksort($this->order, 'strcasecmp');

		$mer_array = array_merge(['HashKey' => $this->hashKey], $this->order, ['HashIV' => $this->hashIV]);

		$check_mer_str = urldecode(http_build_query($mer_array));

		foreach ($this->url_encode_mapping as $key => $value) {
			$check_mer_str = str_replace($key, $value, $check_mer_str);
		}

		$check_mer_str = strtolower(urlencode($check_mer_str));

		return strtoupper(hash('sha256', $check_mer_str));
	}

	public function matchCheckCode(array $payload = [])
	{
		$CheckMacValue = $_POST['CheckMacValue'];

		unset($_POST['CheckMacValue']);

		uksort($_POST, 'strcasecmp');

		$mer_array = array_merge(['HashKey' => $this->hashKey], $_POST, ['HashIV' => $this->hashIV]);

		$check_mer_str = urldecode(http_build_query($mer_array));

		foreach ($this->url_encode_mapping as $key => $value) {
			$check_mer_str = str_replace($key, $value, $check_mer_str);
		}

		$check_mer_str = strtolower(urlencode($check_mer_str));

		return $CheckMacValue == strtoupper(hash('sha256', $check_mer_str));
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function newOrder(
		$type = PG_PAY_METHOD_CREDIT,
		$respond_type = 'POST',
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
		if (!isset($this->actionUrl)) throw new \InvalidArgumentException('ActionURL not set');

		if (($type == PG_PAY_METHOD_ATM) || ($type == PG_PAY_METHOD_BARCODE) || ($type == PG_PAY_METHOD_CVS)) {
			if (!isset($this->paymentInfoUrl)) throw new \InvalidArgumentException('PaymentInfoURL not set');
		}

		$timestamp = empty($timestamp) ? time() : $timestamp;

		$this->clearOrder();

		$this->order['ChoosePayment'] = $type;
//		$this->order['Remark'] = '';
//		$this->order['Redeem'] = '';
//		$this->order['ItemURL'] = '';
//		$this->order['OrderResultURL'] = '';
//		$this->order['ChooseSubPayment'] = '';
//		$this->order['CreditInstallment'] = '';
//		$this->order['InstallmentAmount'] = '';
		$this->order['PaymentType'] = 'aio';
		$this->order['MerchantID'] = $this->merchantId;
		$this->order['MerchantTradeDate'] = date("Y/m/d H:i:s", $timestamp);
		$this->order['MerchantTradeNo'] = $merchant_order_no;
		$this->order['TotalAmount'] = intval($amount);
		$this->order['ItemName'] = $item_describe;
		$this->order['TradeDesc'] = $order_comment;
		$this->order['EncryptType'] = 1;


		if (!empty($this->returnUrl)) $this->order['ReturnURL'] = $this->returnUrl;
		if (!empty($this->clientBackUrl)) $this->order['ClientBackURL'] = $this->clientBackUrl;

		if ((!empty($this->paymentInfoUrl)) && (in_array($type, [PG_PAY_METHOD_ATM, PG_PAY_METHOD_CVS, PG_PAY_METHOD_BARCODE]))) {
			$this->order['PaymentInfoURL'] = $this->paymentInfoUrl;
		}

		return true;
	}

	public function processOrder($type = 'POST')
	{
		return $this->processOrderPost();
	}

	/**
	 * @return bool|array
	 */
	public function processOrderPost()
	{
		if (!isset($_POST)) return false;
		if (empty($_POST)) return false;

		$post = filter_var_array($_POST, [
			'MerchantID'           => FILTER_SANITIZE_STRING,
			'MerchantTradeNo'      => FILTER_SANITIZE_STRING,
			'RtnCode'              => FILTER_VALIDATE_INT,
			'TradeAmt'             => FILTER_VALIDATE_INT,
			'RtnMsg'               => FILTER_SANITIZE_STRING,
			'TradeNo'              => FILTER_SANITIZE_STRING,
			'PaymentDate'          => FILTER_SANITIZE_STRING,
			'PaymentType'          => FILTER_SANITIZE_STRING,
			'PaymentTypeChargeFee' => FILTER_VALIDATE_INT,
			'TradeDate'            => FILTER_SANITIZE_STRING,
			'SimulatePaid'         => FILTER_VALIDATE_INT,
			'CheckMacValue'        => FILTER_SANITIZE_STRING,
			'PeriodType'           => FILTER_SANITIZE_STRING,
			'Frequency'            => FILTER_VALIDATE_INT,
			'ExecTimes'            => FILTER_VALIDATE_INT,
			'amount'               => FILTER_VALIDATE_INT,
			'gwsr'                 => FILTER_VALIDATE_INT,
			'stage'                => FILTER_VALIDATE_INT,
			'stast'                => FILTER_VALIDATE_INT,
			'eci'                  => FILTER_VALIDATE_INT,
			'staed'                => FILTER_VALIDATE_INT,
			'red_dan'              => FILTER_VALIDATE_INT,
			'red_yet'              => FILTER_VALIDATE_INT,
			'red_de_amt'           => FILTER_VALIDATE_INT,
			'red_ok_amt'           => FILTER_VALIDATE_INT,
			'PeriodAmount'         => FILTER_VALIDATE_INT,
			'TotalSuccessTimes'    => FILTER_VALIDATE_INT,
			'FirstAuthAmount'      => FILTER_VALIDATE_INT,
			'TotalSuccessAmount'   => FILTER_VALIDATE_INT,
			'process_date'         => FILTER_SANITIZE_STRING,
			'auth_code'            => FILTER_SANITIZE_STRING,
			'WebATMAccBank'        => FILTER_SANITIZE_STRING,
			'WebATMAccNo'          => FILTER_SANITIZE_STRING,
			'WebATMBankName'       => FILTER_SANITIZE_STRING,
			'ATMAccBank'           => FILTER_SANITIZE_STRING,
			'ATMAccNo'             => FILTER_SANITIZE_STRING,
			'PaymentNo'            => FILTER_SANITIZE_STRING,
			'PayFrom'              => FILTER_SANITIZE_STRING,
			'card4no'              => FILTER_SANITIZE_STRING,
			'card6no'              => FILTER_SANITIZE_STRING,
		], false);

		if (!$post['RtnCode']) return false;

		$post['matched'] = $this->matchCheckCode($post);

		return $post;
	}

	public function genForm($auto_submit = true)
	{
		$this->order['CheckMacValue'] = $this->genCheckValue();

		$formId = sprintf("PG_FORM_GO_%s", sha1(time()));

		$html = sprintf("<form style='display: none' id='%s' method='post' action='%s'>", $formId, "{$this->actionUrl}{$this->version}");
		foreach ($this->order as $key => $value) {
			$html .= sprintf("<input type='text' name='%s' value='%s'>", $key, $value);
		}
		$html .= "</form>";

		if ($auto_submit) $html .= sprintf("<script>document.getElementById('%s').submit();</script>", $formId);

		return $html;
	}

	public function rspOk()
	{
		echo "1|OK";
		return true;
	}

	public function rspError($msg = '')
	{
		echo "0|$msg";
		return true;
	}
}