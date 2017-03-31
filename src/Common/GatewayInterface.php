<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 31/03/2017
 * Time: 5:27 PM
 */

namespace VoiceTube\TaiwanPaymentGateway\Common;


interface GatewayInterface
{
	/**
	 * Get order array.
	 * @return array
	 */
	public function getOrder();

	/**
	 * Set a new order
	 * @param string $type
	 * @param string $merchant_order_no
	 * @param integer|float $amount
	 * @param string $item_describe
	 * @param string $order_comment
	 * @param string $respond_type
	 * @param int $timestamp
	 * @throws \InvalidArgumentException
	 * @return boolean
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
	 * @param bool $auto_submit
	 * @return string
	 */
	public function genForm($auto_submit = true);

	/**
	 * @return string
	 */
	public function genCheckValue();
}