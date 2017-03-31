<?php

namespace VoiceTube\TaiwanPaymentGateway\Common;


abstract class AbstractGateway extends AbstractUtility
{

	function __construct(array $config = [])
	{
		if (!empty($config)) {
			$this->setArrayConfig($config);
		}
	}

	public function clearOrder()
	{
		$this->order = [];
	}
}