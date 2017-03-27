<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Provider;

class PaymentGateway
{

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

	/**
	 * 智付通 Spgateway
	 * https://www.spgateway.com/
	 *
	 * @param array $config
	 * @return Provider\SpGatewayProvider
	 */
	public static function SpGateway(array $config = [])
	{
		return new Provider\SpGatewayProvider($config);
    }

    /**
	 * 綠界 ECPay
	 * https://www.ecpay.com.tw
	 *
	 * @param array $config
	 * @return Provider\EcPayProvider
	 */
	public static function EcPay(array $config = [])
	{
		return new Provider\EcPayProvider($config);
    }

    /**
	 * 歐付寶 allPay
	 * https://www.allpay.com.tw/
	 *
	 * @param array $config
	 * @return Provider\AllPayProvider
	 */
	public static function AllPay(array $config = [])
	{
		return new Provider\AllPayProvider($config);
    }
}