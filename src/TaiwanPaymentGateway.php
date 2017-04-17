<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 15/04/2017
 * Time: 11:28 PM
 */

namespace VoiceTube\TaiwanPaymentGateway;

class TaiwanPaymentGateway
{

    /**
     * Create a new gateway instance
     *
     * @param string $provider
     * @param array $config
     * @throws \RuntimeException If no such gateway is found
     * @return Common\GatewayInterface An object of class $provider is created and returned
     */
    public static function create($provider, array $config = [])
    {

        $provider = "\\VoiceTube\\TaiwanPaymentGateway\\{$provider}PaymentGateway";

        if (!class_exists($provider)) {
            throw new \RuntimeException("Class '$provider' not found");
        }

        return new $provider($config);
    }
}
