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
* [綠界 ECPay](https://www.ecpay.com.tw)
* [歐付寶 allPay](https://www.allpay.com.tw/)


How to use
----------

##### Usable providers

* `TaiwanPaymentGateway\PaymentGateway::SpGateway`
* `TaiwanPaymentGateway\PaymentGateway::AllPay`
* `TaiwanPaymentGateway\PaymentGateway::EcPay`

#### Accept payment method.

* WebATM  : `PG_PAY_METHOD_WEB_ATM`
* BarCode : `PG_PAY_METHOD_BARCODE`
* TenPay  : `PG_PAY_METHOD_TENPAY` (AllPay only)
* TopUp   : `PG_PAY_METHOD_TOPUP` (AllPay only)
* Credit  : `PG_PAY_METHOD_CREDIT`
* ATM : `PG_PAY_METHOD_ATM`
* CVS : `PG_PAY_METHOD_CVS`
* ALL : `PG_PAY_METHOD_ALL` (AllPay, EcPay only)

#### Load the library

```php
require_once 'vendor/autoload.php';

use VoiceTube\TaiwanPaymentGateway;
```

#### Initial gateway using factory

```php
$gw = TaiwanPaymentGateway\PaymentGateway::factory('SpGateway', [
    'hashKey'       => 'c7fe1bfba42369ec1add502c9917e14d',
    'hashIV'        => '245a49c8fb5151f0',
    'merchantId'    => 'MS1234567',
    'version'       => '1.2',
    'actionUrl'     => 'https://ccore.spgateway.com/MPG/mpg_gateway',
    'returnUrl'     => 'https://localhost/payment/confirm',
    'notifyUrl'     => 'https://localhost/payment/notify',
    'clientBackUrl' => 'https://localhost/payment/return',
    'paymentInfoUrl'=> 'https://localhost/payment/information',
]);
```

#### or Initial from specify gateway

```php
$gw = TaiwanPaymentGateway\PaymentGateway::SpGateway([
    'hashKey'       => 'c7fe1bfba42369ec1add502c9917e14d',
    'hashIV'        => '245a49c8fb5151f0',
    'merchantId'    => 'MS1234567',
    'version'       => '1.2',
    'actionUrl'     => 'https://ccore.spgateway.com/MPG/mpg_gateway',
    'returnUrl'     => 'https://localhost/payment/confirm',
    'notifyUrl'     => 'https://localhost/payment/notify',
    'clientBackUrl' => 'https://localhost/payment/return',
    'paymentInfoUrl'=> 'https://localhost/payment/information',
]);

$ec = TaiwanPaymentGateway\PaymentGateway::EcPay([
    'hashKey'       => '5294y06JbISpM5x9',
    'hashIV'        => 'v77hoKGq4kWxNNIS',
    'merchantId'    => '2000132',
    'version'       => 'V4',
    'actionUrl'     => 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/',
    'returnUrl'     => 'https://localhost/payment/confirm',
    'clientBackUrl' => 'https://localhost/payment/return',
    'paymentInfoUrl'=> 'https://localhost/payment/information',
]);

$ap = TaiwanPaymentGateway\PaymentGateway::AllPay([
    'hashKey'       => '5294y06JbISpM5x9',
    'hashIV'        => 'v77hoKGq4kWxNNIS',
    'merchantId'    => '2000132',
    'version'       => 'V2',
    'actionUrl'     => 'https://payment-stage.allpay.com.tw/Cashier/AioCheckOut/',
    'returnUrl'     => 'https://localhost/payment/confirm',
    'clientBackUrl' => 'https://localhost/payment/return',
    'paymentInfoUrl'=> 'https://localhost/payment/information',
]);
```

#### Create new order

```php
$gw->newOrder(
    $type = PG_PAY_METHOD_CREDIT,
    $respond_type,
    $merchant_order_no,
    $amount,
    $item_describe,
    $email,
    $order_comment,
    $timestamp = 0
);
```
