<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 31/03/2017
 * Time: 6:51 PM
 */

namespace VoiceTube\TaiwanPaymentGateway\Common;


abstract class AbstractUtility extends OrderBag
{
	public function setArrayConfig($config)
	{
		foreach ($config as $key => $value) {
			$this->setConfig($key, $value);
		}
	}

	private function isExists($key)
	{
		return property_exists(self::class, $key);
	}

	public function setConfig($key, $value)
	{
		$key = trim($key);

		if ($this->isExists($key)) {
			$this->$key = $value;

			return $value;
		} else {
			trigger_error('config key not exists.');

			return false;
		}
	}

	public function getConfig($key)
	{
		$key = trim($key);

		if ($this->isExists($key)) return $this->$key;
		trigger_error('config key not exists.');

		return false;
	}
}