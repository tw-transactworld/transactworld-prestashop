<?php
require('../../config/config.inc.php');

//curl code..
			$url = _PS_BASE_URL_ .__PS_BASE_URI__."modules/transactworld/statusupdate.php";
			//$url = 'http://domain.com/get-post.php';
			$ssl= _PS_BASE_URL_ .__PS_BASE_URI__."modules/transactworld/ssl.cer";
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CAINFO, $ssl);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch,CURLOPT_POST,0);
			$result = curl_exec($ch);
			//close connection
			curl_close($ch);
			//curl code ends.
			
			if (file_exists("LessonsLog.txt")==false)
			{
				$myfile = fopen("LessonsLog.txt", "w") or die("Unable to open file!");
				$txt = date('Y-m-d H:i:s'). " " . $result . PHP_EOL;
				fwrite($myfile, $txt);
				fclose($myfile);
			}
			else
			{
				$myfile = fopen("LessonsLog.txt", "a") or die("Unable to open file!");
				$txt = date('Y-m-d H:i:s'). " " . $result . PHP_EOL;
				fwrite($myfile, $txt);
				fclose($myfile);
			}
	
			$this->_html .= $this->displayConfirmation($this->l('Running Cron'));
?>