<?php
require __DIR__ . '/vendor/autoload.php';
require_once './PaypalService.php';
 
/*
apprendresf-facilitator@gmail.com

Client ID :
AUAijNcfcF_yay6h6pbudb2Pb3dKaPE4btxHwxQlXQY9FuzcjOa8t4ftA0uRot7gK2i4IHUdRI1WUCvy

Secret:
EFgMfW6wWABh-WHDTztRcwJGGaq_DCHjVzSQ4dSibWhYvVxmihMT_SEdHvZpC7fRm9BQOmAsBWYTzpKK


pprendresf-facilitator@gmail.com    BUSINESS
apprendresf-buyer@gmail.com	        PERSONAL	

https://www.sandbox.paypal.com/us/home
*/

$paypal = new PaypalService();
$orderResult = $paypal->createOrder();


print_r($orderResult );
//$paypal->checkOrder($orderID);
