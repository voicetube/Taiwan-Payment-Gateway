<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Common;

class SpGatewayPaymentGateway extends Common\AbstractGateway implements Common\GatewayInterface
{

	public function __construct(array $config = [])
	{
		parent::__construct($config);

		if (empty($this->actionUrl)) $this->actionUrl = 'https://core.spgateway.com/MPG/mpg_gateway';
		if (empty($this->version)) $this->version = '1.2';

		return $this;
	}

	public function useBarCode()
	{
		$this->order['BARCODE'] = 1;
		return $this;
	}

	public function useWebATM()
	{
		$this->order['WEBATM'] = 1;
		return $this;
	}

	public function useCredit()
	{
		$this->order['CREDIT'] = 1;
		return $this;
	}

	public function useATM()
	{
		$this->order['VACC'] = 1;
		return $this;
	}

	public function useCVS()
	{
		$this->order['CVS'] = 1;
		return $this;
	}

	public function triggerEmailModify($mode = false)
	{
		$this->order['EmailModify'] = $mode ? 1 : 0;
		return $this;
	}

	public function onlyLoginMemberCanPay($mode = false)
	{
		$this->order['LoginType'] = $mode ? 1 : 0;
		return $this;
	}

	public function setCreditInstallment($months, $total_amount = 0)
	{
		$this->order['InstFlag'] = $months;
		return $this;
	}

	public function setOrderExpire($expire_Date)
	{
		if (is_numeric($expire_Date)) $expire_Date = intval($expire_Date);
		if (is_string($expire_Date)) $expire_Date = intval(strtotime($expire_Date));

		$this->order['ExpireDate'] = date('Ymd', $expire_Date);
		return $this;
	}

	public function setUnionPay()
	{
		$this->order['UNIONPAY'] = 1;
		if (isset($this->order['CREDIT'])) unset($this->order['CREDIT']);
		return $this;
	}

	public function setEmail($email)
	{
		$this->order['Email'] = $email;
		return $this;
	}

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
	 * @return SpGatewayPaymentGateway
	 */
	public function newOrder(
		$merchant_order_no,
		$amount,
		$item_describe,
		$order_comment,
		$respond_type = 'JSON',
		$timestamp = 0
	)
	{
		/**
		 * Argument Check
		 */
		if (!isset($this->hashIV)) throw new \InvalidArgumentException('HashIV not set');
		if (!isset($this->hashKey)) throw new \InvalidArgumentException('HashKey not set');
		if (!isset($this->merchantId)) throw new \InvalidArgumentException('MerchantID not set');

		if (!isset($this->returnUrl)) throw new \InvalidArgumentException('ReturnURL not set');
		if (!isset($this->notifyUrl)) throw new \InvalidArgumentException('NotifyURL not set');
		if (!isset($this->actionUrl)) throw new \InvalidArgumentException('ActionURL not set');

		$timestamp = empty($timestamp) ? time() : $timestamp;

		$this->clearOrder();

		$this->order['Amt'] = intval($amount);
		$this->order['Version'] = $this->version;
		$this->order['LangType'] = 'zh-tw';
		$this->order['TimeStamp'] = $timestamp;
		$this->order['MerchantID'] = $this->merchantId;
		$this->order['RespondType'] = $respond_type;



		$this->order['MerchantOrderNo'] = $merchant_order_no;

		$this->order['ItemDesc'] = $item_describe;
		$this->order['OrderComment'] = $order_comment;

		if (!empty($this->returnUrl)) $this->order['ReturnURL'] = $this->returnUrl;
		if (!empty($this->notifyUrl)) $this->order['NotifyURL'] = $this->notifyUrl;
		if (!empty($this->paymentInfoUrl)) $this->order['CustomerURL'] = $this->paymentInfoUrl;
		if (!empty($this->clientBackUrl)) $this->order['ClientBackURL'] = $this->clientBackUrl;

		return $this;
	}

	public function genForm($auto_submit = true)
	{

		if (
			!isset($this->order['BARCODE']) &&
			!isset($this->order['CREDIT']) &&
			!isset($this->order['WEBATM']) &&
			!isset($this->order['VACC']) &&
			!isset($this->order['CVS'])
		) {
			throw new \InvalidArgumentException('Payment method not set');
		}

		if (
			in_array('BARCODE', $this->order) ||
			in_array('VACC', $this->order) ||
			in_array('CVS', $this->order)
		) {
			if (!isset($this->paymentInfoUrl)) throw new \InvalidArgumentException('PaymentInfoURL not set');
		}

		if (!isset($this->order['LoginType'])) $this->order['LoginType'] = 0;
		if (!isset($this->order['EmailModify'])) $this->order['EmailModify'] = 0;

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

	public function genCheckValue()
	{
		$mer_array = [
			'MerchantOrderNo' => $this->order['MerchantOrderNo'],
			'MerchantID'      => $this->merchantId,
			'TimeStamp'       => $this->order['TimeStamp'],
			'Version'         => $this->version,
			'Amt'             => $this->order['Amt'],
		];

		ksort($mer_array);

		$mer_array = array_merge(['HashKey' => $this->hashKey], $mer_array, ['HashIV' => $this->hashIV]);

		$check_mer_str = http_build_query($mer_array);

		return strtoupper(hash("sha256", $check_mer_str));
	}
}