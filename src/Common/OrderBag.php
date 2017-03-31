<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 31/03/2017
 * Time: 6:09 PM
 */

namespace VoiceTube\TaiwanPaymentGateway\Common;


abstract class OrderBag
{
	protected $hashKey;
	protected $hashIV;

	protected $merchantId;
	protected $version;

	protected $actionUrl;
	protected $returnUrl;
	protected $notifyUrl;
	protected $clientBackUrl;
	protected $paymentInfoUrl;

	protected $order = [];
	protected $parameters = [];
}