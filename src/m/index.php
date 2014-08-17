<?php // Fantasy Healthcare QJM Front End

include_once('../functions.php');
$gotoPage = "";
$url = "";
if($_GET['tok']){
	$_SESSION['tok'] = $_GET['tok'];
}
if(!isset($_SESSION['uid'])) {
	$u = sanitize($_GET['u']);
	$p = sanitize($_GET['p']);
	$pmd5 = md5($p);
	if($db->connect_errno){
		die('Unable to connect.');
	}
	$sql = "SELECT * FROM `users` WHERE email='$u' AND password='$pmd5';";
	if(!$result = $db->query($sql)){
		die('Error running query.');
	}
	$row = $result->fetch_assoc();
	if($result->num_rows) {
		$return['success'] = md5(strtolower($u.$p.'m4c4r00n'));
		$_SESSION['u'] = $row['email'];
		$_SESSION['uid'] = $row['uid'];
		$_SESSION['tok'] = $return['success'];
	} else {
		$gotoPage = "login";
		//die('Sorry, your username or password is incorrect. Please try again.');
	}
}
$token = $_SESSION["tok"];
$uid = $_SESSION["uid"];
$rid = $_SESSION["rid"];
$u = $_SESSION["u"];
if(isset($_GET["page"])){
	$gotoPage = $_GET["page"]; // Get suggested page from mobile redirect
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta charset="utf-8">
	<title>Fantasy Healthcare</title>
	<link href='http://fonts.googleapis.com/css?family=Chivo' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Oxygen' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
	<link rel="stylesheet" href="css/style.css">
	<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
    <script src="js/init.js"></script>
	<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
	<script src="js/app.js"></script>
	<?php if($gotoPage){?>
	<script>
		$(document).ready(function() {
			$.mobile.changePage("#<?php echo $gotoPage;?>");
		});
	</script>
	<?php }?>
</head>
<body>

<!-- PAGE: MAIN -->
<div data-role="page" id="main">
	<div data-role="header" data-tap-toggle="false">
    	<a data-tok="<?php echo $token;?>" data-role="button" data-icon="arrow-l" class="queue ui-btn-left" data-theme="c">Back</a>
		<h1>Welcome</h1>
        <a href="#main" id="btn-logout" data-role="button" data-icon="home" data-iconpos="right" class="ui-btn-right" data-theme="e">Logout</a>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true">
			<!--<li><a href="#login">Login</a></li>
			<li><a href="#signup">Sign Up</a></li>
			<li><a href="#main">Main</a></li>-->
            <li><a href="#join">Join a League</a></li>
            <li><a href="#createleague">Start a League</a></li>
            <!--<li><a href="#createteam">Create your Team</a></li>
            <li><a href="#dashboard">Dashboard</a></li>
            <li><a href="#draft">Draft</a></li>
            <li><a href="#rankings">Rankings</a></li>
            <li><a href="#game">Game</a></li>
            <li><a href="#trivia">Trivia</a></li>
            <li><a href="#survey">Survey</a></li>-->
		</ul>
	</div>
  <p>
      <br>
  </p>
</div>

<!-- PAGE: LOGIN -->
<div data-role="page" id="login">
	<div data-role="header" data-position="fixed" data-tap-toggle="false">
		<h1>Login</h1>
	</div>
	<div data-role="content">
	  <div data-role="fieldcontain">
      	<p style="font-size:1em; text-align:justify;">Enter your email address and password to sign in.</p>
	    <input type="email" name="email" id="login-email" placeholder="Email address">
        <input type="password" name="password" id="login-password" placeholder="Password"><br>
		<a href="#" data-role="button" id="btn-login" data-theme="e">Login</a>
      </div>
      <div data-role="fieldcontain">
		<a data-role="button" href="#signup" data-theme="a">Sign Up!</a>
      </div>
    </div>
</div>

<!-- PAGE: SIGN UP -->
<div data-role="page" id="signup">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#login" data-rel="next" data-role="button" data-icon="home" class="ui-btn-left" data-theme="c">Home</a>
		<h1>Sign Up</h1>
	</div>
	<div data-role="content">
	  <div data-role="fieldcontain">
      	<p style="font-size:1em; text-align:justify;">Joining is free and easy. Enter your information below to start!</p>
      	<input type="text" name="firstname" id="signup-firstname" placeholder="First name">
        <input type="text" name="lastname" id="signup-lastname" placeholder="Last name">
	    <input type="email" name="email" id="signup-email" placeholder="Email address">
        <input type="email" name="emailagain" id="signup-emailagain" placeholder="Email address (again)">
      </div>
      <div data-role="fieldcontain">
        <input type="password" name="password" id="signup-password" placeholder="Password">
        <input type="password" name="passwordagain" id="signup-passwordagain" placeholder="Password (again)">
      </div>
      <div data-role="fieldcontain">
      	<button id="btn-signup" data-theme="e">Sign Up!</button>
      </div>
    </div>
</div>

<!-- PAGE: JOIN A LEAGUE -->
<div data-role="page" id="join">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#main" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Join League</h1>
        <a href="#createleague" data-role="button" data-icon="plus" data-iconpos="right" class="ui-btn-right" data-theme="e">New</a>
	</div>
	<div data-role="content">
    	<p style="font-size:1em;text-align:justify;position:relative;top:-15px;">Select a league from the list or create a new league using the button above.</p>
		<ul id="leaguelist" data-role="listview" data-filter="true" data-inset="true">
		</ul>
        <p class="clear"><br></p>
        <a href="#createleague" data-role="button" data-icon="plus" data-theme="e" data-iconpos="right">New League</a>
	</div>
</div>

<!-- PAGE: JOIN POPUP -->
<div data-role="dialog" id="joinpopup">
	<div data-role="header" data-tap-toggle="false">
		<h1>Password?</h1>
	</div>
	<div data-role="content">
    	<input type="hidden" name="join-lid" id="join-lid">
        <input type="password" name="password" id="join-password" placeholder="Password">
        <p class="clear"><br></p>
        <a href="#" id="joinwithpassword" data-role="button" data-icon="arrow-r" data-theme="e" data-iconpos="right">Join</a>
	</div>
</div>

<!-- PAGE: CREATE A LEAGUE -->
<div data-role="page" id="createleague">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#main" data-rel="back" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>New League</h1>
	</div>
	<div data-role="content">
	  <div data-role="fieldcontain">
      	<p style="font-size:1em;text-align:justify;position:relative;top:-15px;">Creating a new league is easy! If you want to keep outsiders from joining, supply a password.</p>
      	
        <div data-role="fieldcontain">
        	<input type="text" name="name" id="createleague-name" placeholder="League Name">
            <input type="password" name="password" id="createleague-password" placeholder="Password (optional)">
        </div>
        <div data-role="fieldcontain">
        	<p>Provider Region...</p>
        	<select id="createleague-region">
            	<option value="1">Wisconsin</option>
            </select>
            <p>Draft starts in...</p>
            <select id="createleague-start">
            	<option value="+5 minutes">5 min</option>
            	<option value="+1 hour">1 hour</option>
                <option value="+3 hours">3 hours</option>
                <option value="+24 hours">24 hours</option>
            </select>
            <p>Make sure your give your friends enough time to join!</p>
        </div>
        <div data-role="fieldcontain">
            <button id="btn-createleague" data-theme="e">Create!</button>
        </div>
      	
      </div>
    </div>
</div>

<!-- PAGE: CREATE A TEAM -->
<div data-role="page" id="createteam">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Create Team</h1>
	</div>
	<div data-role="content">
	  <div data-role="fieldcontain">
      <?php if(!$_GET['error']){?>
      	<p style="font-size:1em;text-align:justify;position:relative;top:-15px;">You will need a team to play in this league. The name and logo can be anything you like.</p>
      <?php } else {?>
      <p style="font-size:1em;text-align:justify;position:relative;top:-15px;color:#f00;"><?php echo $_GET['error'];?>.</p>
      <?php }?>
        <form method="post" data-ajax="false" enctype="multipart/form-data" action="../upload" id="form-createteam">
            <input type="text" name="createteam-name" id="createteam-name" placeholder="Team Name">
            <div data-role="fieldcontain">
                <p><label for="createteam-img">Logo</label></p>
                <input id="createteam-img" name="createteam-img" type="file" accept="image/*, capture=camera">
            </div>
            <button id="btn-createteam" data-theme="e">Done!</button>
        </form>
      </div>
    </div>
</div>

<!-- PAGE: DASHBOARD -->
<div data-role="page" id="dashboard">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Dashboard</h1>
	</div>
	<div data-role="content">
        <div id="clock-draft">...</div>
        <h3>Teams so far:</h3>
        <div id="teamlist" data-role="listview" data-inset="true">
        </div>
        <p>Use this time to research which hospitals may perform best in what areas.</p>
        <p><a href="#invitefriends" data-role="button" data-theme="e">Invite Friends!</a></p>
        <div id="draftnowcontainer">
            <a href="#draftwait" id="draftstart" data-role="button" data-icon="alert" data-theme="c">Start draft now!</a>
        </div>
    </div>
</div>

<!-- PAGE: DASHBOARD SIGNIN (used from create team page) -->
<div data-role="page" id="dashboard-signin">
	<div data-role="header" data-tap-toggle="false">
    	<!--<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>-->
		<h1>Dashboard</h1>
	</div>
	<div data-role="content">
		<p><em>Signing in, please wait...</em></p>
        <img src="css/images/ajax-loader.gif" alt="">
    </div>
</div>

<!-- PAGE: DRAFT - WAIT -->
<div data-role="page" id="draft">
	<div data-role="header" data-tap-toggle="false">
    	<!--<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>-->
		<h1>Draft</h1>
	</div>
	<div data-role="content">
		<p><em>Other players choosing, please wait...</em></p>
        <img src="css/images/ajax-loader.gif" alt="">
    </div>
</div>

<!-- PAGE: DRAFT - PICK -->
<div data-role="page" id="draftpick">
	<div data-role="header" data-tap-toggle="false">
    	<!--<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>-->
		<h1>Draft Pick</h1>
	</div>
	<div data-role="content">
                <h3 id="draftexpertise"></h3><br>
                <ul id="draftlist" data-role="listview" data-theme="c">
                </ul>
    </div>
    <div data-role="footer" data-position="fixed" data-tap-toggle="false">
        <h4>You have <span id="clock-draft-left">30</span> seconds left...</h4>
    </div>
    
    <div data-role="popup" id="popup-draftpick" data-overlay-theme="a" data-theme="e" style="max-width:400px;" class="ui-corner-all">
        <div data-role="content" data-theme="e" class="ui-corner-bottom ui-content">
            <p style="text-align:justify">IMPORTANT: The names listed <strong>do not</strong> represent real-life doctors! However, they are a representation of real-life data of the providers "they" represent. You should choose players based on your own personal knowledge and research, and see how they stack up against what your friends know!</p>
            <a href="#" id="popup-draftpick-close" data-role="button" data-theme="c">Okay</a>
        </div>
    </div>
    
</div>

<!-- PAGE: DASHBOARD FINAL -->
<div data-role="page" id="dashboardfinal">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Results</h1>
	</div>
	<div data-role="content">
        <h3>Final results!</h3>
        <div id="resultlist">
        </div>
        <h3>And the winner is...</h3>
        <div id="resultwinner"></div>
        <div id="restartcontainer">
        	<a href="#join" id="leaguereset" data-role="button" data-icon="refresh" data-theme="c">Reset League</a>
            <a href="#join" id="leaguedelete" data-role="button" data-icon="delete" data-theme="c">Delete League</a>
        </div>
    </div>
</div>

<!-- PAGE: RANKINGS -->
<div data-role="page" id="rankings">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Rankings</h1>
	</div>
	<div data-role="content">
		<div data-role="collapsible-set" data-theme="a" data-content-theme="a">
            <div data-role="collapsible">
                <h3>The Falcons</h3>
                <ul data-role="listview" data-theme="c">
                    <li><a href="#dashboard">Dr. Spock</a></li>
                    <li><a href="#dashboard">Dr. Who</a></li>
                    <li><a href="#dashboard">Nurse Fisher</a></li>
                    <li><a href="#dashboard">Dr. McCoy</a></li>
                    <li><a href="#dashboard">Dr. Hamill</a></li>
                    <li><a href="#dashboard">Dr. Vader</a></li>
                    <li><a href="#dashboard">Dr. Brimley</a></li>
                    <li><a href="#dashboard">Dr. Ford</a></li>
                </ul>
            </div>
            <div data-role="collapsible">
                <h3>The Ravens</h3>
                <ul data-role="listview" data-theme="c">
                    <li><a href="#dashboard">Dr. Spock</a></li>
                    <li><a href="#dashboard">Dr. Who</a></li>
                    <li><a href="#dashboard">Nurse Fisher</a></li>
                    <li><a href="#dashboard">Dr. McCoy</a></li>
                    <li><a href="#dashboard">Dr. Hamill</a></li>
                    <li><a href="#dashboard">Dr. Vader</a></li>
                    <li><a href="#dashboard">Dr. Brimley</a></li>
                    <li><a href="#dashboard">Dr. Ford</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- PAGE: GAME WAITING ROOM -->
<div data-role="page" id="gamewait">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Game</h1>
	</div>
	<div data-role="content">
        <p><em>Waiting for other teams, this may take a minute...</em></p>
        <img src="css/images/ajax-loader.gif" alt="">
    </div>
</div>

<!-- PAGE: GAME -->
<div data-role="page" id="game">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Game</h1>
	</div>
	<div data-role="content">
    	<div id="scoreboard">
       		<span id="tidh"></span> &nbsp;vs. <span id="tidv"></span><br>
			<em class="sm"><span id="tidhname"></span> vs. <span id="tidvname"></span></em><br>
			<span id="hpts"></span> to <span id="vpts"></span><br>
        </div>
		<h3>Activity</h3>
        <ul id="gamelog" data-role="listview" data-inset="true" data-mini="true">
        </ul>
        <fieldset class="ui-grid-a">
            <div class="ui-block-a"><a href="#survey" data-role="button" data-theme="e" data-mini="true">Survey!</a></div>
            <div class="ui-block-b"><a href="#trivia" data-role="button" data-theme="e" data-mini="true">Trivia!</a></div>
            <!--<div class="ui-block-a"><button id="btn-game1" data-theme="c" data-mini="true">Goalkick!</button></div>
            <div class="ui-block-b"><button id="btn-game2" data-theme="c" data-mini="true">Touchdown!</button></div>
            <div class="ui-block-a"><button id="btn-game3" data-theme="c" data-mini="true">Offensive!</button></div>
            <div class="ui-block-b"><button id="btn-game4" data-theme="c" data-mini="true">Defensive!</button></div>-->
        </fieldset>
		<button id="pfb" data-theme="e" data-mini="true">Paper Football!</button>
    </div>
</div>

<!-- PAGE: INVITE FRIENDS -->
<div data-role="page" id="invitefriends">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#join" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Invite</h1>
	</div>
	<div data-role="content">
	  <div data-role="fieldcontain">
      	<p style="font-size:1em;text-align:justify;position:relative;top:-15px;">Easily invite your friends to play in this league! There's still time!</p>
            <input type="text" name="invitefriends-name" id="invitefriends-name" placeholder="Friend's Name">
            <input type="email" name="invitefriends-email" id="invitefriends-email" placeholder="Friend's Email">
            <div data-role="fieldcontain">
            <textarea name="invitefriends-comment" id="invitefriends-comment"></textarea>
            </div>
            <button id="btn-invitefriends" data-theme="e">Done!</button>
      </div>
    </div>
</div>

<!-- PAGE: TRIVIA -->
<div data-role="page" id="trivia">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#gamewait" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Trivia</h1>
	</div>
	<div data-role="content">
    	<div id="trivia-result"></div>
        <form id="triviaquestions" data-role="fieldcontain">
        </form>
        <button id="btn-trivia-check" data-theme="e">Check Answers</button>
    </div>
</div>

<!-- PAGE: SURVEY -->
<div data-role="page" id="survey">
	<div data-role="header" data-tap-toggle="false">
    	<a href="#gamewait" data-role="button" data-icon="arrow-l" class="ui-btn-left" data-theme="c">Back</a>
		<h1>Survey</h1>
	</div>
  <div data-role="content">
        <p>Thanks for your interest in our survey. You will receive bonus points for the current game after completion. A few basic questions follow, please respond to them as accurately as possible. If you've had experiences with multiple providers, please come back and fill them out for additional points!</p>
    <p>I have had a personal experience with the following provider...</p>
    <p>
    <select>
    <?php
	$sql = "SELECT * FROM `providers` WHERE rid='1';";
	$result = $db->query($sql);
	while ($row = $result->fetch_assoc()) {
	?>
        <option name="provider" value="<?php echo $row['hid'];?>"><?php echo $row['name'];?></option>
    <?php }?>
    </select>
    </p>
    <p>I was treated for...</p>
    <select>
    <?php
    $sql = "SELECT * FROM `regions` WHERE rid='1';";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	$set = explode(',',$row['set']);
	foreach ($set as $k){
		$expertise = ucwords($k);
	?>
    	<option name="provider-expertise" value="<?php echo $k;?>"><?php echo $expertise;?></option>
        
    <?php }?>
    	<option name="provider-expertise" value="Other">Other</option>
    </select>
    <p>Based on my personal experience, I would rate their services, from 1-100, 100 being excellent:</p>
    <input type="range" name="slider-<?php echo $k;?>" id="provicer-slider" value="50" min="0" max="100" data-mini="true" data-highlight="true">
	<p>Optional comments:</p>
    <textarea name="provider-comment" id="provider-comment" placeholder=""></textarea>
    <p><br>Thanks again for filling out this survey. This data will be treated anonymously. Press "Submit" to finish.</p>
    <button id="provider-submit">Submit</button>
    </div>
</div>

</body>
</html>