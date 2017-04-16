<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 16/04/2017
 * Time: 10:23 AM
 */

namespace TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\EcPayPaymentGateway;

class EcPayPaymentGatewayTest extends \PHPUnit_Framework_TestCase
{
	protected $gw;
	protected $order = [
		'mid' => "VT-TPG-TEST",
		'amount' => 100,
		'itemDesc' => 'VT-TPG-TEST-ITEM-DESC',
		'orderComment' => 'VT-TPG-TEST-ITEM-DESC',
		'ts' => 1492287995
	];

	function __construct($name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->gw = new EcPayPaymentGateway([
			'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
			'hashIV'        => 'KHQ49UsmwMZJk6D1',
			'merchantId'    => 'MS11434419',
			'version'       => '1.2',
			'actionUrl'     => 'https://ccore.spgateway.com/MPG/mpg_gateway',
			'returnUrl'     => 'https://localhost/tpg/confirm',
			'notifyUrl'     => '',
			'clientBackUrl' => 'https://localhost/tpg/return',
		]);
	}

	public function testConstruct()
	{
		$this->gw = new EcPayPaymentGateway([
			'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
			'hashIV'        => 'KHQ49UsmwMZJk6D1',
			'merchantId'    => 'MS11434419',
			'version'       => '1.2',
			'actionUrl'     => 'https://ccore.spgateway.com/MPG/mpg_gateway',
			'returnUrl'     => 'https://localhost/tpg/confirm',
			'notifyUrl'     => '',
			'clientBackUrl' => 'https://localhost/tpg/return',
		]);
		$this->assertInstanceOf(EcPayPaymentGateway::class, $this->gw);
	}

	public function testGetNonExistsConfig()
	{
		try {
			$this->gw->getConfig('NonExists');
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals('config key not exists.', $e->getMessage());
		}
	}

	public function testSetNonExistsConfig()
	{
		try {
			$this->gw->setConfig('NonExists', sha1(time()));
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals('config key not exists.', $e->getMessage());
		}
	}

	public function testNewOrder()
	{
		$this->gw->newOrder(
			$this->order['mid'],
			$this->order['amount'],
			$this->order['itemDesc'],
			$this->order['orderComment'],
			'POST',
			$this->order['ts']
		);

		$this->assertNotEmpty($this->gw->getOrder());
	}

	public function testGenOrderForm()
	{
		$this->testNewOrder();
		$this->gw->useCredit()->needExtraPaidInfo()->setCreditInstallment(3, $this->order['amount']);
		$this->assertNotEmpty($this->gw->genForm(false));
	}

	public function testUnionPay()
	{
		$this->testNewOrder();
		$this->gw->useCredit()->setUnionPay();
		$this->assertNotEmpty($this->gw->genForm());
	}

	public function testPaymentMethodNotSet()
	{
		try{
			$this->testNewOrder();
			$this->assertNotEmpty($this->gw->genForm(true));
		} catch (\Exception $e) {
			$this->assertEquals('Payment method not set', $e->getMessage());
		}
	}
	public function testPaymentInfoUrlNotSet()
	{
		try{
			$this->testNewOrder();
			$this->gw->useBarCode()->setConfig('paymentInfoUrl', 0);
			$this->gw->genForm();
		} catch (\Exception $e) {
			$this->assertEquals('PaymentInfoURL not set', $e->getMessage());
		}
	}

	public function testUseWebAtm()
	{
		$this->testNewOrder();
		$this->gw
			->useWebATM()
			->setConfig('paymentInfoUrl', 'https://localhost/payment/information');
	}

	public function testUseAtm()
	{
		$this->testNewOrder();
		$this->gw
			->useATM()
			->setOrderExpire(mktime(23, 59, 59, date('m'), date('d') + 3, date('Y')))
			->setConfig('paymentInfoUrl', 'https://localhost/payment/information');
	}

	public function testUseCvs()
	{
		$this->testNewOrder();
		$this->gw
			->useCVS()
			->setOrderExpire(7)
			->setConfig('paymentInfoUrl', 'https://localhost/payment/information');
	}

	public function testUseBarCode()
	{
		$this->testNewOrder();
		$this->gw
			->useBarCode()
			->setOrderExpire(mktime(23, 59, 59, date('m'), date('d') + 7, date('Y')))
			->setConfig('paymentInfoUrl', 'https://localhost/payment/information');
	}

	public function testUseAll()
	{
		$this->testNewOrder();
		$this->gw
			->useALL()
			->setConfig('paymentInfoUrl', 'https://localhost/payment/information');
	}

	public function testNewOrderNoHashIv()
	{
		try {
			$gw = new EcPayPaymentGateway([
				'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
				'hashIV'        => null, //'KHQ49UsmwMZJk6D1',
				'merchantId'    => 'MS11434419',
				'returnUrl'     => 'https://localhost/tpg/confirm',
				'notifyUrl'     => '',
				'clientBackUrl' => 'https://localhost/tpg/return',
			]);

			$gw->newOrder($this->order['mid'],
				$this->order['amount'],
				$this->order['itemDesc'],
				$this->order['orderComment'],
				'JSON',
				$this->order['ts']
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals('HashIV not set', $e->getMessage());
		}

	}

	public function testNewOrderNoHashKey()
	{
		try {
			$gw = new EcPayPaymentGateway([
				'hashKey'       => null, //'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
				'hashIV'        => 'KHQ49UsmwMZJk6D1',
				'merchantId'    => 'MS11434419',
				'returnUrl'     => 'https://localhost/tpg/confirm',
				'notifyUrl'     => '',
				'clientBackUrl' => 'https://localhost/tpg/return',
			]);

			$gw->newOrder($this->order['mid'],
				$this->order['amount'],
				$this->order['itemDesc'],
				$this->order['orderComment'],
				'JSON',
				$this->order['ts']
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals('HashKey not set', $e->getMessage());
		}

	}

	public function testNewOrderNoMerchantID()
	{
		try {
			$gw = new EcPayPaymentGateway([
				'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
				'hashIV'        => 'KHQ49UsmwMZJk6D1',
				'merchantId'    => null, //'MS11434419',
				'returnUrl'     => 'https://localhost/tpg/confirm',
				'notifyUrl'     => '',
				'clientBackUrl' => 'https://localhost/tpg/return',
			]);

			$gw->newOrder($this->order['mid'],
				$this->order['amount'],
				$this->order['itemDesc'],
				$this->order['orderComment'],
				'JSON',
				$this->order['ts']
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals('MerchantID not set', $e->getMessage());
		}

	}

	public function testNewOrderNoReturnURL()
	{
		try {
			$gw = new EcPayPaymentGateway([
				'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
				'hashIV'        => 'KHQ49UsmwMZJk6D1',
				'merchantId'    => 'MS11434419',
				'returnUrl'     => null, //'https://localhost/tpg/confirm',
				'notifyUrl'     => '',
				'clientBackUrl' => 'https://localhost/tpg/return',
			]);

			$gw->newOrder($this->order['mid'],
				$this->order['amount'],
				$this->order['itemDesc'],
				$this->order['orderComment'],
				'JSON',
				$this->order['ts']
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals('ReturnURL not set', $e->getMessage());
		}

	}

	public function testNewOrderSetNotifyURL()
	{
		$gw = new EcPayPaymentGateway([
			'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
			'hashIV'        => 'KHQ49UsmwMZJk6D1',
			'merchantId'    => 'MS11434419',
			'returnUrl'     => 'https://localhost/tpg/confirm',
			'notifyUrl'     => 'https://localhost/tpg/notify',
			'clientBackUrl' => 'https://localhost/tpg/return',
		]);

		$gw->newOrder($this->order['mid'],
			$this->order['amount'],
			$this->order['itemDesc'],
			$this->order['orderComment'],
			'JSON',
			$this->order['ts']
		);

	}

	public function testNewOrderNoNotifyUrl()
	{
		try {
			$gw = new EcPayPaymentGateway([
				'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
				'hashIV'        => 'KHQ49UsmwMZJk6D1',
				'merchantId'    => 'MS11434419',
				'returnUrl'     => 'https://localhost/tpg/confirm',
				'notifyUrl'     => null,
				'clientBackUrl' => 'https://localhost/tpg/return',
			]);

			$gw->setConfig('paymentInfoUrl', 'https://localhost/tpg/info');

			$gw->newOrder($this->order['mid'],
				$this->order['amount'],
				$this->order['itemDesc'],
				$this->order['orderComment'],
				'JSON',
				$this->order['ts']
			);
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals('NotifyURL not set', $e->getMessage());
		}

	}

	public function testNewOrderNoActionURL()
	{
		try {
			$gw = new EcPayPaymentGateway([
				'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
				'hashIV'        => 'KHQ49UsmwMZJk6D1',
				'merchantId'    => 'MS11434419',
				'returnUrl'     => 'https://localhost/tpg/confirm',
				'notifyUrl'     => '',
				'clientBackUrl' => 'https://localhost/tpg/return',
			]);

			$gw->setConfig('actionUrl', 0);

			$gw->newOrder($this->order['mid'],
				$this->order['amount'],
				$this->order['itemDesc'],
				$this->order['orderComment'],
				'JSON',
				$this->order['ts']
			)->useBarCode();
		} catch (\InvalidArgumentException $e) {
			$this->assertEquals('ActionURL not set', $e->getMessage());
		}

	}

	public function testNewOrderSetPaymentInfoURL()
	{
		$gw = new EcPayPaymentGateway([
			'hashKey'       => 'a73rjr4ocBjDcy6UGltXINJBw2NcdCEo',
			'hashIV'        => 'KHQ49UsmwMZJk6D1',
			'merchantId'    => 'MS11434419',
			'returnUrl'     => 'https://localhost/tpg/confirm',
			'notifyUrl'     => '',
			'clientBackUrl' => 'https://localhost/tpg/return',
		]);

		$gw->setConfig('paymentInfoUrl', 'https://localhost/tpg/info');

		$gw->newOrder($this->order['mid'],
			$this->order['amount'],
			$this->order['itemDesc'],
			$this->order['orderComment'],
			'JSON',
			$this->order['ts']
		);
	}
}
