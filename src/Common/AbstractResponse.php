<?php

namespace VoiceTube\TaiwanPaymentGateway\Common;


abstract class AbstractResponse extends AbstractUtility
{
	function __construct(array $config = [])
	{
		if (!empty($config)) {
			$this->setArrayConfig($config);
		}
	}
}