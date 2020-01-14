<?php

namespace TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\TapPayPaymentGateway;

class TapPayPaymentGatewayTest extends \PHPUnit_Framework_TestCase
{
	protected $gw;
	protected $order = [
		'mid' => null,
		'amount' => 100,
		'itemDesc' => 'VT-TPG-TEST-ITEM-DESC',
		'orderComment' => 'VT-TPG-TEST-ITEM-DESC',
		'ts' => 1492297995
	];

	// partnerKey & merchant_id defined on Portal
	protected $config = [
		'prime' => 'test_3a2fb2b7e892b914a03c95dd4dd5dc7970c908df67a49527c0a648b2bc9',
		'cardholder' => [
			'name' => 'VT-TPG-TEST-NAME',
			'email' => 'VT-TPG-TEST-EMAIL@voicetube.com',
			'phone_number' => '8861234567890',
		],
		'partnerKey' => '',
		'merchantId' => '',
	];

	protected $testPrimeUrl = 'https://sandbox.tappaysdk.com/tpc/payment/pay-by-prime';

	function __construct($name = null, array $data = [], $dataName = '')
	{
		$this->order['mid'] = 'TEST' . time();
		$this->gw = new TapPayPaymentGateway($this->config);
	}

	public function testConstruct()
	{
		$config = $this->config;

		$this->gw = new TapPayPaymentGateway($config);
		$this->assertInstanceOf(TapPayPaymentGateway::class, $this->gw);
	}

	// TapPay: create order
	public function testNewOrder()
	{
		$this->gw
		->setResultUrl('aaaa', 'bbbb')
		->newOrder(
			$this->order['mid'],
			$this->order['amount'],
			$this->order['itemDesc'],
			$this->order['orderComment'],
			'POST',
			$this->order['ts']
		);

		$order = $this->gw->getOrder();

		$this->assertArrayHasKey('bank_transaction_id', $order);
	}

	// TapPay: send pay by prime and send pay by token
	public function testSendPrimeOrder()
	{
		$this->gw
		->setResultUrl('aaaa', 'bbbb')
		->newOrder(
			$this->order['mid'],
			$this->order['amount'],
			$this->order['itemDesc'],
			$this->order['orderComment'],
			'POST',
			$this->order['ts']
		);

		$order = $this->gw->getOrder();

		// send pay by prime
		$sendInfo = $this->gw->sendPrimeOrder($this->testPrimeUrl, $order);

		$response = json_decode($sendInfo->getBody());

		$this->assertEquals(200, $sendInfo->getStatusCode());

		$this->assertEquals(0, $response->status);
	}
}
