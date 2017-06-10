<?php
require('../../config/config.inc.php');
/*$url = _PS_BASE_URL_ .__PS_BASE_URI__."modules/paymentz/statusupdate.php";
$c_min=$_REQUEST['c_min'];
$c_hrs=$_REQUEST['c_hrs'];
$c_dayofmonth=$_REQUEST['c_dayofmonth'];
$c_month=$_REQUEST['c_month'];
$c_dayofweek=$_REQUEST['c_dayofweek'];*/

$url ="modules/paymentz/statusupdate.php";
$c_min="1";
$c_hrs="4";
$c_dayofmonth="3";
$c_month="2";
$c_dayofweek="";

if($c_min!="" && $c_min!=null)
{
	$c_min='*/'.$c_min;
}
else
{
	$c_min='*';
}
if($c_hrs!="" && $c_hrs!=null)
{
	$c_hrs='*/'.$c_hrs;
}
else
{
	$c_hrs='*';
}
if($c_dayofmonth!="" && $c_dayofmonth!=null)
{
	$c_dayofmonth='*/'.$c_dayofmonth;
}
else
{
	$c_dayofmonth='*';
}
if($c_month!="" && $c_month!=null)
{
	$c_month='*/'.$c_month;
}
else
{
	$c_month='*';
}
if($c_dayofweek!="" && $c_dayofweek!=null)
{
	$c_dayofweek='*/'.$c_dayofweek;
}
else
{
	$c_dayofweek='*';
}

//$file = file_get_contents("crontab.txt");
$output = shell_exec('crontab -l');
$lines = explode("\n", $output);
$exclude = array();
foreach ($lines as $line) {
    if (strpos($line, 'statusupdate.php') !== FALSE) {
         continue;
    }
    $exclude[] = $line;
}
$putme=implode("\n", $exclude);
file_put_contents('crontab.txt', $putme.$c_min.' '.$c_hrs.' '.$c_dayofmonth.' '.$c_month.' '.$c_dayofweek.' /usr/bin/php'.' '.$url.PHP_EOL);
exec('crontab /tmp/crontab.txt');

?>
