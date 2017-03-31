<?php

namespace VoiceTube\TaiwanPaymentGateway;

use Illuminate\Support\ServiceProvider;
use VoiceTube\TaiwanPaymentGateway\Provider\AllPayProvider;
use VoiceTube\TaiwanPaymentGateway\Provider\EcPayProvider;
use VoiceTube\TaiwanPaymentGateway\Provider\ProviderInterface;
use VoiceTube\TaiwanPaymentGateway\Provider\SpGatewayProvider;

class PaymentGatewayServiceProvider extends ServiceProvider
{

	protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
	    $this->app->bind(
	    	ProviderInterface::class,
	    	AllPayProvider::class
	    );

	    $this->app->bind(
		    ProviderInterface::class,
		    EcPayProvider::class
	    );

	    $this->app->bind(
		    ProviderInterface::class,
		    SpGatewayProvider::class
	    );
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [AllPayProvider::class, EcPayProvider::class, SpGatewayProvider::class];
	}
}
