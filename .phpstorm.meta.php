<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 16/04/2017
 * Time: 1:11 AM
 */

	$STATIC_METHOD_TYPES = [
		\VoiceTube\TaiwanPaymentGateway\TaiwanPaymentGateway::create('') => [
			'' == '@',
			"SpGateway" instanceof \VoiceTube\TaiwanPaymentGateway\SpGatewayPaymentGateway,
			"AllPay" instanceof \VoiceTube\TaiwanPaymentGateway\AllPayPaymentGateway,
			"EcPay" instanceof \VoiceTube\TaiwanPaymentGateway\EcPayPaymentGateway,
		],
		\VoiceTube\TaiwanPaymentGateway\TaiwanPaymentResponse::create('') => [
			'' == '@',
			"SpGateway" instanceof \VoiceTube\TaiwanPaymentGateway\SpGatewayPaymentResponse,
			"AllPay" instanceof \VoiceTube\TaiwanPaymentGateway\AllPayPaymentResponse,
			"EcPay" instanceof \VoiceTube\TaiwanPaymentGateway\EcPayPaymentResponse,
		]
	];