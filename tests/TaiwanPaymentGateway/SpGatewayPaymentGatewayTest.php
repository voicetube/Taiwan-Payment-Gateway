<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 16/04/2017
 * Time: 4:08 AM
 */

namespace TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\SpGatewayPaymentGateway;

class SpGatewayPaymentGatewayTest extends \PHPUnit_Framework_TestCase
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
		$this->gw = new SpGatewayPaymentGateway([
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
		$this->assertInstanceOf(SpGatewayPaymentGateway::class, $this->gw);
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

	public function testNewOrderJSON()
	{
		$this->gw->newOrder(
			$this->order['mid'],
			$this->order['amount'],
			$this->order['itemDesc'],
			$this->order['orderComment'],
			'JSON',
			$this->order['ts']
		);

		$this->assertNotEmpty($this->gw->getOrder());
	}

	public function testNewOrderPOST()
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
		$this->testNewOrderJSON();
		$this->gw->useCredit()->setCreditInstallment(3);
		$this->assertNotEmpty($this->gw->genForm(false));
	}

	public function testUnionPay()
	{
		$this->testNewOrderJSON();
		$this->gw->setUnionPay();
		$this->assertNotEmpty($this->gw->genForm(false));
	}

	public function testUserCanModifyEmail()
	{
		$this->testNewOrderJSON();
		$this->gw->useCredit()->triggerEmailModify(true);
		$this->assertNotEmpty($this->gw->genForm(false));
	}

	public function testUserCantModifyEmail()
	{
		$this->testNewOrderJSON();
		$this->gw->useCredit()->triggerEmailModify(false);
		$this->assertNotEmpty($this->gw->genForm(false));
	}

	public function testOnlyGatewayMemberCanPay()
	{
		$this->testNewOrderJSON();
		$this->gw->useCredit()->onlyLoginMemberCanPay(true);
		$this->assertNotEmpty($this->gw->genForm(false));
	}

	public function testNotOnlyGatewayMemberCanPay()
	{
		$this->testNewOrderJSON();
		$this->gw->useCredit()->onlyLoginMemberCanPay(false);
		$this->assertNotEmpty($this->gw->genForm(false));
	}

	public function testPaymentMethodNotSet()
	{
		try{
			$this->testNewOrderJSON();
			$this->assertNotEmpty($this->gw->genForm(true));
		} catch (\Exception $e) {
			$this->assertEquals('Payment method not set', $e->getMessage());
		}
	}
	public function testPaymentInfoUrlNotSet()
	{
		try{
			$this->testNewOrderJSON();
			$this->gw->useBarCode()->setConfig('paymentInfoUrl', 0);
			$this->gw->setEmail('abc@test.com')->genForm();
		} catch (\Exception $e) {
			$this->assertEquals('PaymentInfoURL not set', $e->getMessage());
		}
	}

	public function testWrongEmailAddress()
	{
		try{
			$this->testNewOrderJSON();
			$this->gw->useBarCode()->setConfig('paymentInfoUrl', 0);
			$this->gw->setEmail('abc@test')->genForm();
		} catch (\Exception $e) {
			$this->assertEquals('Invalid email format', $e->getMessage());
		}
	}

	public function testUseWebAtm()
	{
		$this->testNewOrderJSON();
		$this->gw
			->useWebATM()
			->setOrderExpire(mktime(23, 59, 59, date('m'), date('d') + 3, date('Y')))
			->setConfig('paymentInfoUrl', 'https://localhost/payment/information');
	}

	public function testUseAtm()
	{
		$this->testNewOrderJSON();
		$this->gw
			->useATM()
			->setOrderExpire(date('Y/m/d H:i:s', mktime(23, 59, 59, date('m'), date('d') + 3, date('Y'))))
			->setConfig('paymentInfoUrl', 'https://localhost/payment/information');
	}

	public function testUseCvs()
	{
		$this->testNewOrderJSON();
		$this->gw->useCVS()->setConfig('paymentInfoUrl', 'https://localhost/payment/information');
	}
}
