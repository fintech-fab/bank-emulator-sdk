<?php

namespace FintechFab\BankEmulatorSdk\Test;


use FintechFab\BankEmulatorSdk\Gateway;


/**
 *
 * if your wont run this tests,
 * set your configuration into config property
 * and comment 'markTestSkipped' into setUp
 *
 * Class GatewayTest
 *
 * @package FintechFab\BankEmulatorSdk\Test
 */
class GatewayTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var Gateway
	 */
	private $gateway = null;
	private $config = array(
		'terminalId'    => '1',
		'secretKey'     => 'de9cba5a3d6431edd3f839994962fa7d',
		'gatewayUrl'    => 'http://fintech-fab.dev/bank/emulator/demo/gateway',
		'callbackEmail' => '',
		'shopUrl'       => 'http://fintech-fab.dev/bank/emulator/demo/shop',
		'callbackUrl'   => 'http://fintech-fab.dev/bank/emulator/demo/callback',
		'currency'      => 'RUB',
		'strongSSL'     => false,
	);

	public function setUp()
	{
		parent::setUp();
		$this->markTestSkipped();
	}

	public function testAuth()
	{
		// auth

		$this->makeGateway();
		$params = array(
			'orderId'      => mt_rand(1, 9999999),
			'orderName'    => 'Order from PhpUnit',
			'orderDesc'    => '',
			'orderAmount'  => sprintf("%01.2f", mt_rand(100, 99999) / 100),
			'cardNumber'   => '4532274626451231',
			'expiredYear'  => '15',
			'expiredMonth' => '12',
			'cvcCode'      => '333',
		);
		$result = $this->gateway->auth($params);
		$this->assertTrue($result, $this->gateway->getError());

		$this->assertEquals('00', $this->gateway->getResultRC());
		$this->assertEquals('success', $this->gateway->getResultStatus());

		// complete

		$params = array(
			'orderId'     => $this->gateway->getResultOrderId(),
			'orderAmount' => $this->gateway->getResultAmount(),
			'rrn'         => $this->gateway->getResultRRN(),
			'irn'         => $this->gateway->getResultIRN(),
		);
		$this->makeGateway();
		$result = $this->gateway->complete($params);
		$this->assertTrue($result, $this->gateway->getError());

		$this->assertEquals('00', $this->gateway->getResultRC());
		$this->assertEquals('success', $this->gateway->getResultStatus());

		// refund

		$params = array(
			'orderId'     => $this->gateway->getResultOrderId(),
			'orderAmount' => $this->gateway->getResultAmount(),
			'rrn'         => $this->gateway->getResultRRN(),
			'irn'         => $this->gateway->getResultIRN(),
		);
		$this->makeGateway();
		$result = $this->gateway->refund($params);
		$this->assertTrue($result, $this->gateway->getError());

		$this->assertEquals('00', $this->gateway->getResultRC());
		$this->assertEquals('success', $this->gateway->getResultStatus());

	}

	public function testSale()
	{
		// sale

		$this->makeGateway();
		$params = array(
			'orderId'      => mt_rand(1, 9999999),
			'orderName'    => 'Order from PhpUnit',
			'orderDesc'    => '',
			'orderAmount'  => sprintf("%01.2f", mt_rand(100, 99999) / 100),
			'cardNumber'   => '4532274626451231',
			'expiredYear'  => '15',
			'expiredMonth' => '12',
			'cvcCode'      => '333',
		);
		$result = $this->gateway->sale($params);
		$this->assertTrue($result, $this->gateway->getError());

		$this->assertEquals('00', $this->gateway->getResultRC());
		$this->assertEquals('success', $this->gateway->getResultStatus());

	}

	public function testEndpoint()
	{
		// endpoint

		$this->makeGateway();
		$params = array(
			'orderId'      => mt_rand(1, 9999999),
			'orderName'    => 'Order from PhpUnit',
			'orderDesc'    => '',
			'orderAmount'  => sprintf("%01.2f", mt_rand(100, 99999) / 100),
		);
		$result = $this->gateway->endpoint($params);
		$this->assertTrue($result && is_array($result), $this->gateway->getError());

		$this->assertEquals($params['orderId'], $result['order']);
		$this->assertEquals($this->config['terminalId'], $result['term']);
		$this->assertNotEmpty($result['sign']);

	}

	private function makeGateway()
	{
		$this->gateway = Gateway::newInstance($this->config);
	}


}


