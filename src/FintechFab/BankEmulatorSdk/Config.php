<?php

namespace FintechFab\BankEmulatorSdk;


class Config
{

	/**
	 * @var array
	 */
	private static $configParams = array(
		'terminalId'    => '',
		'secretKey'     => '',
		'gatewayUrl'    => '',
		'endpointUrl'   => '',
		'callbackEmail' => '',
		'shopUrl'       => '',
		'callbackUrl'   => '',
		'currency'      => '',
		'strongSSL'     => true,
	);


	/**
	 * Set config parameters
	 *
	 * @param array $config
	 */
	public static function setAll($config)
	{
		foreach ($config as $key => $value) {
			self::set($key, $value);
		}

	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @throws GatewayException
	 */
	public static function set($name, $value)
	{

		if (!isset(self::$configParams[$name])) {
			throw new GatewayException('User undefined config param [' . $name . ']');
		}

		self::$configParams[$name] = $value;

	}

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public static function get($name)
	{
		return isset(self::$configParams[$name])
			? self::$configParams[$name]
			: null;
	}

	/**
	 * @return array
	 */
	public static function getAll()
	{
		return self::$configParams;
	}

} 