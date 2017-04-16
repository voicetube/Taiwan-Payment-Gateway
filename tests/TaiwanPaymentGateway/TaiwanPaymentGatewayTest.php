<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 16/04/2017
 * Time: 3:41 AM
 */

namespace TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\TaiwanPaymentGateway;

class TaiwanPaymentGatewayTest extends \PHPUnit_Framework_TestCase
{
	protected $gw;

	public function testFactory()
	{
		$this->gw = TaiwanPaymentGateway::create('SpGateway', [
			'hashKey'       => 'c7fe1bfba42369ec1add502c9917e14d',
			'hashIV'        => '245a49c8fb5151f0',
			'merchantId'    => 'MS1234567',
			'version'       => '1.2',
			'actionUrl'     => 'https://ccore.spgateway.com/MPG/mpg_gateway',
			'returnUrl'     => 'https://localhost/payment/confirm',
			'notifyUrl'     => 'https://localhost/payment/notify',
			'clientBackUrl' => 'https://localhost/payment/return',
			'paymentInfoUrl'=> 'https://localhost/payment/information',
		]);

		$this->assertEquals('VoiceTube\TaiwanPaymentGateway\SpGatewayPaymentGateway', get_class($this->gw));
	}

	public function testFactoryWrongProvider()
	{
		try {
			$provider = 'SpGatewa';

			$this->gw = TaiwanPaymentGateway::create($provider, [
				'hashKey'       => 'c7fe1bfba42369ec1add502c9917e14d',
				'hashIV'        => '245a49c8fb5151f0',
				'merchantId'    => 'MS1234567',
				'version'       => '1.2',
				'actionUrl'     => 'https://ccore.spgateway.com/MPG/mpg_gateway',
				'returnUrl'     => 'https://localhost/payment/confirm',
				'notifyUrl'     => 'https://localhost/payment/notify',
				'clientBackUrl' => 'https://localhost/payment/return',
				'paymentInfoUrl'=> 'https://localhost/payment/information',
			]);
		} catch (\RuntimeException $e) {
			$this->assertEquals("Class '\\VoiceTube\\TaiwanPaymentGateway\\{$provider}PaymentGateway' not found", $e->getMessage());
		}
	}
}
