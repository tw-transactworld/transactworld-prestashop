<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
$action = Tools::getValue('action');
$transactworld = Module::getInstanceByName('transactworld');
switch($action)
{
    case "get_states":
        $iso = Tools::getValue('id_country');
        $id_country = Country::getByIso($iso);
        $states = State::getStatesByIdCountry($id_country);
		if(!empty($states))
		{	
			$html = '<select name="TMPL_state" id="selectState" class="form-control"><option value="">-</option>';
			foreach($states as $key=>$value)
			{            
				$html .= "<option value='".$value['iso_code']."'>".$value['name']."</option>"; 
			}
			$html .='</select>';
		}else {
			$html = '<input class="form-control" id="textState" type="text" name="TMPL_state" placeholder="Enter state code.">';
		}
		echo $html;
        exit;
        break;
    case "get_terminal":
    	$currency_iso = Tools::getValue('currency_iso');
    	$card_type = Tools::getValue('card_type');    	
    	$sql = "SELECT `terminal_id`,`payment_type` FROM `"._DB_PREFIX_.$transactworld->name."` WHERE `currency`='".$currency_iso."' AND `card_type`='".$card_type."'";
    	$result = Db::getInstance()->getRow($sql);
    	echo json_encode($result);
    	exit;
    	break;
    case "collect_card_types":
    	$card_types = $transactworld->getCardTypes();
    	foreach($card_types as $key=>$value)
    	{
    		$card_types_array[$value['payment_type_id']][] = array("card_type_id"=>$value['card_type_id'],"card_type_name"=>$value["card_type_name"]);
    	}
    	//Tools::p($card_types_array);
    	echo json_encode($card_types_array);
    	break;
    /*case "get_cards":
    	$paymenttype = Tools::getValue('paymenttype');
    	$card_types = $paymentz->getCardByPaymentType($paymenttype);
		//var_dump($card_types);
    	$html = "";
    	if(is_array($card_types) && !empty($card_types))
    	{
    		foreach($card_types as $key=>$value)
    		{
    			$checked = "";
    			if($key == 0)
    				$checked="checked";
    			
    			$image_html = "";
    			if($value['logo'] != "")
    			{
    				$image = _PS_BASE_URL_ .__PS_BASE_URI__."modules/paymentz/images/".$value['logo'];
    				if(@getimagesize($image))
    				{
    					$image_html = "<img src='".$image."' width='86px' height='49px'>";
    				}    				
    			}
    			$html .= "<input type='radio' name='cardtype' class='cardtype' $checked value='".$value['card_type']."'>&nbsp;&nbsp;&nbsp;".(($image_html!='')?$image_html:$value['card_type_name'])."</br>";
    		}
    	}else {
    		$html .= "-";
    	}    	
    	echo $html;
    	break;	*/	
}
?>