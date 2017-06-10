
<?php
session_start();
if (!defined('_PS_VERSION_'))
	exit;
class Transactworld extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();
	public $details;
	public $owner;
	public $address;
	public $extra_mail_vars;
	public function __construct()
	{

		
		$this->name = 'transactworld';
		$this->order = 'orders';
		$this->cart = 'cart_product';
                $this->paymentzConfig = 'paymentz_config';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'TransactWorld';
		$this->controllers = array('validation');
		$this->ps_versions_compliancy = array('min' => '1.4', 'max' => _PS_VERSION_);
		$this->is_eu_compatible = 0;


		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('toid', 'totype','processingurl','ipaddr','key','ccv','cca','ccm','ew','v'));
		if (!empty($config['totype']))
			$this->totype = $config['totype'];
		if (!empty($config['processingurl']))
			$this->processingurl =$config['processingurl'];
		if (!empty($config['key']))
			$this->processingurl = $config['key'];
		if (!empty($config['ccv']))
			$this->processingurl = $config['ccv'];
		if (!empty($config['cca']))
			$this->processingurl = $config['cca'];
		if (!empty($config['ccm']))
			$this->processingurl = $config['ccm'];
		if (!empty($config['ew']))
			$this->processingurl = $config['ew'];
		if (!empty($config['v']))
			$this->processingurl = $config['v'];

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('TransactWorld');
		$this->description = $this->l('Accept payments for your products via TransactWorld.');
		$this->confirmUninstall = $this->l('Are you sure about removing these details?');
		if (!isset($this->toid) || !isset($this->totype) || !isset($this->processingurl ))
			$this->warning = $this->l('All the details must be configured before using this module.');
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		$this->extra_mail_vars = array(
				'{toid}' => Configuration::get('toid'),
				'{totype}' => nl2br(Configuration::get('totype')),
				'{processingurl}' => nl2br(Configuration::get('processingurl')),
				'{ipaddr}' => nl2br(Configuration::get('ipaddr')),
				'{key}' => nl2br(Configuration::get('key')),
				'{ccv}' => nl2br(Configuration::get('ccv')),
				'{ccm}' => nl2br(Configuration::get('ccm')),
				'{cca}' => nl2br(Configuration::get('cca')),
				'{ew}' => nl2br(Configuration::get('ew')),
				'{v}' => nl2br(Configuration::get('v'))
		);
	}

	public function hookDisplayHeader($params)
	{
		$this->context->controller->addjQuery();
		$this->context->controller->addJS($this->_path.'views/js/form-validation.js', 'all');
	}
	
	public function hookDisplayBackOfficeTop()
	{
		$this->context->controller->addjQuery();
		$this->context->controller->addJS($this->_path.'views/js/backend-config.js');
	}

	
	 public function install()
	 {
		 $id_shop = Shop::getContextShopID(true);
		if ($id_shop !== null)
		{
		 $this->_errors[] = $this->l('Please select All-Shop.');
			 return false;
		 }
		
		if (!parent::install() || !$this->registerHook('payment') || ! $this->registerHook('displayPaymentEU') || !$this->registerHook('paymentReturn')  || !$this->registerHook('header')|| !$this->registerHook('displayBackOfficeTop') || !$this->createOrderStatuses())
		 return false;
		 return true;
	 }

	 
	public function createOrderStatuses()
	{
		// create new order status STATUSNAME
		$order_states = array(array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#006fe3',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 0,
				'deleted' => 0,
				'status_config' => 'PS_OS_TRANSACTWORLD_AWAITING', //from here changed
				'name' =>'Awaiting TransactWorld payment'
				),
				array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#64cc00',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 1,
				'deleted' => 0,
				'status_config' => 'PS_OS_TRANSACTWORLD_SUCCESS',
				'name' =>'TransactWorld Payment Success'
				),
				array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#ffad33',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 1,
				'deleted' => 0,
				'status_config' => 'PS_OS_TRANSACTWORLD_PARTIALLYSUCCESS',
				'name' =>'TransactWorld Payment Partial Success'
				),
				array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#a02f00',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 0,
				'deleted' => 0,
				'status_config' => 'PS_OS_TRANSACTWORLD_FAILED',
				'name' =>'TransactWorld Payment Failed'
				),
				array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#20B2AA',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 1,
				'deleted' => 0,
				'status_config' => 'PS_OS_TRANSACTWORLD_REVERSED',
				'name' =>'TransactWorld Payment Reversed'
				),
				array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#00FA9A',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 1,
				'deleted' => 0,
				'status_config' => 'PS_OS_PAYMENTZ_CHARGEBACK',
				'name' =>'Paymentz Payment Chargeback'
				),
				array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#F5DEB3',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 1,
				'deleted' => 0,
				'status_config' => 'PS_OS_PAYMENTZ_SETTLED',
				'name' =>'Paymentz Payment Settled'
				),
				array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#F5DEB3',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 1,
				'deleted' => 0,
				'status_config' => 'PS_OS_PAYMENTZ_CANCELLED',
				'name' =>'Paymentz Payment Cancel'
				),
				array(
				'invoice' => 1,
				'send_email' => 0,
				'module_name' => $this->name,
				'color' => '#F5DEB3',
				'unremovable' => 0,
				'hidden' => 0,
				'logable' => 1,
				'delivery' => 0,
				'shipped' => 0,
				'paid' => 1,
				'deleted' => 0,
				'status_config' => 'PS_OS_PAYMENTZ_UNKNOWN_STATUS',
				'name' =>'Paymentz Payment Fail with Unknown Reason'
				)
			);
			
				
		
		foreach($order_states as $status_value)
		{
			$status_name		= $status_value['name'];
			$status_config_name = $status_value['status_config']; 
			unset($status_value['name']);
			unset($status_value['status_config']);
			
			if (!Configuration::get($status_config_name)){
				
				if(!Db::getInstance()->insert('order_state', $status_value)){
					return false;
				}
				$id_order_state = (int)Db::getInstance()->Insert_ID();

				$languages = Language::getLanguages(false);
				foreach ($languages as $language)
				{
					Db::getInstance()->insert('order_state_lang', array('id_order_state'=>$id_order_state, 'id_lang'=>$language['id_lang'], 'name'=>$status_name, 'template'=>''));
				}

				@copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'logo.gif', _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'os'.DIRECTORY_SEPARATOR.$id_order_state.'.gif');

				Configuration::updateValue($status_config_name, $id_order_state, false, 0, 0);
			}
		}
		return true;
	}

	public function removeOrderStatuses()
	{
		$where = "`module_name` = '".$this->name."'";
		if(!Db::getInstance()->delete('order_state',$where))
			return false;

		return true;
	}
	public function removeOrderStatuses_lang()
	{
		$where = "`name` in ('Awaiting TransactWorld payment','TransactWorld Payment Success','TransactWorld Payment Partial Success','TransactWorld Payment Failed','TransactWorld Payment Reversed','Paymentz Payment Chargeback','Paymentz Payment Settled','Paymentz Payment Cancel','Paymentz Payment Fail with Unknown Reason')";
		if(!Db::getInstance()->delete('order_state_lang',$where))
			return false;

		return true;
	}
	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$secret_key = Tools::getValue('key');
			
			if (!Tools::getValue('totype'))
				$this->_postErrors[] = $this->l('Name of Payment Service Provider is required.');//jh
//			elseif (!Tools::getValue('defaultMinimum'))
//				$this->_postErrors[] = $this->l('Minimum Amount is required.');
//			elseif (!Tools::getValue('defaultMaximum'))
//				$this->_postErrors[] = $this->l('Maximum Amount is required.'); // Minimum Amount
			elseif (!Tools::getValue('processingurl'))
				$this->_postErrors[] = $this->l('processingurl is required.');
			elseif(empty($secret_key) || $secret_key[0] == "")
				$this->_postErrors[] = $this->l('Secret key is required.');			
		}
	}

	private function _postValidationSplit()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$toid = Tools::getValue('toid');
			$seller_id = Tools::getValue('seller_id');
			$currency = Tools::getValue('currency');
			$card_type = Tools::getValue('card_type');
			$payment_type = Tools::getValue('payment_type');
			$min_amount = Tools::getValue('min_amount');
			$max_amount = Tools::getValue('max_amount');
		
			if(empty($toid) || $toid[0] == "")
				$this->_postErrors[] = $this->l('Merchant ID required.');
			elseif(empty($seller_id) || $seller_id[0] == "")
				$this->_postErrors[] = $this->l('Seller Id is required.');
			elseif(empty($card_type) || $card_type[0] == "")
				$this->_postErrors[] = $this->l('Card Type is required.');
			elseif(empty($payment_type) || $payment_type[0] == "")
				$this->_postErrors[] = $this->l('Payment Type is required.');
			elseif(empty($currency) || $currency[0] == "")
				$this->_postErrors[] = $this->l('Please Select Curreny');
			elseif(empty($min_amount) || $min_amount[0] == "")
				$this->_postErrors[] = $this->l('Minimum Ammount is required.');
			elseif(empty($max_amount) || $max_amount[0] == "")
				$this->_postErrors[] = $this->l('Maximum Ammount is required.');
			
		}
	}
	
	private function _postSplitConfig()
	{
		if(Tools::isSubmit('btnSplitConfig'))
		{
			Configuration::updateValue('split_member', Tools::getValue('split_member'));
			$split_member = Tools::getValue('split_member');
			$userid=Configuration::get('toid');
			$seller_id = Tools::getValue('seller_id');
			$merchant_id = Tools::getValue('toid');
			$currency = Tools::getValue('currency');
			$minimum_amount = Tools::getValue('min_amount');
			$maximum_amount = Tools::getValue('max_amount');
			$card_type = Tools::getValue('card_type');
			$payment_type = Tools::getValue('payment_type');
			
			$arrayStore=array();
			$arraySize=count($merchant_id);
			$done=array();

			for($i=0;$i<$arraySize;$i++)
			{
				$j=$i;
				$j=$j+1;
				$arrayStore[]=$card_type[$i].'-'.$payment_type[$i].'-'.$currency[$i].'-'.$seller_id[$i];
				if(!preg_match('/^[-a-zA-Z0-9]+$/', $seller_id[$i]))
				{
					$done[]="1";
					$this->_html .= $this->displayError('Invalid Seller ID: Accept only [0-9][A-Z][a-z], on row number '.$j);
				}
				if(!preg_match('/^[-a-zA-Z0-9]+$/', $merchant_id[$i]))
				{
					$done[]="1";
					$this->_html .= $this->displayError('Invalid Merchant ID: Accept only [0-9][A-Z][a-z], on row number '.$j);
				}
				
				if(!preg_match('/^(\d)*\.(\d)+$/', $minimum_amount[$i]) && !preg_match('/^[-0-9]+$/', $minimum_amount[$i]))
				{
					$done[]="1";
					$this->_html .= $this->displayError('Invalid Minimum Amount: Accept only [0-9] or decimal values, on row number '.$j);
				}
				if(!preg_match('/^(\d)*\.(\d)+$/', $maximum_amount[$i]) && !preg_match('/^[-0-9]+$/', $maximum_amount[$i]))
				{
					$done[]="1";
					$this->_html .= $this->displayError('Invalid Maximum Amount: Accept only [0-9] or decimal values, on row number '.$j);
				}
				
			}
			
			if(count($arrayStore)==count(array_count_values($arrayStore)))
			{
			}
			else
			{
			   
			    $done[]="1";
			    $this->_html .= $this->displayError('Payment type, Card type, Seller Id and Currency combinations can not be repeated.');
			}

			if(!count($done))
			{
				$SQL = "TRUNCATE TABLE `"._DB_PREFIX_.$this->name."`";
				Db::getInstance()->execute($SQL);
				$k=1;
				foreach($merchant_id as $id_key => $id_value)
				{
					$member_id = $id_value;
					$sellerid = $seller_id[$id_key];
					$terminal_currency = $currency[$id_key];

					$data = array(
							"seller_id" =>$sellerid,
							"merchant_id" =>$userid,
                                                        "toid" =>$userid,
							"member_id" =>$member_id,
							"terminal_name" =>$name_terminal,
							"currency" =>$terminal_currency,
							"min_amount" =>$minimum_amount[$id_key],
							"max_amount" =>$maximum_amount[$id_key],
							"card_type" => $card_type[$id_key],
							"payment_type" => $payment_type[$id_key]
						);
					Db::getInstance()->insert($this->name,$data);
					$k++;
				}
				$this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));
			}
		}
	}
	
	
	private function _postProcess()
	{		
		if (Tools::isSubmit('btnSubmit'))
		{
			
//			$defaultMinimum = Tools::getValue('defaultMinimum');
//			$defaultMaximum = Tools::getValue('defaultMaximum');
			$totype = Tools::getValue('totype');
			//$processingurl = 'https://staging.paymentz.com/transaction/PayProcessController'; //Tools::getValue('processingurl'); 
			$ipaddr = Tools::getValue('ipaddr');
			$userid = Tools::getValue('toid');
			$seller_id = Tools::getValue('seller_id');
			$merchant_id = Tools::getValue('toid');
			$secret_key = Tools::getValue('key');
			$done=array();
			
//			if(!preg_match('/^[-a-zA-Z]+$/', $totype))
//			{
//				$done[]="1";
//				$this->_html .= $this->displayError('Invalid Payment Service Provider: Accept only [A-Z][a-z].');
//			}
//			if (filter_var($processingurl, FILTER_VALIDATE_URL) !== false)
//			{	
//			}
//			else
//			{
//				$done[]="1";
//				$this->_html .= $this->displayError('Invalid processing URL.');
//			}
//			if(!preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $ipaddr))
//			{
//				$done[]="1";
//				$this->_html .= $this->displayError('Invalid Merchant Server IP Address: Accept only [0-9] and `.`');
//			}
//			if(!preg_match('/^[-a-zA-Z0-9]+$/', $secret_key))
//			{
//				$done[]="1";
//				$this->_html .= $this->displayError('Invalid Secret Key: Accept only [0-9][A-Z][a-z], on row number ');
//			}
			
			if(!count($done))
			{
				
				Configuration::updateValue('totype', Tools::getValue('totype'));
				Configuration::updateValue('processingurl', Tools::getValue('processingurl'));
				Configuration::updateValue('liveurl', Tools::getValue('liveurl'));
                                Configuration::updateValue('merchantid1', Tools::getValue('merchantid1'));
				Configuration::updateValue('ipaddr', Tools::getValue('ipaddr'));
				Configuration::updateValue('key', Tools::getValue('key'));
				Configuration::updateValue('toid', Tools::getValue('toid'));
				Configuration::updateValue('test_mode', Tools::getValue('test_mode'));
//				Configuration::updateValue('defaultMinimum', Tools::getValue('defaultMinimum'));
//				Configuration::updateValue('defaultMaximum', Tools::getValue('defaultMaximum'));
				
				$this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));
			}
			

			
			
		}
		
	}
	
	private function _postPaymentTypeProcess()
	{
		if(Tools::isSubmit('btnPaymentTypeSubmit'))
		{
			$payment_type_id = Tools::getValue("payment_type_id");
			$payment_type_name = Tools::getValue("payment_type_name");
			$card_required = Tools::getValue("card_required");
			if(empty($payment_type_id))
				$this->_postErrors[] = $this->l('Payment Type ID required.');
			else if(empty($payment_type_name))
				$this->_postErrors[] = $this->l('Payment Type Name required.');
			$arraycount=count($payment_type_id);
			for($i=0;$i<$arraycount;$i++)
			{

				if(!preg_match('/^[-0-9]+$/', $payment_type_id[$i]))
				{
					$this->_postErrors[] = $this->l('Invalid Payment Type ID: Accept only [0-9].');
				}
				if(!preg_match('/^[-a-zA-Z]+$/', $payment_type_name[$i]))
				{
					$this->_postErrors[] = $this->l('Invalid Payment Type Name: Accept only [0-9][a-zA-Z].');
				}
				
			}


			if (!count($this->_postErrors))
			{
				$SQL = "TRUNCATE TABLE `"._DB_PREFIX_."paymentz_payment_types`";
				Db::getInstance()->execute($SQL);
				
					
				foreach($payment_type_id as $key=>$value)
				{
					
					$data = array("payment_type_id" => $value,
							"payment_type_name" => $payment_type_name[$key],
							"card_required"=>$card_required[$key],
							"language" => 1
							);
					Db::getInstance()->insert('paymentz_payment_types',$data);
				}
						
				$SQL = "SELECT DISTINCT payment_type_id FROM `"._DB_PREFIX_."paymentz_card_types` WHERE payment_type_id NOT IN (
					SELECT payment_type_id FROM `"._DB_PREFIX_."paymentz_payment_types`
				)";
				$resultData=Db::getInstance()->executeS($SQL);
				foreach($resultData as $key=>$value)
				{
						$deleteSQL = "delete FROM `"._DB_PREFIX_."paymentz_card_types` WHERE payment_type_id=".$value['payment_type_id'];
						Db::getInstance()->executeS($deleteSQL);
				}
				
				$SQL = "SELECT payment_type_id FROM `"._DB_PREFIX_."paymentz_payment_types` WHERE payment_type_id NOT IN (
							SELECT payment_type_id FROM `"._DB_PREFIX_."paymentz_card_types`
						)";
				$resultData1=Db::getInstance()->executeS($SQL);
				foreach($resultData1 as $key=>$value)
				{
						$payment_type_id1[]=$value['payment_type_id'];
				}
				$countRow=count($payment_type_id1);
				for($j=0;$j<=$countRow-1;$j++)
				{
					
					$SQL = "Select DISTINCT card_type_id,card_type_name,language,logo from `"._DB_PREFIX_."paymentz_card_types`";
					$resultData2=Db::getInstance()->executeS($SQL);
					foreach($resultData2 as $key=>$value)
					{
							$data = array("card_type_id" => $value['card_type_id'],
								"card_type_name" => $value['card_type_name'],
								"language" => $value['language'],
								"payment_type_id" => $payment_type_id1[$j],
								"logo" => $value['logo']
							);
							Db::getInstance()->insert('paymentz_card_types',$data);
					}
					
				}
				
				
			}
			else {
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
			}
					
		}
	}

	private function _postCardTypeProcess()
	{
		if(Tools::isSubmit('btnCardTypeSubmit'))
		{
			$card_type_id = Tools::getValue("card_type_id");
			$card_type_name = Tools::getValue("card_type_name");
			$payment_type = Tools::getValue("payment_type_id");
			$existing_logo = Tools::getValue("existing_logo");


			$arraycount=count($card_type_id);
			for($i=0;$i<$arraycount;$i++)
			{

				if(!preg_match('/^[-0-9]+$/', $card_type_id[$i]))
				{
					$this->_postErrors[] = $this->l('Invalid Card Type ID: Accept only [0-9].');
				}
				if(!preg_match('/^[-a-zA-Z]+$/', $card_type_name[$i]))
				{
					$this->_postErrors[] = $this->l('Invalid Card Type Name: Accept only [a-zA-Z].');
				}
				
			}

            // Card logo extension validations
			foreach($card_type_id as $key=>$value){
				if($_FILES['card_type_logo']['name'][$key])
				{
					$logo = explode('.',$_FILES['card_type_logo']['name'][$key]);
					$file_ext = array("jpeg", "jpg", "png", "gif");

					if (!in_array($logo[1], $file_ext))
					   $this->_postErrors[] = $this->l('Card Logo allows only jpeg, jpg, png and gif.');
				}
			}
			
			if(empty($card_type_id))
				$this->_postErrors[] = $this->l('Card Type ID required.');
			else if(empty($card_type_name))
				$this->_postErrors[] = $this->l('Card Type Name required.');
				
			if (!count($this->_postErrors))
			{
				$SQL = "delete  from `"._DB_PREFIX_."paymentz_card_types`";
				$result = Db::getInstance()->executeS($SQL);
				
				$SQL = "Select *  from `"._DB_PREFIX_."paymentz_payment_types`";
				$result = Db::getInstance()->executeS($SQL);
				foreach($result as $key=>$data)
				{
				
					$payment_type_id=$data['payment_type_id'];
					$SQL = "TRUNCATE TABLE  from `"._DB_PREFIX_."paymentz_card_types`";
					Db::getInstance()->execute($SQL);
					foreach($card_type_id as $key=>$value)
					{
						
						
						$image_name[] = "";
						if($_FILES['card_type_logo']['name'][$key] != "")
						{
							if($_FILES['card_type_logo']['tmp_name'][$key] !="")
							{
								$targetFile = __DIR__."/images/".$_FILES['card_type_logo']['name'][$key];
								if(move_uploaded_file($_FILES['card_type_logo']['tmp_name'][$key], $targetFile))
									$image_name[$key] = $_FILES['card_type_logo']['name'][$key];
							}	
						}else {
							$image_name[$key] = isset($existing_logo[$key])?$existing_logo[$key]:"";
						}
						$data = array("card_type_id" => $value,
								"card_type_name" => $card_type_name[$key],
								"language" => 1,
								"payment_type_id" => $payment_type_id,
								"logo" => $image_name[$key]
							);
							Db::getInstance()->insert('paymentz_card_types',$data);
					}
					
					
					
				}

			}
			else {
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
			}				
		}
	}

   /**
	 * get cron setting data
	 * @author Dev-114
	 * @param integer min, hrs, month, dayofmonth, dayofweek
	 * @return string min, hrs, month, dayofmonth, dayofweek and command
	 */
	private function _postCronTypeProcess()
	{
		if(Tools::isSubmit('btnCronTypeSubmit'))
		{
			$min = Tools::getValue("min");
			$hrs = Tools::getValue("hrs");
			$dayofmonth = Tools::getValue("dayofmonth");
			$month = Tools::getValue("month");
			$dayofweek = Tools::getValue("dayofweek");	

			if(!preg_match('/^[-0-9]+$/', $min)&& $min!="")
			{
				$this->_html .= $this->displayError('Invalid Minute: Accept only [0-59]');
			}
			else if(!preg_match('/^[-0-9]+$/', $hrs) && $hrs!="")
			{
				$this->_html .= $this->displayError('Invalid Hour: Accept only [0-23]');
			}
			else if(!preg_match('/^[-0-9]+$/', $dayofmonth) && $dayofmonth!="")
			{
				$this->_html .= $this->displayError('Invalid Day Of Month: Accept only [1-31]');
			}
			else if(!preg_match('/^[-0-9]+$/', $month) && $month!="")
			{
				$this->_html .= $this->displayError('Invalid Month: Accept only [1-12]');
			}
			else if(!preg_match('/^[-0-9]+$/', $dayofweek) && $dayofweek!="")
			{
				$this->_html .= $this->displayError('Invalid Day Of Week: Accept only [1-6]');
			}
			else
			{
	
			$data = array(
							"c_min" =>$min,
							"c_hrs" =>$hrs,
							"c_dayofmonth" =>$dayofmonth,
							"c_month" =>$month,
							"c_dayofweek" =>$dayofweek
						);
					Db::getInstance()->insert($this->crontable,$data);
			
			$url = _PS_BASE_URL_ .__PS_BASE_URI__."modules/transactworld/createcron.php";
			
			$fields = array(
									'c_min' => urlencode($min),
									'c_hrs' => urlencode($hrs),
									'c_dayofmonth' => urlencode($dayofmonth),
									'c_month' => urlencode($month),
									'c_dayofweek' => urlencode($dayofweek)
							);

			//url-ify the data for the POST
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, '&');
			//open connection
			$ch = curl_init();
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			//execute post
			$result = curl_exec($ch);
			//close connection
			curl_close($ch);
			//curl code ends.
			//cron delay days code start.	
			$days = Tools::getValue("days");
			$data = array(
						"delay_days" =>$days
					);
				Db::getInstance()->insert($this->delayDaystable,$data);
			//cron delay days code end.

			//status_config start.
			Configuration::updateValue('StatusUrl', Tools::getValue('statusurl'));
			//status_config end.	
			$this->_html .= $this->displayConfirmation($this->l('Data Saved successfully.'));	
				
			}
					
						
		}
		
		if(Tools::isSubmit('btnruncron'))
		{			
			$url = _PS_BASE_URL_ .__PS_BASE_URI__."modules/transactworld/statusupdate.php";
			$ssl= _PS_BASE_URL_ .__PS_BASE_URI__."modules/transactworld/ssl.cer";
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CAINFO, $ssl);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch,CURLOPT_POST,0);
			$result = curl_exec($ch);
			curl_close($ch);
			$this->_html .= $this->displayConfirmation($this->l('Cron run successfully.'));
		}
	}
	private function _postSatComConfigProcess()
	{
		if(Tools::isSubmit('btnSatcomConfigSubmit'))
		{
			//TO DO 
			Configuration::updateValue('SatcomServer_Protocol', Tools::getValue('SatcomServer_Protocol'));
			Configuration::updateValue('SatcomServer_IP', Tools::getValue('SatcomServer_IP'));
			Configuration::updateValue('SatcomServer_Port', Tools::getValue('SatcomServer_Port'));
			Configuration::updateValue('Token_APPID', Tools::getValue('Token_APPID'));
			Configuration::updateValue('Token_Prename', Tools::getValue('Token_Prename'));
			Configuration::updateValue('Token_Name', Tools::getValue('Token_Name'));
			Configuration::updateValue('GroundServer_Protocol', Tools::getValue('GroundServer_Protocol'));
			Configuration::updateValue('GroundServer_Host', Tools::getValue('GroundServer_Host'));
			Configuration::updateValue('GroundServer_Path', Tools::getValue('GroundServer_Path'));
			Configuration::updateValue('VPS_Path', Tools::getValue('VPS_Path'));
			$this->_html .= $this->displayConfirmation($this->l('Data Saved successfully.'));	
		}
	}
	
	
	
	private function _displayPaymentz()
	{
		$SQL = "SELECT * FROM `"._DB_PREFIX_.$this->name."`";
		$merchant_details = Db::getInstance()->executeS($SQL);
		$currencies = Currency::getPaymentCurrencies($this->id);
		$card_type_initial = array(array("card_type_id"=>"","card_type_name"=>"-"));
		$card_type = $this->getcardTypePz();
		
		$paymemt_type = $this->getPaymentTypes();
		
		$card_type = array_merge($card_type_initial,$card_type);
		$logo_path = _PS_BASE_URL_ .__PS_BASE_URI__."modules/transactworld/images/";
		
		$this->smarty->assign(array(
				'currencies' => $currencies,
				'merchant_details' => $merchant_details,
				'card_type' => $card_type,
				'payment_type' => $paymemt_type,
				'logo_path' =>$logo_path
		));
		
		//cron data display.
//		$SQL = "SELECT * FROM `"._DB_PREFIX_.$this->crontable."` ORDER BY c_id DESC LIMIT 1";
//        $result = Db::getInstance()->executeS($SQL);
//
//        foreach($result as $key=>$crondata)
//        {
//            $c_min = $crondata['c_min'];
//            $c_hrs = $crondata['c_hrs'];
//            $c_dayofmonth = $crondata['c_dayofmonth'];
//			$c_month = $crondata['c_month'];
//			$c_dayofweek = $crondata['c_dayofweek'];
//        }
//		
		if($c_min!=null)
		{
			$this->context->smarty->assign(array(
			'min' => $c_min,
			'hrs' => $c_hrs,
			'dayofmonth' => $c_dayofmonth,
			'month' => $c_month,
			'dayofweek' => $c_dayofweek,
			));
		}
		
		
		//cron days time display.
//		$SQL = "SELECT * FROM `"._DB_PREFIX_.$this->delayDaystable."` ORDER BY id DESC LIMIT 1";
//        $result = Db::getInstance()->executeS($SQL);
//        foreach($result as $key=>$delaydata)
//        {
//            $ddays = $delaydata['delay_days'];
//        }
		$this->context->smarty->assign(array(
				'days' => $ddays,
				));
				
		return $this->display(__FILE__, 'info.tpl');
	}

	public function getContent()
	{
		if (Tools::isSubmit('btnSubmit'))
		{			
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
		}
		else if(Tools::isSubmit('btnPaymentTypeSubmit'))
		{
			$this->_postPaymentTypeProcess();
			
		}
		else if(Tools::isSubmit('btnCardTypeSubmit'))
		{
			$this->_postCardTypeProcess();
		}
		else if(Tools::isSubmit('btnCronTypeSubmit'))
		{
			$this->_postCronTypeProcess();
		}
		else if(Tools::isSubmit('btnruncron'))
		{
			$this->_postCronTypeProcess();
		}
		else if(Tools::isSubmit('btnSatcomConfigSubmit'))
		{
			$this->_postSatComConfigProcess();
		}
		else if(Tools::isSubmit('btnSplitConfig'))
		{
			$this->_postValidationSplit();
			if (!count($this->_postErrors))
				$this->_postSplitConfig();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
		}
		else
			$this->_html .= '<br />';

		$this->_html .= $this->_displayPaymentz();
		return $this->_html;
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;
		
		if (!$this->checkCurrency($params['cart']))
			return;				
		$ret=$this->checkModuleDispalyMode($params['cart']);		
		$merchant_config = $this->getMerchantDetails($this->context->currency->iso_code);
		$merchant_terminals = $merchant_config['terminals'];
		$merchant_card_types = $merchant_config['card_type'];
		$logo_path = _PS_BASE_URL_ .__PS_BASE_URI__."modules/transactworld/images/";
		$this->smarty->assign(array(
				'this_path' => $this->_path,
				'this_path_bw' => $this->_path,
				'merchant_card_types'=>$merchant_card_types,
				'processingurl'=> Configuration::get('processingurl'),
				'TMPL_CURRENCY'=>$this->context->currency->iso_code,
				'logo_path'=>$logo_path,
				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		if($ret=="true")
		{
			return $this->display(__FILE__, 'payment.tpl');
		}
		else if($ret=="comNotFound")
		{
			return $this->display(__FILE__, 'paymentz_supplierNotFound.tpl');
		}		
	}
	

	public function hookDisplayPaymentEU($params)
	{
		if (!$this->active)
			return;

		if (!$this->checkCurrency($params['cart']))
			return;


		return array(
				'cta_text' => $this->l('Pay by paymentz'),
				'logo' => Media::getMediaPath(dirname(__FILE__).'/logo.jpg'),
				'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
		);
	}

	public function hookPaymentCard($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;

		$this->smarty->assign(array(
				'this_path' => $this->_path,
				'this_path_bw' => $this->_path,
				'toid'=> $this->toid,
				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}


	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
			if ($currency_order->id == $currency_module['id_currency'])
			return true;
		return false;
	}

	public function checkModuleDispalyMode($cart)
	{
		
		global $cookie;
		$currency = new CurrencyCore($cookie->id_currency);
		$currency_iso_code = $currency->iso_code;
		$cart_products = $this->context->cart->getProducts();
		$array_container=array();
		$comb1=array();
		foreach($cart_products as $cart_product){
			$id_supplier    = $cart_product['id_supplier'];

			if (array_key_exists($id_supplier, $array_container)) {
				$array_container[]=$id_supplier;
				
			}
			else
			{
				$array_container[]=$id_supplier;
			}
				
		}
		$Supplier_counts=count($array_container);
		
		for($j=0;$j<$Supplier_counts;$j++)
		{
				$SQL = "SELECT *  FROM "._DB_PREFIX_."paymentz WHERE seller_id = '".$array_container[$j]."' AND currency = '".$currency_iso_code."'";
				$result = Db::getInstance()->executeS($SQL);
				foreach($result as $key=>$paymentz)
				{
					$comb1[$j][]= $paymentz['payment_type'].'0'.$paymentz['card_type'];	
				}	
		}
		$intersect = call_user_func_array('array_intersect',$comb1);
		$countedvalue=count($intersect);
		for($k=0;$k<=$countedvalue;$k++)
		{
			$card_type_Details.=$intersect[$k]."|";
		}
		$card_type_Details=rtrim($card_type_Details,"|");
		if(count(array_unique($array_container)) === 1 || $card_type_Details!="")
		{
			return "true";
		}
		else
		{
			return "comNotFound";
		}
	}
	public function renderForm()
	{
		$fields_form = array(
				'form' => array(
						'legend' => array(
								'title' => $this->l('Paymentz Merchant account Details'),
								'icon' => 'icon-envelope'
						),
						'input' => array(
								array(
										'type' => 'text',
										'label' => $this->l('Merchant id'),
										'name' => 'toid',
										'desc' => 'Merchantâ€™s Unique Id. Provided by Paymentz.com. It will be fixed for all transactions Accept only [0-9] ',
										'empty_message' => $this->l('This field is required.'),
										'required' => true
								),
								array(
										'type' => 'text',
										'label' => $this->l('Name of Payment Service Provider'),
										'name' => 'totype',
										'empty_message' => $this->l('This field is required.'),
										'desc' => $this->l('Provided by Paymentz.com. Do not change the value â€œpaymentzâ€�. It will be fixed for all transactions Accept only [0-9][A-Z][a-z]'),
										'required' => true
								),
								array(
										'type' => 'text',
										'label' => $this->l('Processing URL'),
										'name' => 'processingurl',
										'empty_message' => $this->l('This field is required.'),
										'desc'=> $this->l('URL where payment will be processed.'),
										'required' => true
								),
								array(
										'type' => 'text',
										'label' => $this->l('Merchant server IP Address'),
										'name' => 'ipaddr',
										'desc' => $this->l('Merchantâ€™s IP Address Ex: 127.127.127.127 Accept only [0-9] and â€˜.â€™ '),
										'required' => false
								),
								array(
										'type' => 'text',
										'label' => $this->l('Secret Key'),
										'name' => 'key',
										'empty_message' => $this->l('This field is required.'),
										'desc' => $this->l('Add secret key provided to you by Paymentz.'),
										'required' => true
								),
								array(
										'type' => 'text',
										'label' => $this->l('Terminal Id 1'),
										'name' => 'ccv',
										'desc' => $this->l('Terminal Id for Credit Card -> Visa payment type.'),
										'required' => false
								),
								array(
										'type' => 'text',
										'label' => $this->l('Terminal Id 2'),
										'name' => 'ccm',
										'desc' => $this->l('Terminal Id for Credit Card -> Master Card payment type.'),
										'required' => false
								),
								array(
										'type' => 'text',
										'label' => $this->l('Terminal Id 3'),
										'name' => 'cca',
										'desc' => $this->l('Terminal Id for Credit Card -> Amex payment type.'),
										'required' => false
								),
								array(
										'type' => 'text',
										'label' => $this->l('Terminal Id 4'),
										'name' => 'ew',
										'desc' => $this->l('Terminal Id for Ewallet  payment type.'),
										'required' => false
								),
								array(
										'type' => 'text',
										'label' => $this->l('Terminal 5'),
										'name' => 'v',
										'desc' => $this->l('Terminal Id for Voucher payment type.'),
										'required' => false
								),

						),
						'submit' => array(
								'title' => $this->l('Save'),
						)
				),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
				'fields_value' => $this->getConfigFieldsValues(),
				'languages' => $this->context->controller->getLanguages(),
				'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
				'toid' => Tools::getValue('toid', Configuration::get('toid')),
				'totype' => Tools::getValue('totype', Configuration::get('totype')),
				'processingurl' => Tools::getValue('processingurl', Configuration::get('processingurl')),
				'ipaddr' => Tools::getValue('ipaddr', Configuration::get('ipaddr')),
				'key' => Tools::getValue('key', Configuration::get('key')),
				'ccv' => Tools::getValue('ccv', Configuration::get('ccv')),
				'ccm' => Tools::getValue('ccm', Configuration::get('ccm')),
				'cca' => Tools::getValue('cca', Configuration::get('cca')),
				'ew' => Tools::getValue('ew', Configuration::get('ew')),
				'v' => Tools::getValue('v', Configuration::get('v')),
		);
	}
	
	public function getPaymentTypes()
	{
		$SQL = "SELECT * FROM `"._DB_PREFIX_."paymentz_payment_types` WHERE `language`=1";
		return Db::getInstance()->executeS($SQL);
	}

	public function getCardTypes()
	{
		$SQL = "SELECT  * FROM `"._DB_PREFIX_."paymentz_card_types` WHERE `language`=1";
		return Db::getInstance()->executeS($SQL);
	}
	public function getcardTypePz()
	{
		
		$SQL = "SELECT DISTINCT card_type_name,card_type_id,logo FROM `"._DB_PREFIX_."paymentz_card_types`";
        $result = Db::getInstance()->executeS($SQL);
       
		return $result;
	}
	/**
	 * get card type display name
	 * @author Dev-102
	 * @param integer $card_type_id \
	 * @return string $card_type Card type Name
	 */
//	public function getCardTypeName($card_type_id)
//	{
//		if($card_type_id != "")
//		{
//			$card_type = array("1"=>array("name"=>"VISA","image"=>"visa.png"),
//				"2"=>array("name"=>"MASTER CARD","image"=>"master-card.jpg"),
//				"3"=>array("name"=>"DINER","image"=>"diners.gif"),
//				"4"=>array("name"=>"AMEX","image"=>"amex.jpg"),
//				"5"=>array("name"=>"DISCOVER","image"=>"discover.gif"),
//				"6"=>array("name"=>"CUP","image"=>"cup.jpg"));
//			return $card_type[$card_type_id];
//		}
//	}
	
//	public function getPaymentTypeName($card_type_id)
//	{
//		
//		
//	}
	
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
	
		$SQL = "SELECT * FROM "._DB_PREFIX_.$this->name." WHERE `currency` = '".$currency_iso."' AND `min_amount` <= ".$order_total." AND `max_amount` >= ".$order_total;
		$result = Db::getInstance()->executeS($SQL);
		foreach($result as $key=>$merchant)
		{
			$merchant_details['merchant_id'] = $merchant['merchant_id'];
			$merchant_details['terminals'][] = $merchant['member_id'];
			$merchant_details['card_type'][$merchant['card_type']] = $this->getCardTypeName($merchant['card_type']);
			$merchant_details['payment_type'][] = $merchant['payment_type'];
		}
		return $merchant_details;
	}
	
	/**
	 * collect cards by payment type
	 * @author Dev-102
	 * @param integer $paymenttype
	 * @return array $payment_type_array
	 */
	
		
//	public function getCardByPaymentType($paymenttype)
//	{
//
//	
//		
//		
//	}
  
}
?>