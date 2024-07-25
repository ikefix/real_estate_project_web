<?php

// PayPal
require_once "vendor/autoload.php"; 
use Omnipay\Omnipay;
 
define('CLIENT_ID', 'AQ1t62r6iJPC-4rJ-aWSusTpUU3uACbfRaerHRlefMHIPM-q72QP0EYvOFlNVK-0hQiKaeI9WY0nCf9y');
define('CLIENT_SECRET', 'EFoXxAKa98L37U7AwsYHt7iYDuOY8fXtUgifWn00eASETrJeo2nachAGWtRZbIlBv-DxMymGpnksWUY6');
 
define('PAYPAL_RETURN_URL', BASE_URL.'agent-paypal-success.php');
define('PAYPAL_CANCEL_URL', BASE_URL.'agent-paypal-cancel.php');
define('PAYPAL_CURRENCY', 'USD'); // set your currency here
 
$gateway = Omnipay::create('PayPal_Rest');
$gateway->setClientId(CLIENT_ID);
$gateway->setSecret(CLIENT_SECRET);
$gateway->setTestMode(true); //set it to 'false' when go live