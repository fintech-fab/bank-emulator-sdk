<?php

namespace FintechFab\BankEmulatorSdk;


class OnlineFormWidget
{

	/**
	 *
	 * Render full form html
	 *
	 * @param integer $orderId
	 * @param float   $orderAmount
	 * @param string  $orderName
	 * @param string  $orderDesc
	 */
	public static function render($orderId, $orderAmount, $orderName = null, $orderDesc = null)
	{
		self::open();
		self::fields($orderId, $orderAmount, $orderName, $orderDesc);
		self::submit('оплатить');
		self::close();
	}

	/**
	 * Render tag form
	 */
	public static function open()
	{
		echo '<form
			action="' . Config::get('endpointUrl') . '"
			method="POST">
		';
	}

	/**
	 * Render hidden input fields
	 *
	 * @param integer $orderId
	 * @param float   $orderAmount
	 * @param string  $orderName
	 * @param string  $orderDesc
	 */
	public static function fields($orderId, $orderAmount, $orderName = null, $orderDesc = null)
	{

		$fields = self::gateway()->endpoint(array(
			'orderId'     => $orderId,
			'orderAmount' => $orderAmount,
			'orderName'   => $orderName,
			'orderDesc'   => $orderDesc,
		));

		foreach ($fields as $key => $val) {
			echo '<input
				type="hidden"
				name="' . htmlentities($key) . '"
				value="' . htmlentities($val) . '">
			';
		}

	}

	/**
	 * Render submit button
	 *
	 * @param string $value
	 */
	public static function submit($value)
	{
		echo '<button type="submit">' . htmlentities($value) . '</button>';
	}

	/**
	 * Render close form tag
	 */
	public static function close()
	{
		echo '</form>';
	}

	/**
	 * Init gateway sdk
	 *
	 * @return Gateway
	 */
	public static function gateway()
	{
		static $gateway = null;
		if ($gateway === null) {
			$gateway = new Gateway();
		}

		return $gateway;
	}

} 