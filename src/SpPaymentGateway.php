<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 31/03/2017
 * Time: 6:04 PM
 */

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\SpGateway;

class SpPaymentGateway
{

	protected $instance;

	public $gateway;
	public $hashing;
	public $response;

	function __construct(array $config = [])
	{

		$this->gateway = new SpGateway\SpGatewayGateway();
		$this->response = new SpGateway\SpGatewayResponse();

		if (!empty($config)) {
			$this->gateway->setArrayConfig($config);
		}

		$this->instance = $this;

		return $this->instance;
	}

}