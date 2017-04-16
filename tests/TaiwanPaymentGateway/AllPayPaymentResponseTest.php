<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 16/04/2017
 * Time: 9:47 PM
 */

namespace TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\AllPayPaymentResponse;

class AllPayPaymentResponseTest extends \PHPUnit_Framework_TestCase
{
	protected $config = [
		'hashKey'       => '5294y06JbISpM5x9',
		'hashIV'        => 'v77hoKGq4kWxNNIS',
		'merchantId'    => '2000132',
		'version'       => 'V2',
	];

	public function testConstruct()
	{
		$gwr = new AllPayPaymentResponse($this->config);

		$this->assertInstanceOf(AllPayPaymentResponse::class, $gwr);
	}

	public function testProcessOrder()
	{
		$gwr = new AllPayPaymentResponse($this->config);

		$_POST = [
			'AlipayID' => '',
			'AlipayTradeNo' => '',
			'amount' => '100',
			'ATMAccBank' => '',
			'ATMAccNo' => '',
			'auth_code' => '777777',
			'card4no' => '2222',
			'card6no' => '431195',
			'eci' => '0',
			'ExecTimes' => '',
			'Frequency' => '',
			'gwsr' => '10541521',
			'MerchantID' => '2000132',
			'MerchantTradeNo' => 'VT1490265603',
			'PayFrom' => '',
			'PaymentDate' => '2017/03/23 18:41:19',
			'PaymentNo' => '',
			'PaymentType' => 'Credit_CreditCard',
			'PaymentTypeChargeFee' => '1',
			'PeriodAmount' => '',
			'PeriodType' => '',
			'process_date' => '2017/03/23 18:41:19',
			'red_dan' => '0',
			'red_de_amt' => '0',
			'red_ok_amt' => '0',
			'red_yet' => '0',
			'RtnCode' => '1',
			'RtnMsg' => '交易成功',
			'SimulatePaid' => '0',
			'staed' => '0',
			'stage' => '0',
			'stast' => '0',
			'TenpayTradeNo' => '',
			'TotalSuccessAmount' => '',
			'TotalSuccessTimes' => '',
			'TradeAmt' => '100',
			'TradeDate' => '2017/03/23 18:40:20',
			'TradeNo' => '1703231840205732',
			'WebATMAccBank' => '',
			'WebATMAccNo' => '',
			'WebATMBankName' => '',
			'CheckMacValue' => 'C65E91444C43934C9E363D9EBCD72BB5BA88600E6EB5DA4034BACF99A2663263',
		];

		$result = $gwr->processOrder();

		$this->assertEquals(true, $result['matched']);

		if ($result['matched']) {
			$gwr->rspOk();
		} else {
			$gwr->rspError();
		}

	}

	public function testProcessOrderWrongPOST()
	{
		$gwr = new AllPayPaymentResponse([
			'hashKey'       => 'fyjEf9sLkim7RdDvGeZZfVcLef5jDyWT',
			'hashIV'        => '6fxmp07KjuRaHvFo',
			'merchantId'    => 'MS3606763',
			'version'       => '1.2',
		]);

		$_POST = array(
			'RtnCode'          => '0',
		);

		$result = $gwr->processOrder('POST');

		$this->assertEquals(false, $result);

		$_POST = [];

		$result = $gwr->processOrder('POST');

		$this->assertEquals(false, $result);

		unset($_POST);

		$result = $gwr->processOrder('POST');

		$this->assertEquals(false, $result);

		if ($result['matched']) {
			$gwr->rspOk();
		} else {
			$gwr->rspError('Failed');
		}

	}
}
