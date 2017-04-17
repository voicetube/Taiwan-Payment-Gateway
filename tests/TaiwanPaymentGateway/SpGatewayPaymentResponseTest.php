<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 16/04/2017
 * Time: 12:17 PM
 */

namespace TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\TaiwanPaymentResponse;
use VoiceTube\TaiwanPaymentGateway\SpGatewayPaymentResponse;

class SpGatewayPaymentResponseTest extends \PHPUnit_Framework_TestCase
{

	public function testConstruct()
	{
		$gwr = TaiwanPaymentResponse::create('SpGateway', [
			'hashKey'       => 'fyjEf9sLkim7RdDvGeZZfVcLef5jDyWT',
			'hashIV'        => '6fxmp07KjuRaHvFo',
			'merchantId'    => 'MS3606763',
			'version'       => '1.2',
		]);

		$this->assertInstanceOf(SpGatewayPaymentResponse::class, $gwr);
	}

	public function testProcessOrder()
	{
		$gwr = new SpGatewayPaymentResponse([
			'hashKey'       => 'fyjEf9sLkim7RdDvGeZZfVcLef5jDyWT',
			'hashIV'        => '6fxmp07KjuRaHvFo',
			'merchantId'    => 'MS3606763',
			'version'       => '1.2',
		]);
		$_POST = array(
			"JSONData" =>"{\"Status\":\"SUCCESS\",\"Message\":\"\\u6388\\u6b0a\\u6210\\u529f\",\"Result\":\"{\\\"MerchantID\\\":\\\"MS3606763\\\",\\\"Amt\\\":1200,\\\"TradeNo\\\":\\\"17032212162728251\\\",\\\"MerchantOrderNo\\\":\\\"170322VT0034651\\\",\\\"RespondType\\\":\\\"JSON\\\",\\\"CheckCode\\\":\\\"B79A8F7171CDA95468FF8B091458124E55CA687242BD2848511052F40394256A\\\",\\\"IP\\\":\\\"1.34.194.146\\\",\\\"EscrowBank\\\":\\\"KGI\\\",\\\"PaymentType\\\":\\\"CREDIT\\\",\\\"RespondCode\\\":\\\"00\\\",\\\"Auth\\\":\\\"930637\\\",\\\"Card6No\\\":\\\"400022\\\",\\\"Card4No\\\":\\\"1111\\\",\\\"Exp\\\":\\\"2203\\\",\\\"TokenUseStatus\\\":\\\"0\\\",\\\"InstFirst\\\":1200,\\\"InstEach\\\":0,\\\"Inst\\\":0,\\\"ECI\\\":\\\"\\\",\\\"PayTime\\\":\\\"2017-03-22 12:16:27\\\"}\"}"
		);

		$result = $gwr->processOrder();

		$this->assertEquals(true, $result['matched']);

	}

	public function testProcessOrderWrongJSON()
	{
		$gwr = new SpGatewayPaymentResponse([
			'hashKey'       => 'fyjEf9sLkim7RdDvGeZZfVcLef5jDyWT',
			'hashIV'        => '6fxmp07KjuRaHvFo',
			'merchantId'    => 'MS3606763',
			'version'       => '1.2',
		]);
		$_POST = array(
			"JSONDataWrong" =>"{\"Status\":\"SUCCESS\",\"Message\":\"\\u6388\\u6b0a\\u6210\\u529f\",\"Result\":\"{\\\"MerchantID\\\":\\\"MS3606763\\\",\\\"Amt\\\":1200,\\\"TradeNo\\\":\\\"17032212162728251\\\",\\\"MerchantOrderNo\\\":\\\"170322VT0034651\\\",\\\"RespondType\\\":\\\"JSON\\\",\\\"CheckCode\\\":\\\"B79A8F7171CDA95468FF8B091458124E55CA687242BD2848511052F40394256A\\\",\\\"IP\\\":\\\"1.34.194.146\\\",\\\"EscrowBank\\\":\\\"KGI\\\",\\\"PaymentType\\\":\\\"CREDIT\\\",\\\"RespondCode\\\":\\\"00\\\",\\\"Auth\\\":\\\"930637\\\",\\\"Card6No\\\":\\\"400022\\\",\\\"Card4No\\\":\\\"1111\\\",\\\"Exp\\\":\\\"2203\\\",\\\"TokenUseStatus\\\":\\\"0\\\",\\\"InstFirst\\\":1200,\\\"InstEach\\\":0,\\\"Inst\\\":0,\\\"ECI\\\":\\\"\\\",\\\"PayTime\\\":\\\"2017-03-22 12:16:27\\\"}\"}"
		);

		$result = $gwr->processOrder();

		$this->assertEquals(false, $result);

	}

	public function testProcessOrderFailedJSON()
	{
		$gwr = new SpGatewayPaymentResponse([
			'hashKey'       => 'fyjEf9sLkim7RdDvGeZZfVcLef5jDyWT',
			'hashIV'        => '6fxmp07KjuRaHvFo',
			'merchantId'    => 'MS3606763',
			'version'       => '1.2',
		]);
		$_POST = array(
			"JSONData" =>"{\"Status\":\"FAILED\",\"Message\":\"\\u6388\\u6b0a\\u6210\\u529f\",\"Result\":\"{\\\"MerchantID\\\":\\\"MS3606763\\\",\\\"Amt\\\":1200,\\\"TradeNo\\\":\\\"17032212162728251\\\",\\\"MerchantOrderNo\\\":\\\"170322VT0034651\\\",\\\"RespondType\\\":\\\"JSON\\\",\\\"CheckCode\\\":\\\"B79A8F7171CDA95468FF8B091458124E55CA687242BD2848511052F40394256A\\\",\\\"IP\\\":\\\"1.34.194.146\\\",\\\"EscrowBank\\\":\\\"KGI\\\",\\\"PaymentType\\\":\\\"CREDIT\\\",\\\"RespondCode\\\":\\\"00\\\",\\\"Auth\\\":\\\"930637\\\",\\\"Card6No\\\":\\\"400022\\\",\\\"Card4No\\\":\\\"1111\\\",\\\"Exp\\\":\\\"2203\\\",\\\"TokenUseStatus\\\":\\\"0\\\",\\\"InstFirst\\\":1200,\\\"InstEach\\\":0,\\\"Inst\\\":0,\\\"ECI\\\":\\\"\\\",\\\"PayTime\\\":\\\"2017-03-22 12:16:27\\\"}\"}"
		);

		$result = $gwr->processOrder();

		$this->assertEquals(false, $result);

	}

	public function testProcessOrderPOST()
	{
		$gwr = new SpGatewayPaymentResponse([
			'hashKey'       => 'fyjEf9sLkim7RdDvGeZZfVcLef5jDyWT',
			'hashIV'        => '6fxmp07KjuRaHvFo',
			'merchantId'    => 'MS3606763',
			'version'       => '1.2',
		]);
		$_POST = array(
			'Status'          => 'SUCCESS',
			'Message'         => '授權成功',
			'MerchantID'      => 'MS3606763',
			'Amt'             => '1200',
			'TradeNo'         => '17032311330952317',
			'MerchantOrderNo' => '170323VT0034715',
			'RespondType'     => 'String',
			'CheckCode'       => '4B3DDA5FE88966928FEB903D6037B06A1A929087046E5E8D7A8CB2778A30D67C',
			'IP'              => '111.71.96.26',
			'EscrowBank'      => 'KGI',
			'PaymentType'     => 'CREDIT',
			'RespondCode'     => '00',
			'Auth'            => '930637',
			'Card6No'         => '400022',
			'Card4No'         => '1111',
			'Exp'             => '2204',
			'TokenUseStatus'  => '0',
			'InstFirst'       => '1200',
			'InstEach'        => '0',
			'Inst'            => '0',
			'ECI'             => '',
			'PayTime'         => '2017-03-23 11:33:09',
		);

		$result = $gwr->processOrder('POST');

		$this->assertEquals(true, $result['matched']);

	}

	public function testProcessOrderWrongPOST()
	{
		$gwr = new SpGatewayPaymentResponse([
			'hashKey'       => 'fyjEf9sLkim7RdDvGeZZfVcLef5jDyWT',
			'hashIV'        => '6fxmp07KjuRaHvFo',
			'merchantId'    => 'MS3606763',
			'version'       => '1.2',
		]);

		$_POST = array(
			'Status'          => 'FAILED',
		);

		$result = $gwr->processOrder('POST');

		$this->assertEquals(false, $result);

		$_POST = [];

		$result = $gwr->processOrder('POST');

		$this->assertEquals(false, $result);

		unset($_POST);

		$result = $gwr->processOrder('POST');

		$this->assertEquals(false, $result);

	}

}
