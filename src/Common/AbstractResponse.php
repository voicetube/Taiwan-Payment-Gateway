<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 31/03/2017
 * Time: 5:45 PM
 */

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