Bank Emulator SDK
===============

SDK for Bank Emulator (https://github.com/fintech-fab/bank-emulator)

# Requirements

- php >=5.3.0
- php5-curl

# Installation (composer)

    {
        "require": {
            "fintech-fab/bank-emulator-sdk": "dev-master",
        },
    }

# Simple usage

```PHP
use FintechFab\BankEmulatorSdk\Gateway;

$config = array(
	'terminalId'    => 'your-terminal-id',
	'secretKey'     => 'your-terminal-secret-key',
	'gatewayUrl'    => 'url-to-gateway',
	'callbackEmail' => '',
	'shopUrl'       => 'url-to-your-shop',
	'callbackUrl'   => 'url-to-your-shop-callback',
	'currency'      => 'RUB',
	'strongSSL'     => false,
);

// Start with payment 'auth'

$gatewayAuth = Gateway::newInstance($config);

$params = array(
	'orderId'      => '123456',
	'orderName'    => 'Order from My Example',
	'orderDesc'    => '',
	'orderAmount'  => '123.45',
	'cardNumber'   => '4532274626451231',
	'expiredYear'  => '15',
	'expiredMonth' => '12',
	'cvcCode'      => '333',
);

$successAuth = $gatewayAuth->auth($params);

if($successAuth){

	// processing your payment operation and 'complete' sale

	$gatewayComplete = Gateway::newInstance($config);
	$params = array(
		'orderId'     => $gatewayAuth->getResultOrderId(),
		'orderAmount' => $gatewayAuth->getResultAmount(),
		'rrn'         => $gatewayAuth->getResultRRN(),
		'irn'         => $gatewayAuth->getResultIRN(),
	);
	$successComplete = $gatewayComplete->complete($params);

	if($successComplete){

		// Cancellation process your payment 'refund'

		$gatewayRefund = Gateway::newInstance($config);
		$successRefund = $gatewayRefund->refund($params);
	}

}
```
