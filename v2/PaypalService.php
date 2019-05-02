<?php
//https://github.com/paypal/PayPal-PHP-SDK
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaypalService{
	
    public static $clientId = "AUAijNcfcF_yay6h6pbudb2Pb3dKaPE4btxHwxQlXQY9FuzcjOa8t4ftA0uRot7gK2i4IHUdRI1WUCvy";
    public static $clientSecret = "EDRjZzfKqEz_xWpymLit9ThgPK03NVLpdLqKOuKSg-0xPExW_Ct6YMGU2cINnZdjAnIw8-hQ6YHfOy6y";
	
	
	private static  $apiContext = null;
	public static $device = "USD" ;
	
	
	private static function createApiContext(){

		$apiContext = new ApiContext(
				new OAuthTokenCredential(
						self::$clientId ,
						self::$clientSecret
				)
		);
		
		$apiContext->setConfig(
				array(
						'mode' => 'sandbox',
						'log.LogEnabled' => true,
						'log.FileName' => './PayPal.log',
						'log.LogLevel' => 'DEBUG', // PLEASE USE `FINE` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
						'cache.enabled' => true,
						// 'http.CURLOPT_CONNECTTIMEOUT' => 30
						// 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
				)
				);
		return $apiContext;
	}
	
	
	/**
	 * Helper method for getting an APIContext for all calls
	 * @param string $clientId Client ID
	 * @param string $clientSecret Client Secret
	 * @return \PayPal\Rest\ApiContext
	 */
	public static function getApiContext(){
	
		if( self::$apiContext == null ){
			self::$apiContext = self::createApiContext();
		}
		
	
		return self::$apiContext ;

	}

	
	public static function createBillingUrl(){
		
		/** @var \Paypal\Rest\ApiContext $apiContext */
		$apiContext = self::getApiContext();
		

		// ### Payer
		// A resource representing a Payer that funds a payment
		// For paypal account payments, set payment method
		// to 'paypal'.
		$payer = new Payer();
		$payer->setPaymentMethod("paypal");
		 
		// ### Itemized information
		// (Optional) Lets you specify item wise
		// information
		$item1 = new Item();
		$item1->setName("Produit 1")
		->setCurrency(self::$device)
		->setQuantity(1)
		->setSku("PRODUCT_ID_1") // Similar to `item_number` in Classic API
		->setPrice(5);
		
		$item2 = new Item();
		$item2->setName("Produit 2")
		->setCurrency(self::$device)
		->setQuantity(1)
		->setSku("PRODUCT_ID_2") // Similar to `item_number` in Classic API
		->setPrice(15);
		$itemList = new ItemList();
		$itemList->setItems(array($item1 , $item2));

		// ### Amount
		$amount = new Amount();
		$amount->setCurrency(self::$device)
		->setTotal(20);
		//->setDetails($details);
		 
		// ### Transaction
		$transaction = new Transaction();
		$transaction->setAmount($amount)
		->setItemList($itemList)
		->setDescription("Payment description")
		->setInvoiceNumber(uniqid());
		 
		// ### Redirect urls
		$baseUrl = "http://boutique.freevar.com";
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl("$baseUrl?order=execute")
		->setCancelUrl("$baseUrl?confirmation=1&error=User_Cancelled_the_Approval");
		 
		
		$createProfileResponse = self::createWebProfile($apiContext);
		
		// ### Payment
		// A Payment Resource; create one using
		// the above types and intent set to 'sale'
		$payment = new Payment();
		$payment->setIntent("sale")
		->setPayer($payer)
		->setRedirectUrls($redirectUrls)
		->setTransactions(array($transaction))
		->setExperienceProfileId($createProfileResponse->getId());
		 
 
		 
		// For Sample Purposes Only.
		$request = clone $payment;
		 
		// ### Create Payment
		// Create a payment by calling the 'create' method
		try {
			$payment->create($apiContext);
		} catch (Exception $e) {
			// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
			//e("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);
			throw $e ;
		}
		 
		// ### Get redirect url
		// The API response provides the url that you must redirect
		// the buyer to. Retrieve the url from the $payment->getApprovalLink()
		// method
		return $payment->getApprovalLink();

	}
	
	
	public static function createWebProfile($apiContext){
		
		
		// Lets create an instance of FlowConfig and add
		// landing page type information
		$flowConfig = new \PayPal\Api\FlowConfig();
		// Type of PayPal page to be displayed when a user lands on the PayPal site for checkout. Allowed values: Billing or Login. When set to Billing, the Non-PayPal account landing page is used. When set to Login, the PayPal account login landing page is used.
		$flowConfig->setLandingPageType("Billing");
		// The URL on the merchant site for transferring to after a bank transfer payment.
		$flowConfig->setBankTxnPendingUrl("http://boutique.freevar.com/");
		
		// Parameters for style and presentation.
		$presentation = new \PayPal\Api\Presentation();
		
		// A URL to logo image. Allowed vaues: .gif, .jpg, or .png.
		$presentation->setLogoImage("http://boutique.freevar.com/logo.jpg")
		//	A label that overrides the business name in the PayPal account on the PayPal pages.
		    ->setBrandName("Boutique en ligne")
		//  Locale of pages displayed by PayPal payment experience.
		    ->setLocaleCode("FR");
		
		// Parameters for input fields customization.
		$inputFields = new \PayPal\Api\InputFields();
		// Enables the buyer to enter a note to the merchant on the PayPal page during checkout.
		$inputFields->setAllowNote(true)
		    // Determines whether or not PayPal displays shipping address fields on the experience pages. Allowed values: 0, 1, or 2. When set to 0, PayPal displays the shipping address on the PayPal pages. When set to 1, PayPal does not display shipping address fields whatsoever. When set to 2, if you do not pass the shipping address, PayPal obtains it from the buyer’s account profile. For digital goods, this field is required, and you must set it to 1.
		    ->setNoShipping(1)
		    // Determines whether or not the PayPal pages should display the shipping address and not the shipping address on file with PayPal for this buyer. Displaying the PayPal street address on file does not allow the buyer to edit that address. Allowed values: 0 or 1. When set to 0, the PayPal pages should not display the shipping address. When set to 1, the PayPal pages should display the shipping address.
		    ->setAddressOverride(0);
		
		// #### Payment Web experience profile resource
		$webProfile = new \PayPal\Api\WebProfile();
		
		// Name of the web experience profile. Required. Must be unique
		$webProfile->setName("Boutique.com Shop" . uniqid())
		    // Parameters for flow configuration.
		    ->setFlowConfig($flowConfig)
		    // Parameters for style and presentation.
		    ->setPresentation($presentation)
		    // Parameters for input field customization.
		    ->setInputFields($inputFields);
		
		// For Sample Purposes Only.
		$request = clone $webProfile;
		
		try {
		    // Use this call to create a profile.
		    $createProfileResponse = $webProfile->create($apiContext);
		    return $createProfileResponse;
		} catch (\PayPal\Exception\PayPalConnectionException $ex) {
		    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
             throw $ex ;
		}
  
	}
	
	
	
	
	
	public static function executePayement($paymentId , $payerID){
		$payment = Payment::get($paymentId, self::getApiContext());
		
		// ### Payment Execute
		// PaymentExecution object includes information necessary
		// to execute a PayPal account payment.
		// The payer_id is added to the request query parameters
		// when the user is redirected from paypal back to your site
		$execution = new PaymentExecution();
		$execution->setPayerId($payerID);
		
		// ### Optional Changes to Amount
		// If you wish to update the amount that you wish to charge the customer,
		// based on the shipping address or any other reason, you could
		// do that by passing the transaction object with just `amount` field in it.
		// Here is the example on how we changed the shipping to $1 more than before.
		$transaction = new Transaction();
		$amount = new Amount();
		$details = new Details();
		
		
		
		$amount->setCurrency(self::$device);
		$amount->setTotal(5);
		//$amount->setDetails($details);
		$transaction->setAmount($amount);
		
		// Add the above transaction object inside our Execution object.
		$execution->addTransaction($transaction);
		
		try {
			// Execute the payment
			// (See bootstrap.php for more on `ApiContext`)
			$result = $payment->execute($execution, $apiContext);
		
			// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
			return $result;
		
			try {
				$payment = Payment::get($paymentId, $apiContext);
			} catch (Exception $ex) {
				// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
				 throw $ex;
			}
		} catch (Exception $ex) {
             throw $ex;
		}

		
		return $payment;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}