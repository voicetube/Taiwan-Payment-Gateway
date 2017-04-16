<?php

namespace VoiceTube\TaiwanPaymentGateway\Common;

/**
 * Interface GatewayInterface
 * @package VoiceTube\TaiwanPaymentGateway\Common
 *
 * @see AbstractGateway
 *
 */
interface GatewayInterface
{
    /**
     * Get order array.
     * @return array
     */
    public function getOrder();

    /**
     * Set a new order
     * @param string $merchant_order_no
     * @param integer|float $amount
     * @param string $item_describe
     * @param string $order_comment
     * @param string $respond_type
     * @param int $timestamp
     * @throws \InvalidArgumentException
     * @return GatewayInterface
     */
    public function newOrder(
        $merchant_order_no,
        $amount,
        $item_describe,
        $order_comment,
        $respond_type,
        $timestamp = 0
    );

    /**
     * @return GatewayInterface
     */
    public function setUnionPay();

    /**
     * @param integer|string $expire_Date
     * @return GatewayInterface
     */
    public function setOrderExpire($expire_Date);

    /**
     * @param integer|string $months
     * @param integer $total_amount
     * @return GatewayInterface
     */
    public function setCreditInstallment($months, $total_amount = 0);

    /**
     * @param bool $auto_submit
     * @return string
     */
    public function genForm($auto_submit = true);

    /**
     * @return string
     */
    public function genCheckValue();
}
