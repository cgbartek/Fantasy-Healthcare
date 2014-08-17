<?php 
//error_reporting(E_ERROR);
session_start();
include('functions.php');
$json = 1;
$token = $_SESSION["tok"];
$uid = $_SESSION["uid"];
$lid = $_SESSION["lid"];
$u = $_SESSION["u"];
if(!$uid || !$lid){
	header('Location: m/');die();
}
$name = $_POST['createteam-name'];
$icon = sanitize($_POST['createteam-icon']);
$iconImg = $_POST['createteam-img'];



if(strlen($name) < 3) {
	header('Location: m/?error=Name%20is%20too%20short#createteam');die();
}

if((!empty($_FILES["createteam-img"])) && ($_FILES['createteam-img']['error'] == 0)) {
	$uploaded = 1;
}

$sql = "SELECT * FROM `teams` WHERE `name`='$name' AND `lid`='$lid';";
$result = $db->query($sql);
if($result->num_rows){
	header('Location: m/?error=Team%20already%20exists#createteam');die();
}

//File upload
if((!empty($_FILES["createteam-img"])) && ($_FILES['createteam-img']['error'] == 0)) { //Сheck that we have a file
  $filename = basename($_FILES['createteam-img']['name']); //Check if the file is JPEG image and its size is less than 350Kb
  $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
  if (($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "pdf" || $ext == "gif") && 
    ($_FILES["createteam-img"]["size"] < 1000000)) { //Determine the path to which we want to save this file
      
	$sql = "INSERT INTO `teams` (lid,uid,name,logo) VALUES ('$lid', '$uid', '$name', '');";
	$result = $db->query($sql);
	$insertId = $db->insert_id;
	$logo = $insertId . '_thumb';
	$sql = "UPDATE `teams` SET logo='$logo' WHERE tid='$insertId'";
	$updateresult = $db->query($sql);
	  
	  $newname = dirname(__FILE__).'/uploads/'.$insertId.'.'.$ext; //Check if the file with the same name is already exists on the server
	  if (!file_exists($newname)) { //Attempt to move the uploaded file to its new place
        if ((move_uploaded_file($_FILES['createteam-img']['tmp_name'],$newname))) {
			if($ext == "jpg" || $ext == "jpeg"){
				$im = ImageCreateFromJpeg($newname);
			}
			if($ext == "png"){
				$im = ImageCreateFromPng($newname);
			}
			if($ext == "gif"){
				$im = ImageCreateFromGif($newname);
			}
			$ox = imagesx($im);
			$oy = imagesy($im);
			$height = 200;
			$width = 200;
			if($ox < $oy) // portrait
			{
			   $ny = $height;
			   $nx = floor($ox * ($ny / $oy)); 
			} 
			else // landscape
			{
			   $nx = $width;
			   $ny = floor($oy * ($nx / $ox)); 
			} 
			$nm = imagecreatetruecolor($nx, $ny);
			imagecopyresized($nm, $im, 0, 0, 0, 0, $nx, $ny, $ox, $oy);
			imagejpeg($nm, dirname(__FILE__).'/uploads/'.$insertId."_thumb.jpg", 100);
			
			$uploaded = TRUE;
			
        } else {
			header('Location: m/?error=Problem%20occurred%20during%20upload#createteam');die();
        }
      } else {
		header('Location: m/?error=Already%20exists#createteam');die();
      }
  } else {
	header('Location: m/?error=Wrong%20filetype%20or%20too%20large#createteam');die();
  }
} else {
	header('Location: m/?error=No%20file%20uploaded#createteam');die();
}

// Base64
if($base64) {
	$img = $base64;
	$data = base64_decode($img);
	$file = dirname(__FILE__).'/uploads/'.$insertId.'.jpg';
	$success = file_put_contents($file, $data);
	if($success){
		$uploaded = TRUE;
	}
}

header('Location: m/?success=Upload%20successful#dashboard-signin');die();
?>