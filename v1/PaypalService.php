<?php
//https://github.com/paypal/Checkout-PHP-SDK
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;


class PaypalService{
	
    public static $clientId = "AUAijNcfcF_yay6h6pbudb2Pb3dKaPE4btxHwxQlXQY9FuzcjOa8t4ftA0uRot7gK2i4IHUdRI1WUCvy";
    public static $clientSecret = "EDRjZzfKqEz_xWpymLit9ThgPK03NVLpdLqKOuKSg-0xPExW_Ct6YMGU2cINnZdjAnIw8-hQ6YHfOy6y";
	
	private $environment ;
	private  $client; 
	
	function __construct() {
       $this->environment = new SandBoxEnvironment(self::$clientId, self::$clientSecret);
       $this->client = new PayPalHttpClient($this->environment);
    }
	
	/**
	  Return Order ID
	*/
	public function createOrder(){
		$request = new OrdersCreateRequest();
		$request->prefer('return=representation');
		$request->body = [
							 "intent" => "CAPTURE",
							 "purchase_units" => [[
								 "reference_id" => "test_ref_id1",
								 "amount" => [
									 "value" => "100.00",
									 "currency_code" => "USD"
								 ]
							 ]],
							 "application_context" => [
								  "cancel_url" => "http://tunisie-annonce.com/?status=cancel",
								  "return_url" => "http://tunisie-annonce.com/?status=return"
							 ] 
						 ];

		try {
			// Call API with your client and get a response for your call
			$response = $this->client->execute($request);
			print_r($response );
			$url = null ;
			foreach( $response->result->links as $link){
				if( trim($link->rel) == "approve"){
					$url = $link->href ; 
					break;
				}
			}
			// If call returns body in response, you can get the deserialized version from the result attribute of the response
			//print_r($response);
			
			return array( 'idOrder' => $response->result->id , 'url'=> $url );
		}catch (\Exception $ex) {
			echo "=> Erreur step 1 : code=".$ex->getCode();
			print_r($ex->getMessage());
		}	
	}
	
	
	public function checkOrder($idOrder){
		// Here, OrdersCaptureRequest() creates a POST request to /v2/checkout/orders
		// $response->result->id gives the orderId of the order created above
		$request = new OrdersCaptureRequest($idOrder);
		$request->prefer('return=representation');
		try {
			// Call API with your client and get a response for your call
			$response = $this->client->execute($request);
			
			// If call returns body in response, you can get the deserialized version from the result attribute of the response
			print_r($response);
		}catch (\Exception $ex) {
			echo "=> Erreur step 1 : code=".$ex->getCode();
			print_r($ex->getMessage());
		}
	}
	
}