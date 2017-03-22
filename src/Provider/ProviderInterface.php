<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 20/03/2017
 * Time: 4:46 PM
 */

namespace VoiceTube\TaiwanPaymentGateway\Provider;


interface ProviderInterface
{
    function __construct();

    public function getOrder();
    public function newOrder(
        $type = PG_PAY_METHOD_CREDIT,
        $respond_type,
        $merchant_order_no,
        $amount,
        $item_describe,
        $email,
        $order_comment,
        $timestamp = 0
    );

    public function processOrder($type = 'JSON');

    public function setUnionPay();
    public function setOrderExpire($expire_Date);
    public function setCreditInstallment($months, $total_amount = 0);

    public function needExtraPaidInfo();

    public function matchCheckCode($payload);
    public function genCheckValue();

    public function genForm();
}