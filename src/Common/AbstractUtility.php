<?php

namespace VoiceTube\TaiwanPaymentGateway\Common;


abstract class AbstractUtility extends OrderBag
{

	protected $dot_net_url_encode_mapping;

	public function setArrayConfig($config)
	{
		foreach ($config as $key => $value) {
			$this->setConfig($key, $value);
		}

		$this->dot_net_url_encode_mapping = [
			'%2D' => '-',
			'%5F' => '_',
			'%2E' => '.',
			'%21' => '!',
			'%2A' => '*',
			'%2d' => '-',
			'%5f' => '_',
			'%2e' => '.',
			'%2a' => '*',
			'%28' => '(',
			'%29' => ')',
			'%20' => '+'];
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