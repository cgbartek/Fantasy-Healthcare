<?php
error_reporting(E_ERROR);
session_start();
if($loginRequired && !$_SESSION && !$json){
	header("Location: login?r=".str_replace(".php","",basename($_SERVER['PHP_SELF'])));
	die();
}

$db = new mysqli('localhost', 'username', 'password', 'database');
if($db->connect_errno){
    kill('Unable to connect [' . $db->connect_error . ']');
}

// MISC FUNCTIONS
function slugify($text){
    $text = preg_replace('/[^\\pL\d]+/u', '-', $text);  // Swap out Non "Letters" with a -
    $text = trim($text, '-'); // Trim out extra -'s
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text); // Convert leftovers to ASCII
    $text = strtolower($text); // Make text lowercase
    $text = preg_replace('/[^-\w]+/', '', $text); // Strip out everything else
    return $text;
}

function sanitize($text){
	global $db;
	if($db){
		$text = $db->escape_string($text);
	}
    return trim($text);
}

function makeInt($text){
    return trim(preg_replace("/[^0-9,.]/", "", $text));
}

function kill($msg){
	global $json;
	if(!$json){
		if(!@include_once('header.php')) {
			include_once('header.php');
		}
		echo $msg;
		include('footer.php');
		die();
	} else {
		header("Access-Control-Allow-Origin: *");
		$return['error'] = $msg;
		die(json_encode($return));
	}
}

function emailTo($subject,$msg,$email,$name){
	$message = "
	<html>
	<head>
	  <title>$subject</title>
	</head>
	<body>
	  <p>$msg</p>
	</body>
	</html>
	";
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "To: $name <$email>" . "\r\n";
	$headers .= 'From: Fantasy Healthcare <noreply@domain.com>' . "\r\n";
	
	// Mail it
	mail($to, $subject, $message, $headers);
}

function genPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

// Sort array by specific key's values
function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

function dateTime($str) {
    list($date, $time) = explode(' ', $str);
    list($year, $month, $day) = explode('-', $date);
    list($hour, $minute, $second) = explode(':', $time);
    $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
    return $timestamp;
}  
?>