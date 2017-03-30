<?php

namespace VoiceTube\TaiwanPaymentGateway;

use Illuminate\Support\Facades\Facade;

class PaymentGatewayFacade extends Facade{
	protected static function getFacadeAccessor() { return 'PaymentGateway'; }
}
