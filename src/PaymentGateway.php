<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Provider;

class PaymentGateway
{
    public function __construct($provider, $config)
    {
        return $this->factory($provider, $config);
    }

    /**
     * @param $provider
     * @param $config
     * @return Provider\ProviderInterface|Provider\Provider|boolean
     */
    public static function factory($provider, $config)
    {
        /**
         * @param $service Provider\ProviderInterface|Provider\Provider
         */
        $provider = "\\VoiceTube\\TaiwanPaymentGateway\\Provider\\{$provider}Provider";
        if (class_exists($provider)) return new $provider($config);
        trigger_error('Provider not exists.');
        return false;
    }
}