<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 16/04/2017
 * Time: 12:14 AM
 */

namespace PHPSTORM_META {

    override(
        \VoiceTube\TaiwanPaymentGateway\TaiwanPaymentGateway::create(''),
        map(
            [
            '' == '@',
            "SpGateway" => \VoiceTube\TaiwanPaymentGateway\SpGatewayPaymentGateway::class,
            "AllPay" => \VoiceTube\TaiwanPaymentGateway\AllPayPaymentGateway::class,
            "EcPay" => \VoiceTube\TaiwanPaymentGateway\EcPayPaymentGateway::class,
            ]
        )
    );

    override(
        \VoiceTube\TaiwanPaymentGateway\TaiwanPaymentResponse::create(''),
        map(
            [
            '' == '@',
            "SpGateway" => \VoiceTube\TaiwanPaymentGateway\SpGatewayPaymentResponse::class,
            "AllPay" => \VoiceTube\TaiwanPaymentGateway\AllPayPaymentResponse::class,
            "EcPay" => \VoiceTube\TaiwanPaymentGateway\EcPayPaymentResponse::class,
            ]
        )
    );

}
