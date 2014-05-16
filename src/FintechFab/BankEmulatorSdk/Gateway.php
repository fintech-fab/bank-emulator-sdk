<?php
namespace FintechFab\BankEmulatorSdk;

use Exception;
use stdClass;

class Gateway
{

	const C_ERROR_HTTP = 'http';
	const C_ERROR_GATEWAY = 'gateway';
	const C_ERROR_PROCESSING = 'processing';
	const C_ERROR_PARAMS = 'parameters';


	private $error = array();
	private $response;
	private $rawResponse;


	private $configParams = array(
		'terminalId'    => '',
		'secretKey'     => '',
		'gatewayUrl'    => '',
		'callbackEmail' => '',
		'shopUrl'       => '',
		'callbackUrl'   => '',
		'currency'      => '',
		'strongSSL'     => true,
	);

	private static $customParams = array(
		'auth'     => array(
			'orderId',
			'orderName',
			'orderDesc',
			'orderAmount',
			'cardNumber',
			'expiredYear',
			'expiredMonth',
			'cvcCode',
		),
		'sale'     => array(
			'orderId',
			'orderName',
			'orderDesc',
			'orderAmount',
			'cardNumber',
			'expiredYear',
			'expiredMonth',
			'cvcCode',
			'paymentTo',
		),
		'refund'   => array(
			'orderId',
			'orderAmount',
			'rrn',
			'irn',
		),
		'complete' => array(
			'orderId',
			'orderAmount',
			'rrn',
			'irn',
		),
		'endpoint' => array(
			'orderId',
			'orderName',
			'orderDesc',
			'orderAmount',
		),
		'payment'     => array(),
	);

	private static $convertRequestParams = array(
		'terminalId'    => 'term',
		'callbackEmail' => 'email',
		'shopUrl'       => 'url',
		'callbackUrl'   => 'back',
		'currency'      => 'cur',
		'orderId'       => 'order',
		'orderName'     => 'name',
		'orderDesc'     => 'desc',
		'orderAmount'   => 'amount',
		'cardNumber'    => 'pan',
		'expiredYear'   => 'year',
		'expiredMonth'  => 'month',
		'cvcCode'       => 'cvc',
		'rrn'           => 'rrn',
		'irn'           => 'irn',
		'sign'          => 'sign',
	);


	/**
	 * Authorization payment
	 *
	 * @param array $params
	 *
	 * @return boolean
	 */
	public function auth($params)
	{
		$requestParams = $this->initRequestParams('auth', $params);
		$this->request('auth', $requestParams);

		return empty($this->error);
	}

	/**
	 * Sale payment
	 *
	 * @param array $params
	 *
	 * @return boolean
	 */
	public function sale($params)
	{
		$requestParams = $this->initRequestParams('sale', $params);
		$this->request('sale', $requestParams);

		return empty($this->error);
	}

	/**
	 * Complete authorization payment
	 *
	 * @param array $params
	 *
	 * @return boolean
	 */
	public function complete($params)
	{
		$requestParams = $this->initRequestParams('complete', $params);
		$this->request('complete', $requestParams);

		return empty($this->error);
	}

	/**
	 * Refund for complete or sale payments
	 *
	 * @param array $params
	 *
	 * @return boolean
	 */
	public function refund($params)
	{
		$requestParams = $this->initRequestParams('refund', $params);
		$this->request('refund', $requestParams);

		return empty($this->error);
	}

	/**
	 *
	 * Generate form fields for online web-form
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function endpoint($params)
	{
		$this->cleanup();

		$requestParams = $this->initRequestParams('endpoint', $params);

		return $requestParams;
	}

	/**
	 * Parse callback request
	 *
	 * @param array $params
	 *
	 * @return stdClass
	 * @throws GatewayException
	 */
	public function callback(array $params = null)
	{
		$this->cleanup();

		if(null === $params){
			if(!empty($_POST)){
				$params = $_POST;
			}
		}

		if (
			empty($params) ||
			empty($params['term']) ||
			empty($params['sign']) ||
			empty($params['type'])
		) {
			throw new GatewayException('Input params is failure');
		}

		$type = $params['type'];

		if(!isset(self::$customParams[$type])){
			throw new GatewayException('Undefined type value');
		}

		$sign = $this->sign($type, $params);

		if($sign !== $params['sign']){
			throw new GatewayException('Signature check fail');
		}

		$this->rawResponse = http_build_url($params);

		/** @var stdClass response */
		$this->response = (object)$params;

		if ($this->response->rc !== '00') {
			$this->error = array(
				'type'    => self::C_ERROR_PROCESSING,
				'message' => $this->response->message,
			);
		}

		return empty($this->error);

	}


	/**
	 * @return string|null
	 */
	public function getError()
	{
		return (!empty($this->error['message']))
			? $this->error['message']
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getErrorType()
	{
		return (!empty($this->error['type']))
			? $this->error['type']
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultOrderId()
	{
		return (!empty($this->response->order))
			? $this->response->order
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultTerminalId()
	{
		return (!empty($this->response->term))
			? $this->response->term
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultAmount()
	{
		return (!empty($this->response->amount))
			? $this->response->amount
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultIRN()
	{
		return (!empty($this->response->irn))
			? $this->response->irn
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultRRN()
	{
		return (!empty($this->response->rrn))
			? $this->response->rrn
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultCardNumber()
	{
		return (!empty($this->response->pan))
			? $this->response->pan
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultMessage()
	{
		return (!empty($this->response->message))
			? $this->response->message
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultRC()
	{
		//dd($this->response);
		return (!empty($this->response->rc))
			? $this->response->rc
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultStatus()
	{
		return (!empty($this->response->status))
			? $this->response->status
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultRaw()
	{
		return (!empty($this->rawResponse))
			? $this->rawResponse
			: null;
	}


	/**
	 * @param array $config
	 *
	 * @throws Exception
	 * @return Gateway
	 */
	public static function newInstance($config)
	{

		if (!function_exists('curl_init')) {
			throw new Exception('Curl required');
		}

		$gateway = new self();
		$gateway->setConfig($config);

		return $gateway;

	}

	/**
	 * Set config parameters
	 *
	 * @param $config
	 */
	private function setConfig($config)
	{
		foreach ($config as $key => $value) {
			$this->setConfigParam($key, $value);
		}

	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @throws GatewayException
	 */
	public function setConfigParam($name, $value)
	{

		if (!isset($this->configParams[$name])) {
			throw new GatewayException('User undefined config param [' . $name . ']');
		}

		$this->configParams[$name] = $value;

	}


	/**
	 * Generate request signature
	 *
	 * @param string $type
	 * @param array  $params
	 *
	 * @return string
	 */
	private function sign($type, &$params)
	{

		ksort($params);
		$str4sign = implode('|', $params);
		$sign = md5($str4sign . $type . $this->configParams['secretKey']);

		return $sign;

	}

	/**
	 * Generate params for http query
	 *
	 * @param string $type
	 * @param array  $params
	 *
	 * @return array
	 * @throws GatewayException
	 */
	private function initRequestParams($type, $params)
	{

		$list = self::$customParams;
		if (!isset($list[$type])) {
			throw new GatewayException('Undefined request type');
		}

		$list = $list[$type];
		$requestParams = array();

		foreach ($list as $key) {
			if (!empty($params[$key])) {
				$requestParams[$key] = trim($params[$key]);
			}
		}

		foreach ($this->configParams as $key => $value) {
			if(
				in_array($type, array('complete', 'refund')) &&
				in_array($key, array('callbackUrl', 'callbackEmail', 'shopUrl'))
			){
				continue;
			}
			$requestParams[$key] = $value;
		}

		$requestParams = $this->convert($requestParams);

		if($type !== 'callback'){
			$requestParams['time'] = time();
		}

		$requestParams['sign'] = $this->sign($type, $requestParams);

		return $requestParams;

	}

	/**
	 * Reverse gateway and human names
	 *
	 * @param array $requestParams
	 *
	 * @return array
	 */
	private function convert($requestParams)
	{

		$convertedParams = array();
		$convertList = self::$convertRequestParams;
		if (isset($convertList['term'])) {
			$convertList = array_flip($convertList);
		}

		foreach ($requestParams as $key => $value) {
			if(isset($convertList[$key])){
				$convertedParams[$convertList[$key]] = $value;
			}
		}

		return $convertedParams;

	}


	/**
	 * Executing http request
	 *
	 * @param string $type
	 * @param array  $requestParams
	 */
	private function request($type, $requestParams)
	{
		$this->cleanup();

		$curl = new Curl();
		$curl->setCheckCertificates($this->configParams['strongSSL']);
		$curl->post($this->configParams['gatewayUrl'], array(
			'type'  => $type,
			'input' => $requestParams,
		));

		$this->parseErrors($curl);

		if (!$this->error || $this->error['type'] == self::C_ERROR_PROCESSING) {
			$this->response = json_decode($curl->result);
		}

	}

	/**
	 * Parse error type
	 *
	 * @param Curl $curl
	 */
	private function parseErrors(Curl $curl)
	{

		$this->rawResponse = $curl->result;

		if ($curl->error) {
			$this->error = array(
				'type'    => self::C_ERROR_HTTP,
				'message' => 'Curl error: ' . $curl->error,
			);

			return;
		}

		if ($curl->code != '200') {
			$this->error = array(
				'type'    => self::C_ERROR_HTTP,
				'message' => 'Response code: ' . $curl->code,
			);

			return;
		}

		if (empty($curl->result)) {
			$this->error = array(
				'type'    => self::C_ERROR_GATEWAY,
				'message' => 'Empty Response',
			);

			return;
		}

		$response = @json_decode($curl->result);
		if (!$response) {
			$this->error = array(
				'type'    => self::C_ERROR_GATEWAY,
				'message' => 'Response is not json',
			);

			return;
		}

		if (empty($response->rc) && empty($response->exception)) {
			$this->error = array(
				'type'    => self::C_ERROR_GATEWAY,
				'message' => 'Unrecognized response format',
			);

			return;
		}

		if (!empty($response->exception)) {
			$this->error = array(
				'type'    => self::C_ERROR_PARAMS,
				'message' => $response->exception,
			);

			return;
		}

		if ($response->rc !== '00') {
			$this->error = array(
				'type'    => self::C_ERROR_PROCESSING,
				'message' => $response->message,
			);

			return;
		}

	}

	/**
	 * Clear request/response data
	 */
	private function cleanup()
	{
		$this->response = '';
		$this->rawResponse = '';
		$this->error = array();
	}

}