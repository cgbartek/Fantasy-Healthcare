<?php // Admin Tools
error_reporting(E_ERROR);
date_default_timezone_set('America/New_York');
session_start();
$args = $_REQUEST;
$uid = $_SESSION['uid'];
$lid = $_SESSION['lid'];
$u = $_SESSION['u'];
$tok = $_SESSION['tok'];
include_once('functions.php');

// UPDATE PLAYER STATS (run this after making changes to providers table)
if($args['action'] == "updateplayers"){
	$sql = "SELECT * FROM `providers`";
	$result = $db->query($sql);
	while($row = $result->fetch_assoc()){
		$providers[$row['hid']] = $row;
	}
	$sql = "SELECT * FROM `players` WHERE hid > 0;";
	$result = $db->query($sql);
	while($row = $result->fetch_assoc()){
		$off = "";
		$rank = $providers[$row['hid']][$row['expertise']];
		if($providers[$row['hid']][$row['expertise']] == 0) {
			$off = ", off='1'";
		}
		$sql = "UPDATE `players` SET rank='$rank'$off WHERE pid='$row[pid]'";
		$result2 = $db->query($sql);
	}
	//print_r($providers);
	echo "OK";
}

// RESET ALL DRAFTS
if($args['action'] == "resetdrafts"){
	$sql = "TRUNCATE TABLE stack";
	$result = $db->query($sql);
	$sql = "UPDATE `teams` SET pids=''";
	$result = $db->query($sql);
	$sql = "UPDATE `leagues` SET status=1";
	//$result = $db->query($sql);
	echo "OK";
}

// RESET ALL GAMES
if($args['action'] == "resetgames"){
	$sql = "TRUNCATE TABLE games";
	$result = $db->query($sql);
	$sql = "TRUNCATE TABLE gamelogs";
	$result = $db->query($sql);
	$sql = "UPDATE `teams` SET gid=0";
	$result = $db->query($sql);
	echo "OK";
}
?>