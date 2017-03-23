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
	/**
	 * Get order array.
	 * @return array
	 */
	public function getOrder();

	/**
	 * Set a new order
	 * @param string $type
	 * @param string $respond_type
	 * @param string $merchant_order_no
	 * @param integer|float $amount
	 * @param string $item_describe
	 * @param string $email
	 * @param string $order_comment
	 * @param int $timestamp
	 * @throws \InvalidArgumentException
	 * @return boolean
	 */
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

	/**
	 * Process the order information from gateway server
	 * @param string $type
	 * @return array|boolean
	 */
	public function processOrder($type = 'JSON');

    /**
	 * @return boolean
	 */
    public function setUnionPay();

    /**
     * @param integer|string $expire_Date
	 * @return boolean
	 */
    public function setOrderExpire($expire_Date);

    /**
     * @param integer|string $months
     * @param integer $total_amount
	 * @return boolean
	 */
    public function setCreditInstallment($months, $total_amount = 0);

	/**
	 * @return boolean
	 */
	public function needExtraPaidInfo();

	/**
	 * @param array $payload
	 * @return boolean
	 */
	public function matchCheckCode(array $payload = []);

	/**
	 * @return string
	 */
	public function genCheckValue();

	/**
	 * @param bool $auto_submit
	 * @return string
	 */
	public function genForm($auto_submit = true);

	/**
	 * @return boolean
	 */
	public function rspOk();

	/**
	 * @param string $msg
	 * @return boolean
	 */
	public function rspError($msg = '');
}