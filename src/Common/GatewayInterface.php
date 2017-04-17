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
     * @param string $merchantOrderNo
     * @param integer|float $amount
     * @param string $itemDescribe
     * @param string $orderComment
     * @param string $respondType
     * @param int $timestamp
     * @throws \InvalidArgumentException
     * @return GatewayInterface
     */
    public function newOrder(
        $merchantOrderNo,
        $amount,
        $itemDescribe,
        $orderComment,
        $respondType,
        $timestamp = 0
    );

    /**
     * @return GatewayInterface
     */
    public function setUnionPay();

    /**
     * @param integer|string $expireDate
     * @return GatewayInterface
     */
    public function setOrderExpire($expireDate);

    /**
     * @param integer|string $months
     * @return GatewayInterface
     */
    public function setCreditInstallment($months);

    /**
     * @param bool $autoSubmit
     * @return string
     */
    public function genForm($autoSubmit);

    /**
     * @return string
     */
    public function genCheckValue();
}
