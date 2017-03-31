<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 31/03/2017
 * Time: 5:45 PM
 */

namespace VoiceTube\TaiwanPaymentGateway\Common;


abstract class AbstractGateway extends AbstractUtility
{

//	const PG_PAY_METHOD_BARCODE = 'BARCODE';
//	const PG_PAY_METHOD_WEB_ATM = 'WebATM';
//	const PG_PAY_METHOD_CREDIT = 'Credit';
//	const PG_PAY_METHOD_TENPAY = 'Tenpay';
//	const PG_PAY_METHOD_TOPUP = 'TopUpUsed';
//	const PG_PAY_METHOD_ATM = 'ATM';
//	const PG_PAY_METHOD_ALL = 'ALL';
//	const PG_PAY_METHOD_CVS = 'CVS';

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