Taiwan Payment Gateway
=========================

#### A payment gateway library for Taiwan.

Created by [VoiceTube](https://www.voicetube.com/)

[![Build Status](https://travis-ci.org/merik-chen/Taiwan-Payment-Gateway.svg?branch=reflow-alpha)](https://travis-ci.org/merik-chen/Taiwan-Payment-Gateway)
[![Test Coverage](https://codeclimate.com/repos/58f2e68ab05ce9025a0023ca/badges/78424a612ed55896c86d/coverage.svg)](https://codeclimate.com/repos/58f2e68ab05ce9025a0023ca/coverage)

Features
--------

* Create / Process order
* PSR-4 autoloading compliant structure
* Easy to use to any framework or even a plain php file

Todo
----

* Unit-Testing with PHPUnit
* E-Invoice features

Available Gateway
-----------------

* [智付通 Spgateway](https://www.spgateway.com)
* [歐付寶 allPay](https://www.allpay.com.tw/)
* [綠界 ECPay](https://www.ecpay.com.tw)



How to use
----------

#### Accept payment method.

* WebATM
* BarCode
* TenPay (AllPay only)
* TopUp (AllPay only)
* Credit
* ATM
* CVS
* ALL (AllPay, EcPay only)

#### Load the library

```php
require_once 'vendor/autoload.php';

use VoiceTube\TaiwanPaymentGateway;
```

#### Initial the gateway

```php

/**
* Use factory to create gateway or directly new the gateway
*/

$gw = TaiwanPaymentGateway\TaiwanPaymentGateway::create('SpGateway', [
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


$sp = new TaiwanPaymentGateway\SpGatewayPaymentGateway([
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

$ec = new TaiwanPaymentGateway\EcPayPaymentGateway([
    'hashKey'       => '5294y06JbISpM5x9',
    'hashIV'        => 'v77hoKGq4kWxNNIS',
    'merchantId'    => '2000132',
    'version'       => 'V4',
    'actionUrl'     => 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/',
    'returnUrl'     => 'https://localhost/payment/confirm',
    'clientBackUrl' => 'https://localhost/payment/return',
    'paymentInfoUrl'=> 'https://localhost/payment/information',
]);

$ap = new TaiwanPaymentGateway\AllPayPaymentGateway([
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

#### New order
##### !! All order settings must called after create new order, or the `$gw->newOrder()` function will erase all previously order data.

```php
// create new order
// $respond_type:
//  SpGateway: `POST` or `JSON` (default to 'JSON')
//  AllPay, EcPay: `POST` only
$gw->newOrder(
    required:$merchant_order_no, 
    required:$amount, 
    required:$item_describe, 
    required:$order_comment, 
    optional:$respond_type, 
    optional:$timestamp
);

// Available payment method
$gw->useBarCode();
$gw->useWebATM();
$gw->useCredit();
$gw->useTenPay(); // AllPay only
$gw->useTopUp(); // AllPay only
$gw->useATM();
$gw->useCVS();
$gw->useALL(); // AllPay, EcPay only

// spgateway only settings
$gw->setEmail('bonjour@voicetube.com'); // setting user email
$gw->triggerEmailModify(optional:boolean); // allow user update email address later.
$gw->onlyLoginMemberCanPay(optional:boolean); // force user must to be login later.

// Order settings
$gw->setUnionPay();
$gw->needExtraPaidInfo();
$gw->setCreditInstallment(required:$months, optional:$total_amount);
$gw->setOrderExpire(required:$expire_Date);

// It can be using like this

$rId = sprintf("VT%s", time());
$gw->newOrder($rId, 100, rId, $rId)
   ->useCredit()
   ->setUnionPay()
   ->needExtraPaidInfo();

// generate post form
$gw->genForm(optional:$auto_submit = true);

```

#### Process order result/information

##### Initial the gateway

```php
/**
* Use factory to create response or directly new the response
*/

$gwr = TaiwanPaymentGateway\TaiwanPaymentResponse::create('SpGateway', [
    'hashKey'       => 'c7fe1bfba42369ec1add502c9917e14d',
    'hashIV'        => '245a49c8fb5151f0',
]);

$spr = new TaiwanPaymentGateway\SpGatewayPaymentResponse([
    'hashKey'       => 'c7fe1bfba42369ec1add502c9917e14d',
    'hashIV'        => '245a49c8fb5151f0',
]);

$ecr = new TaiwanPaymentGateway\EcPayPaymentResponse([
    'hashKey'       => '5294y06JbISpM5x9',
    'hashIV'        => 'v77hoKGq4kWxNNIS',
]);

$apr = new TaiwanPaymentGateway\AllPayPaymentResponse([
    'hashKey'       => '5294y06JbISpM5x9',
    'hashIV'        => 'v77hoKGq4kWxNNIS',
]);
```

##### Check the payment response

```php
// processOrder will catch $_POST fields and clean it
// SpGateway: `POST` or `JSON`
// AllPay, EcPay: `POST` only
$result = $spr->processOrder(optional:$type='POST or JSON');

// dump the result
array(22) {
  ["Status"]=>
  string(7) "SUCCESS"
  ["Message"]=>
  string(12) "授權成功"
  ["MerchantID"]=>
  string(9) "MS1234567"
  ["Amt"]=>
  int(1200)
  ["TradeNo"]=>
  string(17) "17032311330952317"
  ["MerchantOrderNo"]=>
  string(15) "1703230034715"
  ["PaymentType"]=>
  string(6) "CREDIT"
  ["RespondType"]=>
  string(6) "String"
  ["CheckCode"]=>
  string(64) "4B3DDA5FE88966928FEB903D6037B06A1A929087046E5E8D7A8CB2778A30D67C"
  ["PayTime"]=>
  string(19) "2017-03-23 11:33:09"
  ["IP"]=>
  string(12) "XXX.XXX.XXX.XXX"
  ["EscrowBank"]=>
  string(3) "KGI"
  ["TokenUseStatus"]=>
  int(0)
  ["RespondCode"]=>
  string(2) "00"
  ["Auth"]=>
  string(6) "930637"
  ["Card6No"]=>
  string(6) "400022"
  ["Card4No"]=>
  string(4) "1111"
  ["Inst"]=>
  int(0)
  ["InstFirst"]=>
  int(1200)
  ["InstEach"]=>
  int(0)
  ["ECI"]=>
  string(0) ""
  ["matched"]=>
  bool(true)
}

// after processing, check the `matched` field.

// for allpay and ecpay, you will need to call `rspOk` or `rspError`.
$ecr->rspOk();
// or
$apr->rspError(optinal:'Custom error msg');

```

#### Ref.

* [[PDF] SpGateway](https://www.spgateway.com/dw_files/info_api/spgateway_gateway_MPGapi_V1_0_3.pdf)
* [[PDF] AllPay](https://www.allpay.com.tw/Content/files/allpay_011.pdf)
* [[PDF] EcPay](https://www.ecpay.com.tw/Content/files/ecpay_011.pdf)