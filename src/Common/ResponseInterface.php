<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 31/03/2017
 * Time: 5:27 PM
 */

namespace VoiceTube\TaiwanPaymentGateway\Common;


interface ResponseInterface
{
	/**
	 * Process the order information from gateway server
	 * @param string $type
	 * @return array|boolean
	 */
	public function processOrder($type = 'JSON');

	/**
	 * @param array $payload
	 * @return boolean
	 */
	public function matchCheckCode(array $payload = []);
}