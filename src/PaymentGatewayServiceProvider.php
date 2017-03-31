<?php

namespace VoiceTube\TaiwanPaymentGateway;

use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton('PaymentGateway', function ($app) {

        	$provider = getenv('PAYMENT_GATEWAY');
        	$provider = $provider === false ? 'SpGateway' : $provider;

        	switch ($provider) {
		        case 'AllPay':
			        return PaymentGateway::AllPay([
				        'hashKey'        => getenv('PAYMENT_HASH_KEY') ? getenv('PAYMENT_HASH_KEY') : '',
				        'hashIV'         => getenv('PAYMENT_HASH_IV') ? getenv('PAYMENT_HASH_IV') : '',
				        'merchantId'     => getenv('PAYMENT_MERCHANT_ID') ? getenv('PAYMENT_MERCHANT_ID') : '',
				        'version'        => getenv('PAYMENT_VERSION') ? getenv('PAYMENT_VERSION') : 'V4',
				        'actionUrl'      => getenv('PAYMENT_ACTION_URL') ? getenv('PAYMENT_ACTION_URL') : '',
				        'returnUrl'      => getenv('PAYMENT_RETURN_URL') ? getenv('PAYMENT_RETURN_URL') : '',
				        'notifyUrl'      => getenv('PAYMENT_NOTIFY_URL') ? getenv('PAYMENT_NOTIFY_URL') : '',
				        'clientBackUrl'  => getenv('PAYMENT_CLIENT_BACK_URL') ? getenv('PAYMENT_CLIENT_BACK_URL') : '',
				        'paymentInfoUrl' => getenv('PAYMENT_PAYMENT_INFO_URL') ? getenv('PAYMENT_PAYMENT_INFO_URL') : '',
			        ]);
			        break;
		        case 'EcPay':
		        	return PaymentGateway::AllPay([
				        'hashKey'        => getenv('PAYMENT_HASH_KEY') ? getenv('PAYMENT_HASH_KEY') : '',
				        'hashIV'         => getenv('PAYMENT_HASH_IV') ? getenv('PAYMENT_HASH_IV') : '',
				        'merchantId'     => getenv('PAYMENT_MERCHANT_ID') ? getenv('PAYMENT_MERCHANT_ID') : '',
				        'version'        => getenv('PAYMENT_VERSION') ? getenv('PAYMENT_VERSION') : 'V2',
				        'actionUrl'      => getenv('PAYMENT_ACTION_URL') ? getenv('PAYMENT_ACTION_URL') : '',
				        'returnUrl'      => getenv('PAYMENT_RETURN_URL') ? getenv('PAYMENT_RETURN_URL') : '',
				        'notifyUrl'      => getenv('PAYMENT_NOTIFY_URL') ? getenv('PAYMENT_NOTIFY_URL') : '',
				        'clientBackUrl'  => getenv('PAYMENT_CLIENT_BACK_URL') ? getenv('PAYMENT_CLIENT_BACK_URL') : '',
				        'paymentInfoUrl' => getenv('PAYMENT_PAYMENT_INFO_URL') ? getenv('PAYMENT_PAYMENT_INFO_URL') : '',
			        ]);
		        	break;
		        case 'SpGateway':
		        default:
		        	return PaymentGateway::AllPay([
				        'hashKey'        => getenv('PAYMENT_HASH_KEY') ? getenv('PAYMENT_HASH_KEY') : '',
				        'hashIV'         => getenv('PAYMENT_HASH_IV') ? getenv('PAYMENT_HASH_IV') : '',
				        'merchantId'     => getenv('PAYMENT_MERCHANT_ID') ? getenv('PAYMENT_MERCHANT_ID') : '',
				        'version'        => getenv('PAYMENT_VERSION') ? getenv('PAYMENT_VERSION') : '1.2',
				        'actionUrl'      => getenv('PAYMENT_ACTION_URL') ? getenv('PAYMENT_ACTION_URL') : '',
				        'returnUrl'      => getenv('PAYMENT_RETURN_URL') ? getenv('PAYMENT_RETURN_URL') : '',
				        'notifyUrl'      => getenv('PAYMENT_NOTIFY_URL') ? getenv('PAYMENT_NOTIFY_URL') : '',
				        'clientBackUrl'  => getenv('PAYMENT_CLIENT_BACK_URL') ? getenv('PAYMENT_CLIENT_BACK_URL') : '',
				        'paymentInfoUrl' => getenv('PAYMENT_PAYMENT_INFO_URL') ? getenv('PAYMENT_PAYMENT_INFO_URL') : '',
			        ]);
		        	break;
	        }

        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['paymentgateway'];
	}
}
