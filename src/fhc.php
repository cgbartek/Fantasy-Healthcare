<?php // Main FHC Gameplay Loop (set up in CRON)
date_default_timezone_set('America/New_York');
header("Access-Control-Allow-Origin: *");
include_once('functions.php');

echo "START<br>";

// Check for database connection
if($db->connect_errno){
    $return['error'] = strip_tags($db->connect_error);
	if($json){die(json_encode($return));} else {die('0|'.$return['error']);}
}

// Get team info
$sql = "SELECT * FROM `teams`;";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	$team[$row['tid']]['name'] = $row['name'];
	$team[$row['tid']]['lid'] = $row['lid'];
	$team[$row['tid']]['uid'] = $row['uid'];
	$team[$row['tid']]['pids'] = json_decode($row['pids']);
}

// Get player info
$sql = "SELECT * FROM `players` WHERE hid > 0;";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	$player[$row['pid']]['hid'] = $row['hid'];
	$player[$row['pid']]['firstname'] = $row['firstname'];
	$player[$row['pid']]['lastname'] = $row['lastname'];
	$player[$row['pid']]['expertise'] = $row['expertise'];
	$player[$row['pid']]['sex'] = $row['sex'];
	$player[$row['pid']]['rank'] = $row['rank'];
}

// Get provider info
$sql = "SELECT * FROM `providers`;";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	$provider[$row['hid']]['rid'] = $row['rid'];
	$provider[$row['hid']]['name'] = $row['name'];
	$provider[$row['hid']]['shortname'] = $row['shortname'];
}

// Get region info
$sql = "SELECT * FROM `regions`;";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	$region[$row['rid']]['name'] = $row['name'];
	$region[$row['rid']]['set'] = explode(",",$row['set']);
}

// set up random array of CPU names
$cpunames = array("Lions","Tigers","Bears","Sharks","Chickens","Bulldogs","Torties","Tabbies","Androids","Bobcats","Lynxes","Foxes","Aardvarks","Coyotes","Wolves","Weasels","Baboons","Cows","Turkeys","Meese","Squids","Neematoads","Quail","Snipe","Snakes","Spidermonkeys","Platypi","Skunks");
$cpus = array_rand($cpunames, 7);

// Find games that need to be started
$sql = "SELECT * FROM `leagues` WHERE status = 0;";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	if(strtotime($row['start']) <= strtotime('now')) {
		$sql = "UPDATE `leagues` SET status='1' WHERE lid='$row[lid]'";
		$updateresult = $db->query($sql);
	}
}

// loop through leagues and set status
$sql = "SELECT * FROM `leagues` WHERE status = 1;";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	// game has started
	if(strtotime($row['start']) <= strtotime('now')){
		// no action yet, so generate stack
		if($row['action']==""){
			// make sure there are enough teams
			$sql = "SELECT * FROM `teams` WHERE lid = '$row[lid]';";
			$teamresult = $db->query($sql);
			$numrows = $teamresult->num_rows;
			if($numrows) {
				for($i=1; $i <= (6 - $numrows); $i++) { // not enough teams, so create some CPU teams
					$cpuname = $cpunames[$i];//$cpus[$i];
					$lcname = strtolower($cpunames[$i]);
					$sql = "INSERT INTO `teams` (lid,uid,name,logo) VALUES ('$row[lid]','0','$cpuname [CPU]','$lcname');"; // changed from $lid
					$teaminsertresult = $db->query($sql);
					//echo $sql;
				}
				// create the stack
				foreach ($region[$row['rid']]['set'] as $k => $v) {
					$sql = "SELECT * FROM `teams` WHERE lid = '$row[lid]';";
					$teamresult = $db->query($sql);
					while($teamrow = $teamresult->fetch_assoc()) {
						$sql = "INSERT INTO `stack` (lid,target,action,param,notes) VALUES ('$row[lid]','$teamrow[tid]','pleasepick','$v','');"; // changed from $lid
						$stackresult = $db->query($sql);
						$sql = "UPDATE `leagues` SET status='2' WHERE lid='$row[lid]'";
						$updateresult = $db->query($sql);
					}
				}
			} else {
				// game started but no players? Extend time
				$newstart = date('Y-m-d H:i:s',strtotime("+1 hour"));
				$sql = "UPDATE `leagues` SET start='$newstart',status=0 WHERE lid='$row[lid]'";
				$updateresult = $db->query($sql);
				echo $sql;
			}
		}
	} else {
		echo "NO LEAGUES<br>";
	}
}

// loop through stack and play off all current CPUs
$sql = "SELECT DISTINCT(lid) AS lid FROM stack ORDER BY lid;";
$lidresult = $db->query($sql);
while ($lids = $lidresult->fetch_assoc()) {
	$sql = "SELECT * FROM `stack` WHERE lid='$lids[lid]' ORDER BY sid LIMIT 5;";
	$result = $db->query($sql);
	while ($row = $result->fetch_assoc()) {
		$sql = "SELECT * FROM `leagues` WHERE lid='$row[lid]';";
		$leagueresult = $db->query($sql);
		$leaguerow = $leagueresult->fetch_assoc();
		$rid = $leaguerow['rid']; // added to override rid being pulled from session below
		if($team[$row['target']]['uid'] != 0){
			break;
		}
		if($team[$row['target']]['uid'] == 0){
			if($row['action'] == "pleasepick"){
				$expertise = $row['param'];
				$providers = array();
				$sql = "SELECT * FROM `providers` WHERE rid = '$rid';";
				$providerresult = $db->query($sql);
				while($providerrow = $providerresult->fetch_assoc()) {
					$providers[$providerrow['hid']] = $providerrow['name'];
				}
				$providerstring = "";
				foreach ($providers as $k => $v){
					$providerstring .= "hid='$k' OR ";
				}
				$providerstring = substr($providerstring, 0, -4);
				$sql = "SELECT * FROM `players` WHERE ($providerstring) AND expertise='$expertise' AND off='0' ORDER BY RAND();";//rank ASC;"; // CPU picks worst players
				//echo $sql;
				$playerresult = $db->query($sql);
				while($playerrow = $playerresult->fetch_assoc()){
					$sql = "SELECT * FROM `teams` WHERE lid='$row[lid]';"; // changed from $lid
					$teamresult = $db->query($sql);
					$teamstring = "";
					while($teamrow = $teamresult->fetch_assoc()){
						$teamstring .= $teamrow['pids'];
					}
					if(strpos($teamstring, ';'.$playerrow['pid'].';') === false){
						$sql = "SELECT * FROM `teams` WHERE tid='$row[target]';";
						$teamresult = $db->query($sql);
						$teamrow = $teamresult->fetch_assoc();
						$pids = substr($teamrow['pids'],0,-1);
						$pids = $pids.";".$playerrow['pid'].";";
						//echo $pids;
						$sql = "UPDATE `teams` SET pids='$pids' WHERE tid='$row[target]'";
						$updateresult = $db->query($sql);
						$sql = "DELETE FROM `stack` WHERE `sid`='$row[sid]';";
						$deleteresult = $db->query($sql);
						break;
					}
				}
			}
		}
	}
}

// Draft is over (stack empty?)
$sql = "SELECT * FROM `leagues` WHERE status='2';";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	$sql = "SELECT * FROM `stack` WHERE lid='$row[lid]' LIMIT 1;";
	$stackresult = $db->query($sql);
	$stackrow = $stackresult->fetch_assoc();
	if(!$stackrow['sid']){
		$sql = "UPDATE `leagues` SET status='3' WHERE lid='$row[lid]'";
		$updateresult = $db->query($sql);
	}
}

// Game start (set up tournaments)
$sql = "SELECT * FROM `leagues` WHERE status='3';";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	$stack = implode(",",$region[$row['rid']]['set']);
	$sql = "SELECT * FROM `games` WHERE lid='$row[lid]';";
	$gameresult = $db->query($sql);
	$gamerow = $gameresult->fetch_assoc();
	if(!$gamerow['gid']){ // No games have been set up yet
		$teams = array();
		$sql = "SELECT * FROM `teams` WHERE lid='$row[lid]';";
		$teamresult = $db->query($sql);
		while($teamrow = $teamresult->fetch_assoc()) {
			$teams[$teamrow['tid']] = $teamrow['tid'];
		}
		$teamresult = $db->query($sql);
		while($teamrow = $teamresult->fetch_assoc()) {
			foreach ($teams as $k) {
				if($k != $teamrow['tid']){
					$sql = "SELECT * FROM `games` WHERE lid='$row[lid]' AND tidh='$k' AND tidv='$teamrow[tid]';"; // make sure teams don't already play
					$matchresult = $db->query($sql);
					$matchrow = $matchresult->fetch_assoc();
					if(!$matchrow['gid']){
						$sql = "INSERT INTO `games` (lid,tidh,tidv,stack) VALUES ('$row[lid]','$teamrow[tid]','$k','$stack');";
						$insertresult = $db->query($sql);
					}
				}
			}
		}
	}
}

// Join teams to games
$sql = "SELECT * FROM `leagues` WHERE status='3';";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	$sql = "SELECT * FROM `teams` WHERE lid='$row[lid]' ORDER BY tid;";
	// Join any game waiting on the visitor
	$teamresult = $db->query($sql);
	while($teamrow = $teamresult->fetch_assoc()) {
		if($teamrow['gid'] == 0){
			$sql = "SELECT * FROM `games` WHERE `lid`='$row[lid]' AND `tidv`='$teamrow[tid]' AND `open`=1 AND `join`!=1 AND `winner`=0 ORDER BY gid;";
			$gameresult = $db->query($sql);
			$gamerow = $gameresult->fetch_assoc();
			$sql = "UPDATE `teams` SET gid='$gamerow[gid]' WHERE tid='$teamrow[tid]'";
			$updateresult = $db->query($sql);
			$sql = "UPDATE `games` SET `join`=1 WHERE gid='$gamerow[gid]'";
			$updateresult = $db->query($sql);
		}
	}
	// No visitor slot to join, so open a home slot
	$sql = "SELECT * FROM `teams` WHERE lid='$row[lid]' ORDER BY tid;";
	$teamresult = $db->query($sql);
	while($teamrow = $teamresult->fetch_assoc()) {
		if($teamrow['gid'] == 0){
			$sql = "SELECT * FROM `games` WHERE lid='$row[lid]' AND tidh='$teamrow[tid]' AND open=0 AND `join`=0 AND winner=0 ORDER BY gid;";
			$gameresult = $db->query($sql);
			$gamerow = $gameresult->fetch_assoc();
			// Check to see if visitor hasn't already joined a game
			$sql = "SELECT * FROM `games` WHERE lid='$row[lid]' AND tidh='$gamerow[tidv]' AND open=1 AND `join`=0 AND winner=0;";
			$team2result = $db->query($sql);
			$team2row = $team2result->fetch_assoc();
			if(!$team2row['gid']) {
				// Check to make sure user hasn't already opened a game
				$sql = "SELECT * FROM `games` WHERE lid='$row[lid]' AND tidh='$teamrow[tid]' AND open=1 AND winner=0;";
				$checkresult = $db->query($sql);
				$checkrow = $checkresult->fetch_assoc();
				if(!$checkrow['gid']){
					$sql = "UPDATE `teams` SET gid='$gamerow[gid]' WHERE tid='$teamrow[tid]'";
					$updateresult = $db->query($sql);
					$sql = "UPDATE `games` SET `open`=1 WHERE gid='$gamerow[gid]'";
					$updateresult = $db->query($sql);
				} else { 
				}
			}
		}
	}
}

// Loop through all games and process each stack
$sql = "SELECT * FROM `games` WHERE stack!='' AND open=1 AND `join`=1 AND winner=0 ORDER BY gid;";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	$stack = explode(',',$row['stack']);
	$expertise = $stack[0];
	// Get home tid info
	$sql = "SELECT * FROM `teams` WHERE lid='$row[lid]' AND tid='$row[tidh]';";
	$hresult = $db->query($sql);
	$hrow = $hresult->fetch_assoc();
	$pids = array_filter(explode(';',$hrow['pids']));
	foreach ($pids as $pid){
		if($player[$pid]['expertise'] == $expertise){
			$pidh = $pid;
		}
	}
	// Get visitor tid info
	$sql = "SELECT * FROM `teams` WHERE lid='$row[lid]' AND tid='$row[tidv]';";
	$vresult = $db->query($sql);
	$vrow = $vresult->fetch_assoc();
	$pids = array_filter(explode(';',$vrow['pids']));
	foreach ($pids as $pid){
		if($player[$pid]['expertise'] == $expertise){
			$pidv = $pid;
		}
	}
	if($player[$pidh]['rank'] >= $player[$pidv]['rank']) {
		$winner = $pidh;
		$loser = $pidv;
		$winnerName = $player[$pidh]['lastname'];
		$loserName = $player[$pidv]['lastname'];
		$winnerProvider = $provider[$player[$pidh]['hid']]['shortname'];
		$loserProvider = $provider[$player[$pidv]['hid']]['shortname'];
		$won = 'h';
		$lost = 'v';
		$winnerteam = $team[$hrow['tid']]['name'];
	} else {
		$winner = $pidv;
		$loser = $pidh;
		$winnerName = $player[$pidv]['lastname'];
		$loserName = $player[$pidh]['lastname'];
		$winnerProvider = $provider[$player[$pidv]['hid']]['shortname'];
		$loserProvider = $provider[$player[$pidh]['hid']]['shortname'];
		$won = 'v';
		$lost = 'h';
		$winnerteam = $team[$vrow['tid']]['name'];
	}
	$pts = round(abs($player[$pidh]['rank'] - $player[$pidv]['rank'])) + 1;
	$s = "";
	if($pts > 1){
		$s = "s";
	}
	$Expertise = ucwords($expertise);
	$note = "<strong>$winnerteam +$pts</strong> ($Expertise)<br><small>Dr. $winnerName <em>($winnerProvider)</em> +$pts pt$s against Dr. $loserName <em>($loserProvider)</em>!</small>";
	$sql = "INSERT INTO `gamelogs` (gid,lid,action,param,note,winner,loser,pts,won) VALUES ('$row[gid]','$row[lid]','compare','$expertise','$note','$winner','$loser','$pts','$won');";
	$insertresult = $db->query($sql);
	unset($stack[0]);
	$stackupdate = implode(',',$stack);
	$sql = "UPDATE `games` SET stack='$stackupdate' WHERE gid='$row[gid]'";
	$updateresult = $db->query($sql);
	//echo $sql;
}

// Game stack finished? Wrap up game
$sql = "SELECT * FROM `games` WHERE stack='' AND open=1 AND `join`=1 AND winner=0 ORDER BY gid;";
$result = $db->query($sql);
while($row = $result->fetch_assoc()) {
	// Tally up home points
	$sql = "SELECT * FROM `gamelogs` WHERE gid='$row[gid]' AND action='compare' AND won='h' ORDER BY gid;";
	$logresult = $db->query($sql);
	while($logrow = $logresult->fetch_assoc()) {
		$sql = "UPDATE `games` SET hpts = hpts + $logrow[pts] WHERE gid='$row[gid]'";
		$updateresult = $db->query($sql);
		$sql = "SELECT * FROM `games` WHERE gid='$row[gid]';";
		$hresult = $db->query($sql);
		$hrow = $hresult->fetch_assoc();
		$hpts = $hrow['hpts'];
	}
	// Tally up visitor points
	$sql = "SELECT * FROM `gamelogs` WHERE gid='$row[gid]' AND action='compare' AND won='v' ORDER BY gid;";
	$logresult = $db->query($sql);
	while($logrow = $logresult->fetch_assoc()) {
		$sql = "UPDATE `games` SET vpts = vpts + $logrow[pts] WHERE gid='$row[gid]'";
		$updateresult = $db->query($sql);
		$sql = "SELECT * FROM `games` WHERE gid='$row[gid]';";
		$vresult = $db->query($sql);
		$vrow = $vresult->fetch_assoc();
		$vpts = $vrow['vpts'];
	}
	// Calculate final score
	echo $hpts ."+".$vpts;
	if($hpts >= $vpts) {
		$sql = "UPDATE `games` SET winner='$row[tidh]' WHERE gid='$row[gid]';";
		$updateresult = $db->query($sql);
		$finalwinner = $team[$row['tidh']]['name'];
	} else {
		$sql = "UPDATE `games` SET winner='$row[tidv]' WHERE gid='$row[gid]';";
		$updateresult = $db->query($sql);
		$finalwinner = $team[$row['tidv']]['name'];
	}
	// Reset gids on each team to start another game
	$sql = "UPDATE `teams` SET gid=0 WHERE gid='$row[gid]'";
	$updateresult = $db->query($sql);
	// Insert final log
	$sql = "SELECT * FROM `games` WHERE gid='$row[gid]';";
	$gresult = $db->query($sql);
	$grow = $gresult->fetch_assoc();
	$note = '<a href="#gamewait">Game over! <em>'.$finalwinner.'</em> win! Next game &raquo;</a>'; //, final score: '.$grow['hpts'].'-'.$grow['vpts'].'
	$sql = "INSERT INTO `gamelogs` (gid,lid,action,note) VALUES ('$row[gid]','$row[lid]','final','$note');";
	$insertresult = $db->query($sql);
}
