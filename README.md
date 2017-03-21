Taiwan Payment Gateway
=========================

#### A payment gateway library for Taiwan.

Created by [VoiceTube](https://www.voicetube.com/)

Features
--------

* PSR-4 autoloading compliant structure
* Easy to use to any framework or even a plain php file

Todo
----

* Unit-Testing with PHPUnit

Available Gateway
-----------------

* [智付通 Spgateway](https://www.spgateway.com)

Available soon
--------------

* [綠界 ECPay](https://www.ecpay.com.tw)
* [歐付寶 allPay](https://www.allpay.com.tw/)


How to use
----------

```php
require_once 'vendor/autoload.php';

use VoiceTube\TaiwanPaymentGateway;

$sp = TaiwanPaymentGateway\PaymentGateway::factory('SpGateway', [
    'hashKey'       => 'XXXX',
    'hashIV'        => 'XXXXX',
    'merchantId'    => 'XXX',
    'version'       => '1.2',
    'actionUrl'     => 'https://ccore.spgateway.com/MPG/mpg_gateway',
    'returnUrl'     => 'https://localhost/payment/confirm',
    'notifyUrl'     => '',
    'clientBackUrl' => 'https://localhost/payment/return',
    'paymentInfoUrl'=> 'https://localhost/payment/information',
]);
```
