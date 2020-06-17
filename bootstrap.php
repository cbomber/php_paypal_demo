<?php
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

require __DIR__  . '/composer/vendor/autoload.php';

// For test payments we want to enable the sandbox mode. If you want to put live
// payments through then this setting needs changing to `false`.
$enableSandbox = false;


// PayPal settings. Change these to your account details and the relevant URLs
// for your site.

if(!$enableSandbox){
    $paypalConfig = [
        'client_id' => '<production_client_id>',
        'client_secret' => '<production_client_secret>',
        'return_url' => "http://".$_SERVER["HTTP_HOST"]."/payments/execute_purchase?success=true",//&activator=$activator&PaymentTypeID=$paymenttypeid&PaymentAmount=$price",
        'cancel_url' => "http://".$_SERVER["HTTP_HOST"]."/payments/execute_purchase?success=false"
    ];
} else {
    $paypalConfig = [
        'client_id' => '<sandbox_client_id>',
        'client_secret' => '<sandbox_client_secret>',
        'return_url' => "http://".$_SERVER["HTTP_HOST"]."/payments/execute_purchase?success=true",//&activator=$activator&PaymentTypeID=$paymenttypeid&PaymentAmount=$price",
        'cancel_url' => "http://".$_SERVER["HTTP_HOST"]."/payments/execute_purchase?success=false"
    ];
}



$apiContext = getApiContext($paypalConfig['client_id'], $paypalConfig['client_secret'], $enableSandbox);

/**
 * Set up a connection to the API
 *
 * @param string $clientId
 * @param string $clientSecret
 * @param bool   $enableSandbox Sandbox mode toggle, true for test payments
 * @return \PayPal\Rest\ApiContext
 */
function getApiContext($clientId, $clientSecret, $enableSandbox = false)
{
    $apiContext = new ApiContext(
        new OAuthTokenCredential($clientId, $clientSecret)
    );

    $apiContext->setConfig([
        'mode' => $enableSandbox ? 'sandbox' : 'live'
    ]);

    return $apiContext;
}
