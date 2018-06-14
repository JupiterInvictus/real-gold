<?php
//$time = time();
//global $teamdefinition, $ahtteamdefinition, $simplecolors;
$application_name = "Concentrix Global Online Leader Dashboard";
$application_copyright = "Copyright Concentrix Europe Ltd 2018";
$application_contact = "joakim.saettem@concentrix.com";
$application_version_major = "2018-01-25";
$application_version_minor = "1.3";

//$time = $_SERVER[‘REQUEST_TIME’];

$bad_color = 'd7191c';
$good_color = 'ffffbf';
$great_color = 'a6d96a';

include "config.php";
include "zm-common.php";

$theme = gettheme();

echo "
<!DOCTYPE html>
<html>
	<head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
		<script src='{$path}sorttable.js'></script>
		<script src='{$path}gold.js'></script>
		<script defer src='https://use.fontawesome.com/releases/v5.0.12/js/all.js' integrity='sha384-Voup2lBiiyZYkRto2XWqbzxHXwzcm4A5RfdfG6466bu5LqjwwrjXCMBQBLMWh7qR' crossorigin='anonymous'></script>
		<link rel=stylesheet href='gold-shared.css?version=2'>
		<link rel=stylesheet href='{$theme}.css'>
		<link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900' rel='stylesheet'>
		<link href='https://afeld.github.io/emoji-css/emoji.css' rel='stylesheet'>
		<title>{$application_name}</title>
	</head>
	<body>";

if ($app_action == 'upload'){
	if(isset($_FILES['filedata'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedata"]["name"]);
			if (move_uploaded_file($_FILES["filedata"]["tmp_name"], $target_file)) {
				echo "The file ". basename( $_FILES["filedata"]["name"]). " has been uploaded.";
				processreport($target_file);
			}
			else { echo "Sorry, there was an error uploading your file."; }
	}
}
if ($_POST[a]=='uploadprtgeneral'){
  if(isset($_FILES['filedataprtgeneral'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedataprtgeneral"]["name"]);
			if (move_uploaded_file($_FILES["filedataprtgeneral"]["tmp_name"], $target_file)) {
				//echo "The PRT report ". basename( $_FILES["filedataprtgeneral"]["name"]). " has been uploaded.";

				$reportType = strtolower(substr(basename($_FILES["filedataprtgeneral"]["name"]), 0, 6));

				if ($reportType == 'prt060') {
					processahtreport($target_file);
				}
				else {
					processGeneralPrtReport($target_file);
				}
			}
			else {
				echo "Sorry, there was an error uploading your PRT file.";
			}
	}
}

// Are we logged in?

// A new password has been supplied for a user
if ($_POST['newpassword']) {

	// Check whether this user is actually flagged as new.
	if (sqr("SELECT user_new FROM users WHERE user_id = '" . $_POST['userid'] . "'")['user_new'] == 1) {
		$newhash = generatehash($_POST['newpassword']);
		sq("UPDATE users SET user_hash='".$newhash."', user_new='0' WHERE user_id = '" . $_POST['userid'] . "'");
	}
	showMainPage();
}
if ($_POST['username']) {
	if (validatepassword($_POST['username'],$_POST['password'])) {
		$_SESSION['user_id'] = getuserid($_POST['username']);
		$_SESSION['logged_in'] = true;
		showMainPage();
	}
	else {
		$login_error = "Incorrect username and/or password.";
	}
}

// Allow new users to set a password
if ($_GET['n']) {
	if (sqr("SELECT user_new FROM users WHERE user_id = '" . $_GET['n'] . "' LIMIT 1")['user_new'] == 1) { showsetnewpassword(); }
	else { showlogin(); }
}
else {
	if (!$_SESSION['logged_in']) { // Not logged in
		showlogin();
	}
	else { // Logged in
		// Display simple colors?
		$simplecolors = sqr("SELECT user_simplecolors FROM users WHERE user_id = '$uid' LIMIT 1")['user_simplecolors'];
		$b=$_GET[b];
		$c=$_GET[c];
		$c2=$_GET[c2];
		$d=$_GET[d];
		$e=$_GET[e];
		$f=$_GET[f];
		$m=$_GET['m'];
		$m2=$_GET['m2'];
		$fullparams = "a=$app_action&b=$b&c=$c&c2=$c2&d=$d&e=$e&f=$f&m=$m&m2=$m2&team=$team";
		$un=getusername($uid);

		$prefteam = sqr("SELECT user_preferred_team FROM users WHERE user_id = '$uid' LIMIT 1")['user_preferred_team'];
		if ($_GET['prefteam']){
			$prefteam = $_GET['prefteam'];
			sq("UPDATE users SET user_preferred_team = '" .  $_GET['prefteam'] . "' WHERE user_id = '$uid' LIMIT 1");
		}
		$team = $prefteam;

		$startdate = sqr("SELECT user_startdate FROM users WHERE user_id = '$uid' LIMIT 1")['user_startdate'];
		$enddate = sqr("SELECT user_enddate FROM users WHERE user_id = '$uid' LIMIT 1")['user_enddate'];
		$currentmonth = date("m");
		$currentyear = date("Y");
		$currentday = date("d");
		$lastday = cal_days_in_month(CAL_GREGORIAN,$currentmonth,$currentyear);

		$app_startdate = $startdate;
		$app_enddate = $enddate;

		// Let's cut any time stamp
		$startdate = substr($startdate,0,10);
		$enddate = substr($enddate,0,10);

		// If startdate is later than enddate, set startdate to be enddate.
		if ($startdate > $enddate) { $startdate = $enddate; }

		// if a new startdate has been specified.
		if ($_GET['startdate']){
			$startdate = $_GET['startdate'];
			$enddate = $_GET['enddate'];
		}

		// If we don't have a startdate
		if ($startdate == ''){
			$startdate_month = str_pad($currentmonth-1,2,"0",STR_PAD_LEFT);
			if ($enddate == ''){ $enddate = "$currentyear-$startdate_month-$lastday"; }
			$startdate = "$currentyear-$startdate_month-01";
		}
		$startdate_year = substr($startdate,0,4); $startdate_month = substr($startdate,5,2); $startdate_day = substr($startdate,8,2);
		$enddate_year = substr($enddate,0,4); $enddate_month = substr($enddate,5,2); $enddate_day = substr($enddate,8,2);
		$startdate_excel = unixdate_to_exceldate(mktime(0,0,0,$startdate_month,$startdate_day,$startdate_year));
		$enddate_excel = unixdate_to_exceldate(mktime(23,59,59,$enddate_month,$enddate_day,$enddate_year));
		$sqldater = " WHERE Teammate_Contact_Date > $startdate_excel";
		$sqldater .= " AND Teammate_Contact_Date < $enddate_excel";

		sq("UPDATE users SET user_startdate = '$startdate' WHERE user_id = '$uid' LIMIT 1");
		sq("UPDATE users SET user_enddate = '$enddate' WHERE user_id = '$uid' LIMIT 1");

		$lastmonth = date("Y-m-d", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
		$lastmonthyear = date("Y", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
		$lastmonthmonth = date("m", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
		$lastmonthday = date("d", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
		$lastend = date("Y-m-d", mktime(0, 0, 0, $lastmonthmonth, cal_days_in_month(CAL_GREGORIAN,$lastmonthmonth,$lastmonthyear), $lastmonthyear));

		$nextmonth = date("Y-m-d", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
		$nextmonthyear = date("Y", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
		$nextmonthmonth = date("m", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
		$nextmonthday = date("d", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
		$nextend = date("Y-m-d", mktime(0, 0, 0, $nextmonthmonth, cal_days_in_month(CAL_GREGORIAN,$nextmonthmonth,$nextmonthyear), $nextmonthyear));

		$display_year = $startdate_year;
		$display_month = $month[$startdate_month];

		echo "<div class='bar-select-dates'>";

		echo "<div class='currentmonth'><a href='?$fullparams&startdate=$currentyear-$currentmonth-01&enddate=$currentyear-$currentmonth-$lastday'>Current Month <i class='far fa-calendar-check'></i></a></div>";

		echo "<div class='startdatepicker'>";
		//echo "<i class='far fa-calendar-alt'></i> ";
		echo "Start: <input class=startdate id=startdate name=startdate type=date value='$startdate' onChange='location.href=\"?a=$app_action&b=$b&c=$c&d=$d&team=$team&enddate=$enddate&startdate=\"+this.value;'></div>";


		echo "<div class='enddatepicker'>";
		//echo "<i class='far fa-calendar-alt'></i> ";
		echo "End: <input class=enddate id=enddate name=enddate type=date value='$enddate' onChange='location.href=\"?a=$app_action&b=$b&c=$c&d=$d&team=$team&startdate=$startdate&enddate=\"+this.value;'></div>";

		// Pick preferred team
		echo "<div class='preferred-team'>Team ";
		echo "<select onChange='location.href=\"?$fullparams$extraurl&startdate=$startdate&enddate=$enddate&team=\" + this.value + \"&prefteam=\" + this.value;' name='prefteam'>";
		echo "<option value='-1'>All teams</option>";
		$sql = "SELECT id,team_name FROM teams LIMIT 50";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		while($row=$result->fetch_assoc()){
			echo "<option "; if ($prefteam==$row[id]){ echo "selected "; }
			echo "value={$row[id]}>{$row[team_name]}</option>";
		}
		echo "</select></div>";


		echo "</div>";

		if ($team){
			$teamdefinition = getTeamDefinitions($team);
			$teamahtdefinition = getTeamAhtDefinitions($team);
		}

		// Default action is dashboard.
		if (!$app_action) { $app_action = 'dashboard'; }

		echo "<div class='top-bar'>";
		echo "
		<div class='new-logo'>
			<div class='logo-text' title='Global Online Leadership Dashboard'>";
			echo "<a href='./'>Gold</a>";
			echo "</div>
			<div class='logo-colors'>
				<div class='logo-red'></div>
				<div class='logo-amber'></div>
				<div class='logo-green'></div>
			</div>
		</div>";
		echo "<i class='search-icon fa fa-search'></i>";
		echo "<input class='top-search-bar' placeholder='Search by NTID, name, survey id, or account number.' onKeyDown='checksearch(event, this);'";
		if (isset($_GET['search'])) {
			echo " value='{$_GET['search']}'";
		}
		echo ">";
		echo "<div class='time-interval-year'>{$display_year}</div>";
		echo "<div class='time-interval-month'>{$display_month}</div>";

		echo "<div title='Previous month' class='icon-month-previous'><a href='?$fullparams&startdate=$lastmonth&enddate=$lastend'><div class='fa fa-3x fa-angle-double-left'></div></a></div>";
		echo "<div title='Next month' class='icon-month-next'><a href='?$fullparams&startdate=$nextmonth&enddate=$nextend'><div class='fa fa-3x fa-angle-double-right'></div></a></div>";
		echo "</div>";

		echo "<div class='bar-actions'>";
		addleftchoice("dashboard");
		addleftchoice("surveys");
		addleftchoice("bonus");
		addleftchoice("trends");
		addleftchoice("digger");
		addleftchoice("team");
		echo "<hr>";
		addleftchoice("targets");
		addleftchoice("certification");
		addleftchoice("settings");
		if (isadmin()) { addleftchoice("admin"); }
		echo "</div>";

		if ($app_action) {
			include "zm-$app_action.php";
			if ($app_action) {
				$converted_title = str_replace("_", " ", $app_action);
				echo "<h1 class='module-title'><i class='fa fa-".geticon($app_action)."'></i> <a href='?a=$app_action'>".ucwords($converted_title)."</a></h1>";
			}
			echo "<div class='pad module-general module-$app_action'>";
			show_module();
			echo "</div>";
			echo "<div class='leftinfo'>Updated:  ";
			$today = date("Y-m-d");
			$medalliaupdated = getsetting('medalliadata');
			$vm055updated = getsetting('vm055data');
			$prt060pupdated = getsetting('prt060');
			$prt085updated = getsetting('prt085');
			$prt073updated = getsetting('prt073');
			$medalliaupdated = getsetting('medalliadata');
			$sub = $_GET[sub];
			echo "Platform: " . dd("$application_version_major $application_version_minor") . " | ";
			echo "Medallia: " . dd($medalliaupdated) . " | ";
			echo "PRT060p: " . dd($prt060pupdated) . " | ";
			echo "PRT073: " . dd($prt073updated) . "";
			echo "</div>";
		}
	}
}
echo "<div class='saettem-logo'><a href='https://saettem.com'><img src='/gold/images/s.png'></a></div>";
echo "</body></html>";
$db->close();
?>
