<?php

class transactworldValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
		public $ssl = true;
		public $display_column_left = false;
                
	public function postProcess()
	{
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');		

                if (!Tools::getValue('ignoreAddress')){
			if ($cart->id_address_delivery == 0 || $cart->id_address_invoice == 0)
				Tools::redirect('index.php?controller=order&step=1');
		}		

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
		{
			if ($module['name'] == 'transactworld')
			{
				$authorized = true;
				break;
			}
		}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
 		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$mailVars = array(
				'{toid}' => Configuration::get('toid'),
				'{totype}' => nl2br(Configuration::get('totype')),
				'{partenerid}' => nl2br(Configuration::get('partenerid')),
				'{processingurl}' => nl2br(Configuration::get('processingurl')),
				'{ipaddr}' => nl2br(Configuration::get('ipaddr')),
				'{key}' => nl2br(Configuration::get('key')),
		);


		$this->module->validateOrder($cart->id,Configuration::get('PS_OS_TRANSACTWORLD_AWAITING'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');

		$currency = new CurrencyCore($this->context->cookie->id_currency);
		$currency_iso_code = $currency->iso_code;
		$cart_products = $this->context->cart->getProducts();
		$array_container=array();
		foreach($cart_products as $cart_product){
			$id_supplier    = $cart_product['id_supplier'];
			if (array_key_exists($id_supplier, $array_container)) {
				$array_container[]=$id_supplier;	
			}else{
				$array_container[]=$id_supplier;
			}	
		}
		$currency = new CurrencyCore($cart->id_currency);   // get currency iso code
		$my_currency_iso_code = $currency->iso_code;
		$my_currency_rate = $currency->conversion_rate;
		$total = $cart->getOrderTotal(true, Cart::BOTH);					

		$order = new Order($this->module->currentOrder);
		$orderid = $order->getUniqReference();

		$redirecturl = $this->context->link->getModuleLink('transactworld', 'redirecturl', array("orderid"=>$this->module->currentOrder), true);

		$totype = Configuration::get('totype');
		$toid =Configuration::get('toid'); // here change toid
		$keys=Configuration::get('key');
		$currency = new CurrencyCore($cart->id_currency);
                $currency_iso_code = $currency->iso_code;

		$array_container = array();
		$cart_products = $this->context->cart->getProducts();
		foreach($cart_products as $cart_product){
			$price          = $cart_product['total_wt'];
			$id_supplier    = $cart_product['id_supplier'];
			$quantity    = $cart_product['quantity'];
			$dprice=number_format($dprice,  2, '.', '')+(number_format($price,  2, '.', ''));
			$array_container[$id_supplier]=number_format($dprice,  2, '.', '');
			
		}
		
		foreach($array_container as $key => $value){
			$mykey[] = $key;
			$myvalue[] = $value;
		}

		$counts=count($mykey);
		for($j=0;$j<$counts;$j++){                        
                        $totalAmount=number_format($totalAmount, 2, '.', '')+number_format($myvalue[$j], 2, '.', '');
			$z++;
		}

		if($z>1)
		{
			$Amount=number_format($totalAmount, 2, '.', '');
		}
		else
		{
			$Amount=number_format($totalAmount, 2, '.', '');
		}

		$merchantid1=Configuration::get('merchantid1');
		$string = "$merchantid1|$totype|$Amount|$orderid|$redirecturl|$keys";
		$checksum = md5($string);

		Tools::addCSS(_PS_BASE_URL_._MODULE_DIR_ . $this->module->name. '/views/css/paymentz.css');
                
                $url="";
                if(Configuration::get('test_mode') =="Y")
                { 
                  $url=Configuration::get("processingurl");
                  
                }
                else
                {
				   $url=Configuration::get("liveurl");
                  
                }    

		$this->context->smarty->assign(array(
				'content_only' => 1,
				"toid"=>$merchantid1,
				"totype"=>$totype,
			    "partenerid"=>Configuration::get('partenerid'),
			    "pctype"=>"1_1|1_2",				
				"paymenttype"=>"",				
				"cardtype"=>"",				
				"reservedField1"=>"",				
				"reservedField2"=>"",
				"ipaddr"=>Configuration::get('ipaddr'),
				"amount"=>$Amount,
				"description"=>$orderid,
				"redirecturl"=>$redirecturl,
				"checksum"=>$checksum,
				"firstname"=>$address->firstname,
				"lastname"=>$address->lastname,
				"TMPL_street"=>$address->address1.((isset($address->address2)&&!empty($address->address2))?" ".$address->address2:""),
				"TMPL_city"=>$address->city,
				"TMPL_COUNTRY"=>Country::getIsoById($address->id_country),
				"TMPL_state"=>$state->iso_code,
				"TMPL_CURRENCY"=>$my_currency_iso_code,
				"TMPL_zip"=>$address->postcode,
				"TMPL_telno"=>($address->phone_mobile != "")?$address->phone_mobile:$address->phone,
				"TMPL_telnocc"=>"",
				"TMPL_emailaddr"=>$this->context->customer->email,
				"orderdescription"=>$orderid,
				"orderid"=>$orderid,
				"processingurl"=>$url,
		));
		$this->setTemplate('redirect.tpl');
	}

	/**
	 *collect all merchant details
	 *@author Dev-102
	 *@param String $currency_iso Currency ISO Code
	 *@return array $merchant_details Merchant Configuration details
	 */	

	public function getMerchantDetails($currency_iso)
	{
		$merchant_details = array();
		$order_total = floatval(number_format($this->context->cart->getOrderTotal(true,3), 2, '.', ''));

		$SQL = "SELECT * FROM "._DB_PREFIX_.$this->module->name." WHERE `currency` = '".$currency_iso."' AND `min_amount` <= ".$order_total." AND `max_amount` >= ".$order_total;		

		$result = Db::getInstance()->executeS($SQL);
		foreach($result as $key=>$merchant)
		{
			$merchant_details['merchant_id'] = $merchant['merchant_id'];
			$merchant_details['terminals'][$merchant['member_id']] = $merchant['terminal_name'];
		}
		return $merchant_details;
	}	
}