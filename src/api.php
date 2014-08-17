<?php // API for internal AJAX calls
date_default_timezone_set('America/New_York');
header("Access-Control-Allow-Origin: *");
include_once('functions.php');
$args = $_REQUEST;
$json = $args['json'];
$uid = $_SESSION['uid'];
$lid = $_SESSION['lid'];
$rid = $_SESSION['rid'];
$tid = $_SESSION['tid'];
$gid = $_SESSION['gid'];
$u = $_SESSION['u'];
$tok = $_SESSION['tok'];

if(!$args['action']) {
	$return['error'] = 'No action requested.';
	if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
}

if($db->connect_errno){
    $return['error'] = strip_tags($db->connect_error);
	if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
}

// 'SUP? - AJAX polling request from MMF front end
if($args["action"] == "sup"){
	$u = sanitize($args["u"]);
	$token = sanitize($args["tok"]);
	if(!$token){
		$return['error'] = "Missing token.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "SELECT * FROM `queue` WHERE token='$token'";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if($row['action']) {
		$return['success'] = $row['action'];
		$sql = "DELETE FROM `queue` WHERE `token`='$token';";
		$result = $db->query($sql);
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	} else {
		$return['success'] = "nm";
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
}

// QUEUE - Add to the 'sup stack to be intercepted by MMF
if($args["action"] == "queue"){
	$token = sanitize($args["tok"]);
	$action = sanitize($args["do"]);
	$sql = "DELETE FROM `queue` WHERE `token`='$token';";
	$result = $db->query($sql);
	$sql = "INSERT INTO `queue` (token,action) VALUES ('$token','$action');";
	$result = $db->query($sql);
}

// LOGIN
if($args["action"] == "login"){
	$u = sanitize($args["u"]);
	$p = sanitize($args["p"]);
	$pmd5 = md5($p);
	$sql = "SELECT * FROM `users` WHERE email='$u' AND password='$pmd5';";
	if(!$result = $db->query($sql)){
		$return['error'] = 'Error running query.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$row = $result->fetch_assoc();
	if($result->num_rows) {
		$return['success'] = md5(strtolower($u.$p.'m4c4r00n'));
		$_SESSION['u'] = $row['email'];
		$_SESSION['uid'] = $row['uid'];
		$_SESSION['tok'] = $return['success'];
		$lastlogin = date('Y-m-d H:i:s',strtotime("now"));
		$ip = $_SERVER['REMOTE_ADDR'];
		$sql = "UPDATE `users` SET ip='$ip',lastlogin='$lastlogin' WHERE uid='$row[uid]'";
		$result = $db->query($sql);
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	} else {
		$return['error'] = 'Sorry, your username or password is incorrect. Please try again.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
}

// SIGNUP
if($args["action"] == "signup"){
	$f = sanitize($args["f"]);
	$l = sanitize($args["l"]);
	$e = sanitize($args["e"]);
	$e2 = sanitize($args["e2"]);
	$p = sanitize($args["p"]);
	$p2 = sanitize($args["p2"]);
	$pmd5 = md5($p);
	$sql = "SELECT * FROM `users` WHERE email='$e';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if(!$result->num_rows) {
		if((strlen($f) < 2) || (strlen($l) < 2)) {
			$return['error'] = 'Names must be 2 characters or more.';
			if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
		}
		if($e != $e2) {
			$return['error'] = 'Email addresses do not match. Please re-enter.';
			if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
		}
		if(strlen($e) < 6) {
			$return['error'] = 'Email must be 6 characters or more.';
			if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
		}
		if($p != $p2) {
			$return['error'] = 'Passwords do not match. Please re-enter.';
			if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
		}
		if(strlen($p) < 6) {
			$return['error'] = 'Password must be 6 characters or more.';
			if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
		}
		$created = date('Y-m-d H:i:s',strtotime("now"));
		$ip = $_SERVER['REMOTE_ADDR'];
		$sql = "INSERT INTO `users` (rid,utype,email,password,firstname,lastname,created,ip) VALUES ('1','1','$e','$pmd5','$f','$l','$created','$ip');";
		$result = $db->query($sql);
		$insertid = $db->insert_id;
		if(!$insertid) {
			$return['error'] = 'Error adding user to database.';
			if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
		}
		$return['success'] = "OK";
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	} else {
		$return['error'] = 'Sorry, your username has been taken. Please try a different email address.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
}

// LOGOUT
if($args["action"] == "logout"){
	unset($_SESSION);
	session_destroy();
	$return['success'] = "OK";
	if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
}

// GET LEAGUES
if($args["action"] == "getleagues"){
	$sql = "SELECT * FROM `leagues` ORDER BY name;";
	$result = $db->query($sql);
	if($result->num_rows) {
		$return['success'] = "OK";
		while($row = $result->fetch_assoc()) {
			$pass = '';
			if($row['password']){
				$pass = '*';
			}
			$return[$row['lid'].'L'.$pass] = $row['name'];
		}
		die(json_encode($return));
	} else {
		$return['error'] = 'Sorry, no results were returned.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
}

// SET LEAGUE
if($args["action"] == "setleague"){
	$selected = sanitize($args["selected"]);
	$password = sanitize($args["password"]);
	if($password){
		$pmd5 = md5($password);
	}
	$sql = "SELECT * FROM `leagues` WHERE lid='$selected' AND password='$pmd5';";
	$result = $db->query($sql);
	if($result->num_rows) {
		$row = $result->fetch_assoc();
		$_SESSION['lid'] = $selected;
		$_SESSION['rid'] = 1;//$row['rid']; //why doesn't this work?
		$_SESSION['tid'] = 0;
		$_SESSION['gid'] = 0;
		$return['lid'] = $row['lid'];
		$return['uid'] = $row['uid'];
		$return['name'] = $row['name'];
		$return['rid'] = $row['rid'];
		$return['status'] = $row['status'];
		$return['start'] = strtotime($row['start']);
		$sql = "UPDATE `users` SET lid='$selected' WHERE uid='$uid'";
		$result = $db->query($sql);
		$return['success'] = $selected;
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	} else {
		$return['error'] = 'Access was denied. Please try entering your password again.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
}

// REJOIN LEAGUE
if($args["action"] == "rejoin"){
	$sql = "SELECT * FROM `leagues` WHERE lid='$lid';";
	$result = $db->query($sql);
	if($result->num_rows) {
		$row = $result->fetch_assoc();
		$_SESSION['lid'] = $lid;
		$_SESSION['rid'] = 1;
		$_SESSION['tid'] = 0;
		$_SESSION['gid'] = 0;
		$return['lid'] = $row['lid'];
		$return['uid'] = $row['uid'];
		$return['name'] = $row['name'];
		$return['rid'] = $row['rid'];
		$return['status'] = $row['status'];
		$return['start'] = strtotime($row['start']);
		$sql = "UPDATE `users` SET lid='$lid' WHERE uid='$uid'";
		$result = $db->query($sql);
		$return['success'] = $lid;
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	} else {
		$return['error'] = 'Access was denied. Please try entering your password again.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
}

// CREATE A LEAGUE
if($args["action"] == "createleague"){
	$name = sanitize($args['name']);
	$password = sanitize($args['password']);
	$region = sanitize($args['region']);
	$start = sanitize($args['start']);
	$start = date('Y-m-d H:i:s',strtotime($start));
	$pmd5 = "";
	if($password) {
		$pmd5 = md5($password);
	}
	$sql = "SELECT name FROM `leagues` WHERE name='$name';";
	$result = $db->query($sql);
	if(!$uid) {
		$return['error'] = 'Sorry, you are not signed in.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	if(!$result->num_rows && $name) {
		$sql = "INSERT INTO `leagues` (name,password,uid,rid,start) VALUES ('$name','$pmd5','$uid','$region','$start');";
		$result = $db->query($sql);
		$insertid = $db->insert_id;
		$_SESSION['lid'] = $insertid;
		$_SESSION['rid'] = 1;
		$_SESSION['tid'] = 0;
		$_SESSION['gid'] = 0;
		$sql = "UPDATE `users` SET lid='$insertid' WHERE uid='$uid'";
		$result = $db->query($sql);
		$return['success'] = $insertid;
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	} else {
		$return['error'] = 'Sorry, this league name already exists.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
}

// RESET LEAGUE
if($args["action"] == "leaguereset"){
	$sql = "SELECT lid FROM `leagues` WHERE uid='$uid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if(!$row['lid']) {
		$return['error'] = 'Only the league manager can reset a league.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	} else {
		$_SESSION['tid'] = 0;
		$_SESSION['gid'] = 0;
		$sql = "UPDATE `leagues` SET status='0',start='$start' WHERE lid='$lid'";
		$result = $db->query($sql);
		$sql = "DELETE FROM `teams` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$sql = "DELETE FROM `gamelogs` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$sql = "DELETE FROM `games` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$sql = "DELETE FROM `stack` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$return['success'] = "OK";
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
}

// DELETE LEAGUE
if($args["action"] == "leaguedelete"){
	$sql = "SELECT lid FROM `leagues` WHERE uid='$uid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if(!$row['lid']) {
		$return['error'] = 'Only the league manager can delete a league.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	} else {
		$_SESSION['lid'] = 0;
		$_SESSION['rid'] = 0;
		$_SESSION['tid'] = 0;
		$_SESSION['gid'] = 0;
		$sql = "DELETE FROM `leagues` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$sql = "DELETE FROM `teams` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$sql = "DELETE FROM `gamelogs` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$sql = "DELETE FROM `games` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$sql = "DELETE FROM `stack` WHERE `lid`='$lid';";
		$result = $db->query($sql);
		$return['success'] = "OK";
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
}

// DRAFT START
if($args["action"] == "draftstart"){
	$sql = "SELECT * FROM `leagues` WHERE lid='$lid' AND uid='$uid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if(!$row['lid']) {
		$return['error'] = 'Only the league manager can start the draft early.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	} else {
		$now = date('Y-m-d H:i:s',strtotime("now"));
		$sql = "UPDATE `leagues` SET start='$now' WHERE lid='$lid'";
		$updateresult = $db->query($sql);
		$return['success'] = "OK";
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
}

// DASHBOARD
if($args["action"] == "dashboard"){
	if(!$uid) {
		$return['error'] = 'Sorry, you are not signed in.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "SELECT * FROM `leagues` WHERE uid='$uid' AND lid='$lid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	$return['owner'] = $row['uid'];
	if(!$lid) {
		$return['success'] = '-1';
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
	if($lid) {
		$sql = "SELECT * FROM `teams` WHERE lid = '$lid' AND uid = '$uid';";
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		if($row['tid']) {
			$_SESSION['tid'] = $row['tid'];
			$return['success'] = $row['tid'];
			if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
		} else {
			$return['success'] = '-2';
			if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
		}
	}
	$return['error'] = 'Sorry, no results were returned.';
	if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
}

// DASHBOARD - GET LIST OF TEAMS IN LEAGUE
if($args["action"] == "getteams"){
	if(!$uid || !$lid) {
		$return['error'] = 'Sorry, you are not signed in.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "SELECT * FROM `teams` WHERE lid = '$lid' ORDER BY name;";
	$result = $db->query($sql);
	while($row = $result->fetch_assoc()) {
		$return['teams'][$row['tid']] = $row['name'];
	}
	$sql = "SELECT * FROM `leagues` WHERE lid = '$lid' AND status > '0';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if($row['lid']){
		$return['status'] = $row['status'];
	}
	$return['success'] = 'OK';
	if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
}

// DRAFT - GET PROVIDERS
if($args["action"] == "getproviders"){
	if(!$uid || !$rid || !$lid) {
		$return['error'] = "Sorry, you are not signed in.$uid - $rid - $lid";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	if($lid) {
		$sql = "SELECT * FROM `providers` WHERE rid = '$rid';";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()) {
			$return['providers'][$row['hid']] = $row['name'];
		}
		$return['success'] = 'OK';
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
	$return['error'] = 'Sorry, no results were returned.';
	if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
}

// DRAFT - GET PLAYERS
if($args["action"] == "getplayers"){
	$expertise = $args["expertise"];
	if(!$uid || !$rid || !$lid) {
		$return['error'] = "Sorry, you are not signed in.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	if($lid) {
		$sql = "SELECT * FROM `providers` WHERE rid = '$rid';";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()) {
			$return['providers'][$row['hid']] = $row['name'];
		}
		foreach ($return['providers'] as $k => $v){
			$sql = "SELECT * FROM `players` WHERE hid='$k' AND expertise='$expertise' AND off=0;";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc()) {
				$sql = "SELECT * FROM `teams` WHERE pids LIKE '%;$row[pid];%' AND lid='$lid';";
				$tresult = $db->query($sql);
				$trow = $tresult->fetch_assoc();
				if(!$trow['tid']){ // Make sure someone else didn't already select this player
					$return['players'][$row['pid']] = $row;
					$return['players'][$row['pid']]['provider'] = $v;
				}
			}
		}
		$return['expertise'] = ucfirst($args["expertise"]);
		$return['success'] = 'OK';
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
	$return['error'] = 'Sorry, no results were returned.';
	if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
}

// DRAFT - GET LEAGUE/DRAFT STATUS (delete?)
if($args["action"] == "getleaguestatus"){
	if(!$uid || !$lid) {
		$return['error'] = "Sorry, you are not signed in.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "SELECT * FROM `leagues` WHERE lid = '$lid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	$return['action'] = $row['action'];
	$return['param'] = $row['param'];
	$return['success'] = 'OK';
	if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
}

// DRAFT - GET STACK
if($args["action"] == "getstack"){
	if(!$tid) {
		$return['error'] = "Sorry, you are not signed in.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "SELECT * FROM `stack` WHERE lid='$lid' ORDER BY sid;";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if($row['target'] == $tid){
		$return['action'] = $row['action'];
		$return['param'] = $row['param'];
		$return['notes'] = $row['notes'];
		$return['success'] = $row['sid'];
		$_SESSION['sid'] = $row['sid'];
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	} else {
		$return['success'] = '-1';
		if(!$row['target']) {
			$sql = "SELECT * FROM `leagues` WHERE lid='$lid';";
			$lresult = $db->query($sql);
			$lrow = $lresult->fetch_assoc();
			if($lrow['status'] == 3) {
				$return['success'] = '-2';
			}
		}
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
}

// DRAFT - SET PICK (remove from stack)
if($args["action"] == "setpick"){
	$pid = sanitize($args['selected']);
	$sid = sanitize($args['stack']);
	if(!$tid) {
		$return['error'] = "Sorry, you are not signed in.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	if(!$pid) {
		$return['success'] = "-1";
		if($json){die(json_encode($return));} else {die('1|'.$return['error']);}
	}
	$sql = "SELECT * FROM `stack` WHERE sid = '$sid' AND target = '$tid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if(!$row['sid']){
		$return['error'] = "Too late. Already picked for you.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
$sql = "SELECT * FROM `teams` WHERE tid='$tid';";
$teamresult = $db->query($sql);
$teamrow = $teamresult->fetch_assoc();
$pids = substr($teamrow['pids'],0,-1);
$pids = $pids.";".$pid.";";
$sql = "UPDATE `teams` SET pids='$pids' WHERE tid='$tid'";
$updateresult = $db->query($sql);
	$sql = "DELETE FROM `stack` WHERE `sid`='$sid';";
	$result = $db->query($sql);
	$return['success'] = 'OK';
	if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
}

// GAME - GET STATUS
if($args["action"] == "getgamestatus"){
	if(!$tid) {
		$return['error'] = "Sorry, you are not signed in.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "SELECT * FROM `teams` WHERE tid='$tid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if($row['gid']) {
		$sql = "SELECT * FROM `games` WHERE gid='$row[gid]' AND open=1 AND `join`=1 AND winner=0;";
		$gameresult = $db->query($sql);
		$gamerow = $gameresult->fetch_assoc();
		if($gamerow['gid']) {
			$return['success'] = $row['gid'];
			$_SESSION['gid'] = $row['gid'];
			$return['tidh'] = $gamerow['tidh'];
			$return['tidv'] = $gamerow['tidv'];
			$return['hpts'] = $gamerow['hpts'];
			$return['vpts'] = $gamerow['vpts'];
			$return['hbpts'] = $gamerow['hbpts'];
			$return['vbpts'] = $gamerow['vbpts'];
			$return['winner'] = $gamerow['winner'];
			$sql = "SELECT * FROM `teams` WHERE tid='$gamerow[tidh]';";
			$teamresult = $db->query($sql);
			$teamrow = $teamresult->fetch_assoc();
			$return['tidhname'] = $teamrow['name'];
			$return['tidhlogo'] = $teamrow['logo'];
			$return['tidhpids'] = explode(";",$teamrow['pids']);
			$sql = "SELECT * FROM `teams` WHERE tid='$gamerow[tidv]';";
			$teamresult = $db->query($sql);
			$teamrow = $teamresult->fetch_assoc();
			$return['tidvname'] = $teamrow['name'];
			$return['tidvlogo'] = $teamrow['logo'];
			$return['tidvpids'] = explode(";",$teamrow['pids']);
			$return['hpt'] = 0;
			$return['vpt'] = 0;
			if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
		} else {
			// game isn't done being set up
			$return['success'] = '-2';
			if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
		}
	} else {
		$return['success'] = '-1';
		$sql = "SELECT * FROM `games` WHERE lid='$lid' AND winner=0;";
		$winresult = $db->query($sql);
		$winrow = $winresult->fetch_assoc();
		if(!$winrow){
			$return['success'] = '-3';
			$sql = "SELECT * FROM `leagues` WHERE uid='$uid' AND lid='$lid';";
			$result = $db->query($sql);
			$row = $result->fetch_assoc();
			$return['owner'] = $row['uid'];
		}
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	}
}

// GAME - GET GAME LOG
if($args["action"] == "getgamelog"){
	if(!$gid) {
		$return['error'] = "Sorry, you are not signed in.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "SELECT glid,note,won FROM `gamelogs` WHERE gid='$gid' ORDER BY glid;";
	$result = $db->query($sql);
	while ($row = $result->fetch_assoc()) {
		$return['logs'][] = $row;
	}
	$sql = "SELECT gid FROM `teams` WHERE tid='$tid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	$return['gid'] = $row['gid'];
	$return['success'] = 'OK';
	$return['hpt'] = 0;
	$return['vpt'] = 0;
	$sql = "SELECT * FROM `gamelogs` WHERE gid='$gid' AND won='h';";
	$logresult = $db->query($sql);
	while ($logrow = $logresult->fetch_assoc()) {
		$return['hpt'] += $logrow['pts'];
	}
	$sql = "SELECT * FROM `gamelogs` WHERE gid='$gid' AND won='v';";
	$logresult = $db->query($sql);
	while ($logrow = $logresult->fetch_assoc()) {
		$return['vpt'] += $logrow['pts'];
	}
	if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
}

// GAME - GET FINAL
if($args["action"] == "getfinal"){
	if(!$uid) {
		$return['error'] = "Sorry, you are not signed in.";
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "SELECT * FROM `teams` WHERE lid='$lid';";
	$result = $db->query($sql);
	while ($row = $result->fetch_assoc()) {
		$team[$row['tid']] = $row;
	}
	$sql = "SELECT * FROM `games` WHERE lid='$lid' ORDER BY gid;";
	$result = $db->query($sql);
	while ($row = $result->fetch_assoc()) {
		$hname = $team[$row['tidh']]['name'];
		$hlogo = $team[$row['tidh']]['logo'];
		$vname = $team[$row['tidv']]['name'];
		$vlogo = $team[$row['tidv']]['logo'];
		$wname = $team[$row['winner']]['name'];
		$wlogo = $team[$row['winner']]['logo'];
		$row['note'] = '&nbsp;<img src="../uploads/'.$hlogo.'.jpg" style="vertical-align:middle;width:48px;height:48px;" alt=""> vs. <img src="../uploads/'.$vlogo.'.jpg" style="vertical-align:middle;width:48px;height:48px;" alt=""> &ndash; Winner: <img src="../uploads/'.$wlogo.'.jpg" style="vertical-align:middle;width:48px;height:48px;" alt="">';
		$return['games'][] = $row;
		$winners[$row['winner']] += 1;
	}
	arsort($winners);
	reset($winners);
	$winner = key($winners);
	$return['winners'] = $winners;
	$return['winner'] = $winner;
	$return['wname'] = $team[$winner]['name'];
	$return['wlogo'] = $team[$winner]['logo'];
	$return['wwins'] = $winners[$winner];
	$return['success'] = "OK";
	if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
}

// INVITE FRIENDS
if($args["action"] == "invitefriends"){
	$message = sanitize($args['invitefriends-comment']);
	$email = sanitize($args['invitefriends-email']);
	$name = sanitize($args['invitefriends-name']);
	$sql = "SELECT * FROM `users` WHERE uid='$uid';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if($row['uid']) {
		emailTo('You have been invited to play Fantasy Healthcare!',$message,$email,$name);
		$return['success'] = "OK";
		if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
	} else {
		$return['error'] = 'Sorry, you are not signed in.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
}

// GET TRIVIA
if($args["action"] == "gettrivia"){
	$sql = "SELECT * FROM `trivia` ORDER BY RAND() LIMIT 10;";
	$result = $db->query($sql);
	if($result->num_rows) {
		$return['success'] = "OK";
		$num = 0;
		while($row = $result->fetch_assoc()) {
			$num++;
			$return[$num]['qid'] = $row['qid'];
			$return[$num]['expertise'] = $row['expertise'];
			$return[$num]['q'] = $row['question'];
			if(!$row['a1']) {
				$row['a1'] = "True";
			}
			if(!$row['a2']) {
				$row['a2'] = "False";
			}
			$return[$num]['a1'] = $row['a1'];
			$return[$num]['a2'] = $row['a2'];
			$return[$num]['a3'] = $row['a3'];
			$return[$num]['a4'] = $row['a4'];
		}
		die(json_encode($return));
	} else {
		$return['error'] = 'Sorry, no results were returned.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
}

// GET TRIVIA ANSWERS
if($args["action"] == "gettriviacheck"){
	$form = $args['form'];
	$return['success'] = "OK";
	foreach($form as $k => $v) {
		$sql = "SELECT * FROM `trivia` WHERE qid='$v[name]';";
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		$return[$v['name']]['answer'] = $v['value'];
		$return[$v['name']]['correct'] = $row['answer'];
		$sql = "SELECT * FROM `games` WHERE gid='$gid' AND (tidh='$tid' OR tidv='$tid');";
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		if($row['gid']) {
			if($row['tidh'] == $tid) {
				$sql = "UPDATE `games` SET hbpts=hbpts+10 WHERE gid='$gid'";
			}
			if($row['tidv'] == $tid) {
				$sql = "UPDATE `games` SET vbpts=vbpts+10 WHERE gid='$gid'";
			}
			$updateresult = $db->query($sql);
		}
	}
	die(json_encode($return));
}

// LOG TO GAMELOG
if($args["action"] == "log"){
	$note = sanitize($args['note']);
	$action = sanitize($args['action']);
	$param = sanitize($args['param']);
	if(!$uid || !$lid) {
		$return['error'] = 'Sorry, you are not signed in.';
		if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
	}
	$sql = "INSERT INTO `gamelogs` (lid,action,param,note) VALUES ('$lid','$action','$param','$note');";
	$result = $db->query($sql);
	$insertid = $db->insert_id;
	$return['success'] = $insertid;
	if($json){die(json_encode($return));} else {die('1|'.$return['success']);}
}

?>