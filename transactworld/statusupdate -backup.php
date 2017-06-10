<?php
session_start();
require('../../config/config.inc.php');		
		//$this->name = 'orders';
//code for fetching all status id's.
		$SQL = "SELECT delay_days FROM "._DB_PREFIX_."paymentz_delay_days  ORDER BY id DESC LIMIT 1";
		$delaydays = Db::getInstance()->executeS($SQL);
		foreach($delaydays as $key=>$delaydays)
		{
			 echo  $delay_days=$delaydays['delay_days'];
			 
		}

		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Awaiting Paymentz payment' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			 echo  $Awaiting_Paymentz_payment=$order_state['id_order_state'];
			 echo "Awaiting_Paymentz_payment";
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Chargeback' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			echo $Paymentz_Payment_Chargeback=$order_state['id_order_state'];
			echo "Paymentz_Payment_Chargeback";
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Reversed' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			echo $Paymentz_Payment_Reversed=$order_state['id_order_state'];
			echo "Paymentz_Payment_Reversed";
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Settled' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			echo $Paymentz_Payment_Settled=$order_state['id_order_state'];
			echo "Paymentz_Payment_Settled";
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Failed' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			echo $Paymentz_Payment_Failed=$order_state['id_order_state'];
			echo "Paymentz_Payment_Failed";
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Success' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			echo $Paymentz_Payment_Success=$order_state['id_order_state'];
			echo "Paymentz_Payment_Success";
		}

		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Partial Success' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			echo $Paymentz_Payment_Partial_Success=$order_state['id_order_state'];
			echo "Paymentz_Payment_Partial_Success";
		}
		
//code for fetching awating status of each record.

		if ($sock = @fsockopen('www.google.com', 80, $num, $error, 5))
		{
				echo $SQL = "SELECT * FROM "._DB_PREFIX_."orders where current_state=".$Awaiting_Paymentz_payment." OR (current_state=$Paymentz_Payment_Success AND connection_mode='off') AND isProcessed=0";
				$orders_details = Db::getInstance()->executeS($SQL);
				
				foreach($orders_details as $key=>$orders_detail)
				{
					
					$user_id=$orders_detail['user_id'];
					$trackingid=$orders_detail['paymentz_id'];
					$id_order=$orders_detail['id_order'];
					$toid=$orders_detail['merchant_id'];
					$reference=$orders_detail['reference'];
					$secure_key=$orders_detail['secure_key'];
					
					$SQL = "SELECT secret_key FROM "._DB_PREFIX_."paymentz where merchant_id='$user_id' ORDER BY id_paymentz DESC LIMIT 1";
					$data = Db::getInstance()->executeS($SQL);
					foreach($data as $key=>$datavalue)
					{
						$secret_key=$datavalue['secret_key'];
					}
					
					echo $connection_mode=$orders_detail['connection_mode'];
					echo $current_state=$orders_detail['current_state'];
					echo $date_add=$orders_detail['date_add'];
					
					//$str = "$toid|$reference|$trackingid|0P1VSBlEkOzPUJBL2Px6UgR5PmQLHf3O";
					echo $str = "$toid|$reference|$trackingid";
					$generatedCheckSum = md5($str);
					
					$jsonRequest='{"toId": '.$toid.',"checkSum": "'.$generatedCheckSum.'","description": "'.$reference.'","trackingId": "'.$trackingid.'"}';	

					$ch = curl_init();
					$url="https://staging.paymentz.com/transactionServices/RESTful/DirectTransaction/status";
					curl_setopt($ch,CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_CAINFO, 'http://localhost:8080/presta/modules/paymentz/Patprocess_arjun/arjuncert.crt');
					curl_setopt($ch, CURLOPT_VERBOSE, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($ch,CURLOPT_POST,1);
					curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonRequest);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'Accept: application/json'
					));
					$result = curl_exec($ch);
					$jsonResponse=json_decode($result, true);
					echo "***************************************************";
					var_dump($jsonResponse);
					echo "***************************************************";
					 echo $status=$jsonResponse['status'];
						$trackingId=$jsonResponse['trackingId'];
					if($status=="authsuccess" || $status=="capturesuccess")
					{
						if(isset($trackingId))
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Success',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						else
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Success',`isProcessed`='1' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						
					}
					else if($status=="settled" || $status=="markedforreversal")
					{
						if(isset($trackingId))
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Settled',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						else
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Settled',`isProcessed`='1' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						
						
					}
					else if($status=="reversed")
					{
						
						if(isset($trackingId))
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Reversed',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						else
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Reversed',`isProcessed`='1' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						
						
					}
					else if($status=="chargeback")
					{
						
						if(isset($trackingId))
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Chargeback',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						else
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Chargeback',`isProcessed`='1' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						
						
					}
					else if($status=="authstarted" || $status=="proofrequired")
					{
						if(isset($trackingId))
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Awaiting_Paymentz_payment',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						else
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Awaiting_Paymentz_payment',`isProcessed`='1' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						
						
					}
					else if($status=="" || $status==null)
					{
						
						if($connection_mode=='on' && $current_state==$Awaiting_Paymentz_payment)
						{
							if(isset($trackingId))
							{
								$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
								$result = Db::getInstance()->executeS($updateSQL);
							}
							else
							{
								$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1' where id_order='$id_order'";
								$result = Db::getInstance()->executeS($updateSQL);
							}
						
							
						}
						
						if($connection_mode=='off' && $current_state==$Awaiting_Paymentz_payment)
						{
							
							if(strtotime($date_add)<strtotime('-'.$delay_days.' days'))
							{
								 //recorde is older than 5 days.
								if(isset($trackingId))
								{
									$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
									$result = Db::getInstance()->executeS($updateSQL);
								}
								else
								{
									$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1' where id_order='$id_order'";
								$result = Db::getInstance()->executeS($updateSQL);
								}
							
								
								
							 }

						}
						
						if($connection_mode=='off' && $current_state==$Paymentz_Payment_Success)
						{
							
							if(strtotime($date_add)<strtotime('-'.$delay_days.' days'))
							{
								 //recorde is older than 5 days.
								if(isset($trackingId))
								{
									$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
									$result = Db::getInstance()->executeS($updateSQL);
								}
								else
								{
									$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1' where id_order='$id_order'";
									$result = Db::getInstance()->executeS($updateSQL);
								}
								
							 }

						}
					}
					else 
					{
						
						if(isset($trackingId))
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1',`paymentz_id`='$trackingId' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						else
						{
							$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1' where id_order='$id_order'";
							$result = Db::getInstance()->executeS($updateSQL);
						}
						
						
					}
					
				}
		}
		else
		{
			//no net connection.
		}
		
		
		
		
		
			
?>