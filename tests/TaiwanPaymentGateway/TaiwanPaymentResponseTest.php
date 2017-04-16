<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 16/04/2017
 * Time: 4:04 AM
 */

namespace TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\TaiwanPaymentResponse;

class TaiwanPaymentResponseTest extends \PHPUnit_Framework_TestCase
{
	protected $gwr;

	public function testFactory()
	{
		$this->gwr = TaiwanPaymentResponse::create('SpGateway', [
			'hashKey'       => 'c7fe1bfba42369ec1add502c9917e14d',
			'hashIV'        => '245a49c8fb5151f0',
			'merchantId'    => 'MS1234567',
		]);

		$this->assertEquals('VoiceTube\TaiwanPaymentGateway\SpGatewayPaymentResponse', get_class($this->gwr));
	}

	public function testFactoryWrongProvider()
	{
		try {
			$provider = 'SpGatewa';

			$this->gw = TaiwanPaymentResponse::create($provider, [
				'hashKey'       => 'c7fe1bfba42369ec1add502c9917e14d',
				'hashIV'        => '245a49c8fb5151f0',
				'merchantId'    => 'MS1234567',
			]);
		} catch (\RuntimeException $e) {
			$this->assertEquals("Class '\\VoiceTube\\TaiwanPaymentGateway\\{$provider}PaymentResponse' not found", $e->getMessage());
		}
	}
}
