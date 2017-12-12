<?php
session_start();
require('../../config/config.inc.php');		

		$split_member = Configuration::get('split_member');
		$flight_mode = Configuration::get('flight_mode');
		$url=Configuration::get('StatusUrl');
		$SQL = "SELECT delay_days FROM "._DB_PREFIX_."paymentz_delay_days  ORDER BY id DESC LIMIT 1";
		$delaydays = Db::getInstance()->executeS($SQL);
		foreach($delaydays as $key=>$delaydays)
		{
			   $delay_days=$delaydays['delay_days'];
			 
		}
		
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Awaiting Paymentz payment' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			   $Awaiting_Paymentz_payment=$order_state['id_order_state'];
			  
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Chargeback' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			 $Paymentz_Payment_Chargeback=$order_state['id_order_state'];
			
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Reversed' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			 $Paymentz_Payment_Reversed=$order_state['id_order_state'];
			
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Settled' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			 $Paymentz_Payment_Settled=$order_state['id_order_state'];
			//echo "Paymentz_Payment_Settled";
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Failed' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			 $Paymentz_Payment_Failed=$order_state['id_order_state'];
			//echo "Paymentz_Payment_Failed";
		}
		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Success' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			 $Paymentz_Payment_Success=$order_state['id_order_state'];
			//echo "Paymentz_Payment_Success";
		}

		$SQL = "SELECT id_order_state FROM "._DB_PREFIX_."order_state_lang where name='Paymentz Payment Partial Success' ORDER BY id_order_state DESC LIMIT 1";
		$order_state = Db::getInstance()->executeS($SQL);
		foreach($order_state as $key=>$order_state)
		{
			 $Paymentz_Payment_Partial_Success=$order_state['id_order_state'];
		}
//code for fetching awating status of each record.

		/*if ($sock = @fsockopen('www.google.com', 80, $num, $error, 5))
		{*/
			
				

				$SQL = "SELECT * FROM "._DB_PREFIX_."orders where (current_state=".$Awaiting_Paymentz_payment." OR current_state=".$Paymentz_Payment_Partial_Success." OR current_state=$Paymentz_Payment_Success)  AND isProcessed=0";
				//var_dump($SQL);
				$orders_details = Db::getInstance()->executeS($SQL);
				
				foreach($orders_details as $key=>$orders_detail)
				{
					$iSucessStaus=0;
					$iFailedStaus=0;
					$iPendingStaus=0;
				
					$user_id=$orders_detail['user_id'];
					//var_dump("id  ".$user_id);
					$trackingid=$orders_detail['paymentz_id'];
					$id_order=$orders_detail['id_order'];
					$toid=$orders_detail['merchant_id'];
					$reference=$orders_detail['reference'];
					$secure_key=$orders_detail['secure_key'];
					
					/*$SQL = "SELECT secret_key FROM "._DB_PREFIX_."paymentz where merchant_id='$user_id' ORDER BY id_paymentz DESC LIMIT 1";
					$data = Db::getInstance()->executeS($SQL);
					foreach($data as $key=>$datavalue)
					{
						$secret_key=$datavalue['secret_key'];
					}*/
					$secret_key=Configuration::get('key');
					//var_dump("key  ".$secret_key);
					 $connection_mode=$orders_detail['connection_mode'];
					 $current_state=$orders_detail['current_state'];
					 $date_add=$orders_detail['date_add'];
					
					if($split_member=="Y")
					{
								
								//get data from paymentz_orderdetail table.
							
								 $SQLOrderDetails = "SELECT * FROM "._DB_PREFIX_."paymentz_orderdetails where order_reference='".$reference."' AND status!='failed'";
								$ordersdetails = Db::getInstance()->executeS($SQLOrderDetails);
								//var_dump($SQLOrderDetails);
								foreach($ordersdetails as $key=>$ordersdetail)
								{
									
									$id=$ordersdetail['id'];
									$order_details_reference=$ordersdetail['order_details_id'];
									
									//$str = "$toid|$reference|$trackingid|0P1VSBlEkOzPUJBL2Px6UgR5PmQLHf3O";
									// $str = "$toid|$order_details_reference|$trackingid|$secret_key";
									if($trackingid==NULL || $trackingid=="")
									{
										$trackingid="";
									}
									
									if($flight_mode=="N")
									{
										$str = "$toid|$order_details_reference|$trackingid|$secret_key";
									}
									else
									{
										$str = "$toid|$order_details_reference|$trackingid";
									}
									
									var_dump("str-----".$str);
									$generatedCheckSum = md5($str);
									
									/*$jsonRequest='{"toId": '.$toid.',"checkSum": "'.$generatedCheckSum.'","description": "'.$order_details_reference.'","trackingId": ""}';	*/
									
									
									
									
								//start tocken
	
									$SatcomServer_Protocol = Configuration::get("SatcomServer_Protocol");
									$SatcomServer_IP = Configuration::get("SatcomServer_IP");
									$SatcomServer_Port = Configuration::get("SatcomServer_Port");
									$Token_APPID = Configuration::get("Token_APPID");
									$Token_Prename = Configuration::get("Token_Prename");
									$Token_Name = Configuration::get("Token_Name");
									
									$satcomurl =$SatcomServer_Protocol."://".$SatcomServer_IP.":".$SatcomServer_Port."/tocken/signin/".$Token_APPID."/".$Token_Prename."/".$Token_Name;
									//var_dump("url: ".$satcomurl);
									
									$ssl= _PS_BASE_URL_ .__PS_BASE_URI__."modules/paymentz/ssl.cer";
									//$url = 'http://domain.com/get-post.php';
									$ch = curl_init();
									curl_setopt($ch,CURLOPT_URL, $satcomurl);
									curl_setopt($ch, CURLOPT_CAINFO, $ssl);
									curl_setopt($ch, CURLOPT_VERBOSE, true);
									curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
									curl_setopt($ch,CURLOPT_POST,0);
									$result = curl_exec($ch);
									$SatComjsonResponse=json_decode($result, true);			
									curl_close($ch);
							
								//	var_dump("json Response".$SatComjsonResponse);
							$Token=$SatComjsonResponse['Token'];
							//end token
							

							
							$GroundServer_Protocol = Configuration::get("GroundServer_Protocol");
							$GroundServer_Host = Configuration::get("GroundServer_Host");
							$GroundServer_Path = Configuration::get("GroundServer_Path");
							$VPS_Path = Configuration::get("VPS_Path");
							
									
							$jsonRequest='{"method": "POST","host": "'.$GroundServer_Host.'","path": "'.$GroundServer_Path.'","protocol": "'.$GroundServer_Protocol.'","header": {"Content-Type": "application/json",  "cache-control": "no-cache","initiator": "PaxLife-Satcom" ,"accept":"application/json"},"body": {"toId": '.$toid.',"checkSum": "'.$generatedCheckSum.'","description": "'.$order_details_reference.'","trackingId": '.$trackingid.'}}';	
							
							$url =$SatcomServer_Protocol."://".$SatcomServer_IP.":".$SatcomServer_Port."/".$VPS_Path;
							
									$ch = curl_init();
									//$url=Configuration::get('StatusUrl');
									$ssl= _PS_BASE_URL_ .__PS_BASE_URI__."modules/paymentz/ssl.cer";
									curl_setopt($ch,CURLOPT_URL, $url);
									curl_setopt($ch, CURLOPT_CAINFO, $ssl);
									curl_setopt($ch, CURLOPT_VERBOSE, true);
									curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
									curl_setopt($ch,CURLOPT_POST,1);
									curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonRequest);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									
									curl_setopt($ch, CURLOPT_HTTPHEADER, array(
										'Content-Type: application/json',
										'Accept: application/json',
										'Authorization: AUTH '.$Token
									));
									$result = curl_exec($ch);
									$jsonResponse=json_decode($result, true);
									//echo "***************************************************";
									//var_dump($jsonResponse);
									//echo "***************************************************";
									  $status=$jsonResponse['status'];
										$trackingId=$jsonResponse['trackingId'];
										
									if($status=="authsuccessful" || $status=="capturesuccess" || $status=="settled" || $status=="markedforreversal" || $status=="reversed" || $status=="chargeback")
									{
										$iSucessStaus=$iSucessStaus+1;
										if(isset($trackingId))
										{
											$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='$status',`tracking_id`='$trackingId' where id='$id'";
											$result = Db::getInstance()->executeS($updateSQL);
										}
										else
										{
											$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='$status' where id='$id'";
											$result = Db::getInstance()->executeS($updateSQL);
										}
										
									}										
									
									else if($status=="authstarted" || $status=="proofrequired")
									{
										$iPendingStaus=$iPendingStaus+1;
										if(isset($trackingId))
										{
											$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='$status',`tracking_id`='$trackingId' where id='$id'";
											$result = Db::getInstance()->executeS($updateSQL);
										}
										else
										{
											$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='$status' where id='$id'";
											$result = Db::getInstance()->executeS($updateSQL);
										}
										
										
									}
									else if($status=="" || $status==null)
									{
										
										if($connection_mode=='on' && $current_state==$Awaiting_Paymentz_payment)
										{
											$iFailedStaus=$iFailedStaus+1;
											if(isset($trackingId))
											{
									
												$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='failed',`tracking_id`='$trackingId' where id='$id'";
												$result = Db::getInstance()->executeS($updateSQL);
											}
											else
											{
												$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='failed' where id='$id'";
												$result = Db::getInstance()->executeS($updateSQL);
											}
										
											
										}
										
										if($connection_mode=='off' && ($current_state==$Awaiting_Paymentz_payment || $current_state==$Paymentz_Payment_Success ))
										{
											$iFailedStaus=$iFailedStaus+1;
											if(strtotime($date_add)<strtotime('-'.$delay_days.' days'))
											{
												 //recorde is older than 5 days.
												 
												 $iFailedStaus=$iFailedStaus+1;
												if(isset($trackingId))
												{
												
													
													$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='failed',`tracking_id`='$trackingId' where id='$id'";
													$result = Db::getInstance()->executeS($updateSQL);
												}
												else
												{
													$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='failed' where id='$id'";
													$result = Db::getInstance()->executeS($updateSQL);
												}
											
												
												
											 }

										}
										
										
									}
									else 
									{
										$iFailedStaus=$iFailedStaus+1;
										if(isset($trackingId))
										{
											$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='failed',`tracking_id`='$trackingId' where id='$id'";
											$result = Db::getInstance()->executeS($updateSQL);
										}
										else
										{
											$updateSQL = "update "._DB_PREFIX_."paymentz_orderdetails set `status`='failed' where id='$id'";
											$result = Db::getInstance()->executeS($updateSQL);
										}
										
									}
									
									
								}
							//update order status and all fields here.
							
							if($iSucessStaus>0 && $iFailedStaus==0 && $iPendingStaus==0)
							{

								$finalStatus ='SUCCESS';
								
								$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Success',`isProcessed`='1' where id_order='$id_order'";
								$result = Db::getInstance()->executeS($updateSQL);

							}
							else if(($iSucessStaus>0 && $iFailedStaus>0 && $iPendingStaus==0) ||($iSucessStaus>0 && $iFailedStaus==0 && $iPendingStaus>0) || ($iSucessStaus>0 && $iFailedStaus>0 && $iPendingStaus>0))
							{

								$finalStatus ='PARTIAL_SUCCESS';
								$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Partial_Success',`isProcessed`='1' where id_order='$id_order'";
								$result = Db::getInstance()->executeS($updateSQL);

							}
							else if(($iSucessStaus==0 && $iFailedStaus>0))
							{

								$finalStatus ='FAILED';
								$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Paymentz_Payment_Failed',`isProcessed`='1' where id_order='$id_order'";
								$result = Db::getInstance()->executeS($updateSQL);

							}
							else if(($iSucessStaus==0 && $iFailedStaus==0 && $iPendingStaus>0))
							{

								$finalStatus ='PENDING';
								$updateSQL = "update "._DB_PREFIX_."orders set `current_state`='$Awaiting_Paymentz_payment',`isProcessed`='1' where id_order='$id_order'";
								$result = Db::getInstance()->executeS($updateSQL);

							}
						
						
					}
					else if($split_member=="N")
					{
						
							//$str = "$toid|$reference|$trackingid|0P1VSBlEkOzPUJBL2Px6UgR5PmQLHf3O";
							
							if($trackingid==NULL || $trackingid=="")
							{
								$trackingid="";
							}
							
							if($flight_mode=="N")
							{
								$str = "$toid|$reference|$trackingid|$secret_key";
							}
							else
							{
								$str = "$toid|$reference|$trackingid";
							}
							
							
							$generatedCheckSum = md5($str);
							
							$jsonRequest='{"toId": '.$toid.',"checkSum": "'.$generatedCheckSum.'","description": "'.$reference.'","trackingId": '.$trackingid.'}';	

							$ch = curl_init();
							//$url="https://staging.paymentz.com/transactionServices/RESTful/DirectTransaction/status";
							$url=Configuration::get('StatusUrl');
							$ssl= _PS_BASE_URL_ .__PS_BASE_URI__."modules/paymentz/ssl.cer";
							curl_setopt($ch,CURLOPT_URL, $url);
							curl_setopt($ch, CURLOPT_CAINFO, $ssl);
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
							//echo "***************************************************";
							//var_dump($jsonResponse);
							//echo "***************************************************";
							$status=$jsonResponse['status'];
							$trackingId=$jsonResponse['trackingId'];
							if($status=="authsuccessful" || $status=="capturesuccess")
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
		/*}
		else
		{
			//no net connection.
		}*/
		
		
		
		
		
			
?>