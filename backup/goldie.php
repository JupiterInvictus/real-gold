<?php

// Date intervals must be supplied to functions as dates. Year, Month, Day (2017, 05, 01), no hours, minutes and seconds, no unixdates and no exceldates.

class zdate {
	var $year, $month, $day;
	function set_date($year, $month, $day) {
		$this->year = $year;
		$this->month = $month;
		$this->day = $day;
	}
	function set_from_excel($exceldate){
		$unixdate = ($exceldate - 25569) * 86400;
		set_from_unix($unixdate);
	}
	function set_from_unix($unixdate){
		$datestring = date("Y-m-d",$unixdate);
		$this->year = substr($datestring,0,4);
		$this->month = substr($datestring,5,2);
		$this->day = substr($datestring,8,2);
	}
	function toexcel($night) {
		if ($night){$x=23;$y=59;$z=59;}
		return 25568 + (mktime($x,$y,$z,$this->day, $this->month, $this->year) / 86400);
	}
	function tounix($night) {
		if ($night){$x=23;$y=59;$z=59;}
		return mktime($x,$y,$z,$this->day, $this->month, $this->year);
	}
}
function exceldate_to_unixedate($exceldate){ return ($exceldate - 25569) * 86400; }
function unixdate_to_exceldate($unixdate){ return 25569 + ($unixdate / 86400); }
$starttime = microtime();
define('CHARSET', 'ISO-8859-1');
session_start();
$application_version_major = "2017-06-12";
$application_version_minor = "14:23";
$month['01']="January";$month['02']="February";$month['03']="March";$month['04']="April";$month['05']="May";$month['06']="June";$month['07']="July";$month['08']="August";
$month['09']="September";$month['10']="October";$month['11']="November";$month['12']="December";
$month['1']="January";$month['2']="February";$month['3']="March";$month['4']="April";$month['5']="May";$month['6']="June";$month['7']="July";$month['8']="August";
$month['9']="September";
$monthy['01']='JAN';$monthy['02']='FEB';$monthy['03']='MAR';$monthy['04']='APR';$monthy['05']='MAY';$monthy['06']='JUN';
$monthy['07']='JUL';$monthy['08']='AUG';$monthy['09']='SEP';$monthy['10']='OCT';
$monthy['11']='NOV';$monthy['12']='DEC';
$monthy['1']='JAN';$monthy['2']='FEB';$monthy['3']='MAR';
$monthy['4']='APR';$monthy['5']='MAY';$monthy['6']='JUN';
$monthy['7']='JUL';$monthy['8']='AUG';$monthy['9']='SEP';

$dateselector = "";
date_default_timezone_set('Europe/London');

$uid = $_SESSION[user_id];
include "../../zm-core-db.php"; $ddb = 'concentrix'; db_connect();
include "../../zm-core-login.php";
include "../../zm-core-functions.php";

// Load all metric vars
	$sql = "SELECT varname,vardef FROM metricvariables ORDER by LENGTH(varname) DESC";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){$metricvars[$row[varname]]=$row[vardef];}

// Load all metric calculations
	$sql = "SELECT metric_id,metric_calculation FROM metrics LIMIT 500";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){$metriccalculations[$row[metric_id]]=$row[metric_calculation];}

// get datefilter
function gf($d,$m,$t) {
	global $monthy,$teamvmfilter;
	if (gg('metrics','metric_vm055','metric_id',$m)) {
		if(substr($d,1,1)=='T'){$d=substr($d,26,15);$d=date("Y-m-d",exceldate_to_unixedate($d));}
		$month=substr($d,5,2);$year=substr($d,0,4);$d=date("Y-m-d",mktime(0,0,0,$month+1,1,$year));
		$y=substr($d,0,4);$c=substr($d,5,2);$c=$monthy[$c];
		$f=" AND (team = (SELECT vmdata_team FROM teams WHERE id='$t') AND month='$c-$y') LIMIT 1";
	}
	else {$f = $teamvmfilter[$t];if($d){if($f){$f=" AND ($d) AND ($f)";}else{$f=" AND ($d)";}}else{if($f){$f=" AND ($f)";}}}
	return $f;
}

// convert date
function cd($a,$b){
	$a = strtotime($a);
	$b = strtotime($b . " +1 day");
	//$c=substr($a,0,4);$d=substr($a,5,2);$e=substr($a,8,2);$f=substr($b,0,4);$g=substr($b,5,2);$h=substr($b,8,2);
	$i = unixdate_to_exceldate($a);
	$j = unixdate_to_exceldate($b);
	//$i=unixdate_to_exceldate(mktime(0,0,0,$d,$e,$c));$j=unixdate_to_exceldate(mktime(0,0,0,$g,$h,$f));
	$s=" Teammate_Contact_Date >= $i";$s.=" AND Teammate_Contact_Date <= $j";return $s;
}
function dd($d){$t=date("Y-m-d");$a=substr($d,0,10);$b=substr($d,10,6);if($a==$t){$a="today";}return "<b>$a</b> @ $b";}
// Show an a href Save the view.
function s($v,$e,$t,$a,$n) {
	if($t==''){$t=$v;}if($a==''){$a=0;}$r=str_repeat("\t",$a)."<a href='' onClick='s(\"$v\",\"$e\")'>$t</a>";
	if($n){$r.="\n";}return $r;
}
function et($t,$w){if($t==''){$t=0;}echo str_repeat("\t",$t).$w."\n";}
function sq($q){global $db;if(!$s=$db->query($q)){cl($q);cl($db->error);}}
function sqr($q){global $db;if(!$s=$db->query($q)){cl($q);cl($db->error);}return $s->fetch_assoc();}

function sqrr($q){global $db;$count=0;if(!$s=$db->query($q)){cl($q);cl($db->error);}while($x=$s->fetch_assoc()){$xx[$count]=$x;$count++;}return $xx;}

// get metric symbol
function gms($m){$r=sqr("SELECT metric_symbol FROM metrics WHERE metric_id='$m' LIMIT 1");return $r[metric_symbol];}
function gmr($m){$r=sqr("SELECT metric_rounding FROM metrics WHERE metric_id = '$m' LIMIT 1");return $r[metric_rounding];}
function ss($s) {
	global $_GET;if(substr($s,-2)=='[]'){$s=substr($s,0,-2);$_GET[$s]=implode(', ',$_GET[$s]);}
	if(isset($_GET[$s])){set_usersetting($s,$_GET[$s]);}
}
function showquote() {
	global $db;
	et(2,"<div class='quote'>");
		et(3,"<div class='quote-pre'>");
			et(4,"What do you like most about PayPal?");
		et(3,"</div>");
		$sql = "SELECT COUNT(Like_most_about_PayPal__LTR_) as moof FROM raw_data WHERE Like_most_about_PayPal__LTR_ <> ''";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row = $result->fetch_assoc();
		$randomrow = rand(1,$row[moof]);
		$sql = "SELECT teammate_nt_id,external_survey_id,teammate_name, Like_most_about_PayPal__LTR_ AS ltr FROM raw_data WHERE Like_most_about_PayPal__LTR_ <> '' LIMIT 1 OFFSET $randomrow";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row = $result->fetch_assoc();
		et(3,"\"".s('survey_details',"b={$row[external_survey_id]}",$row[ltr])."\"");
		et(3,"<div class='quote-source'>");
			et(4,"@".s('teammate_details',"b={$row[teammate_nt_id]}",$row[teammate_name]));
		et(3,"</div>");
	et(2,"</div>");
}
function target($m,$t,$d) {
	$sub = " AND submetric=''";
	$h='high';if(gg('metrics','metric_vm055','metric_id',$m)){$h='low';}$x="target_value_$h";
	$target=sqr("SELECT $x FROM targets WHERE target_class='red' AND target_team_id='$t' AND target_metric_id='$m' AND target_start_date <='$d' AND target_stop_date >= '$d' $sub LIMIT 1");
	return $target[$x];
}
function valuediff($v,$m,$t) { // Value, metric, target
	$metricinfo = sqr("SELECT metric_rounding,metric_lowbest,metric_maxvalue,metric_minvalue FROM metrics WHERE metric_id = '$m' LIMIT 1");

	if ($metricinfo[metric_maxvalue]!=''){
		$range = $metricinfo[metric_maxvalue] - $metricinfo[metric_minvalue];
	}
	else {
		$range = $t;
	}

	// E.g. aht and tr
	if ($metricinfo[metric_lowbest]) {
		$diff = ($v - $t)/$range;
	}
	else {
		$diff = ($v - $t) / $range;
	}
	return $diff;
}

function tclass($v,$m,$t,$d){ // Value, Metric, Team, Date
	$sub = " AND submetric=''";

	$metricinfo = sqr("SELECT metric_rounding FROM metrics WHERE metric_id = '$m' LIMIT 1");

	// Apply the rounding to ensure logical colours displayed.
	$v = round($v,$metricinfo[metric_rounding]);

	$class=sqr("SELECT target_class,target_value_high,target_value_low FROM targets WHERE target_value_low <= '$v+0.001' AND target_value_high >= '$v-0.001' AND target_metric_id='$m' AND target_team_id='$t' AND target_start_date <='$d' AND target_stop_date >= '$d' $sub LIMIT 1");

	$tgt = target($m,$t,$d);

	// need to cache this shit
	$diff = valuediff($v,$m,$tgt);

	// Amber code will slow things down.
	if ($class[target_class]=='red') {
		if (abs($diff) < 0.021) {
			$class[target_class] = 'amber';
		}
	}
	return array($class[target_class],$diff);
}
// Get database data
function g ($tablename, $columnname, $tmpid) {
	global $db;
	$sql = "SELECT $columnname FROM $tablename WHERE id = '$tmpid' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[$columnname];
}
function gg ($tablename, $columnname, $idcolumnname, $tmpid) {
	global $db;
	$sql = "SELECT $columnname FROM $tablename WHERE $idcolumnname = '$tmpid' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[$columnname];
}
function get_usersetting($settingname, $tmpuid) {
	global $user_id, $db;
	if (!$tmpuid) { $tmpuid = $user_id; }
	$sql = "SELECT user_data FROM usersettings WHERE user_id = '$tmpuid' AND user_setting = '$settingname' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[user_data];
}
function set_usersetting($settingname, $settingdata, $tmpuid) {
	global $user_id, $db;
	if (!$tmpuid) { $tmpuid = $user_id; }
	$sql = "SELECT id,user_data FROM usersettings WHERE user_id = '$tmpuid' AND user_setting = '$settingname' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[id]!='') {
		if ($row[user_data]!=$settingdata) {
			$sql = "UPDATE usersettings SET user_data = '$settingdata' WHERE user_id = '$tmpuid' AND user_setting = '$settingname' LIMIT 1 ";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	else {
		$sql = "INSERT INTO usersettings (user_setting,user_data,user_id) VALUES('$settingname','$settingdata','$tmpuid')";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	}

}
function transform_title($t) {$t=str_replace("_"," ",$t);return $t;}
function resolvemetricvar($varname,$date_interval,$filter) {
	global $db;
	$f = $date_interval . $filter;
	//if ($f){$f=" AND ($f)";}
	$sql = "SELECT vardef FROM metricvariables WHERE varname = '$varname' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[vardef]){
		$sql = $row[vardef] . $f;
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		return $row[$varname];
	}
	else { return '--'; }
}
function iscached($m,$t,$d){
	$cachedvalue = sqr("SELECT value,vartrigger FROM statscache WHERE teamid='$t' AND metric_id='$m' AND datestring='$d' LIMIT 1");
	$trigger_stored = $cachedvalue[vartrigger];

	// Check for trigger var before deciding what to return or force a refresh.
	$trigger_actual=restrigger($m,$t,$d);
	if ($trigger_actual != $trigger_stored) {
		// Cache is outdated, let's force a refresh.
		return false;
	}

	if ($cachedvalue){return $cachedvalue[value];}
	return false;
}
function checkcache($m,$t,$d){
	return sqr("SELECT id FROM statscache WHERE teamid='$t' AND metric_id='$m' AND datestring='$d' LIMIT 1");
}
function cachevalue($m,$t,$d,$v) {
	$cacheexists=checkcache($m,$t,$d);
	// Cache already exists. Let's update it.
	$newtrigger=restrigger($m,$t,$d);
	if ($cacheexists>0){
		sq("UPDATE statscache SET value='$v', vartrigger='$newtrigger' WHERE id='$cacheexists'");
	}
	else {
		sq("INSERT INTO statscache (value,vartrigger,metric_id,teamid,datestring) VALUES('$v','$newtrigger', '$m','$t','$d')");
	}
}
function restrigger($m,$t,$d){
	global $teamvmfilter;
	$trigger = sqr("SELECT metric_trigger_var FROM metrics WHERE metric_id='$m' LIMIT 1");
	$tt=substr($trigger[metric_trigger_var],1);
	$resolvedtrigger = resolvemetricvar($tt,gf($d,$m,$t));
	return $resolvedtrigger;
}
function get_value ($metric_id, $d, $t) {
	global $db,$metriccalculations,$metricvars, $teamvmfilter, $monthy;

	// First, check if this value is cached :D
	$is_cached=false;$x=iscached($metric_id, $t, $d);if($x){$is_cached=true;return $x;}
	$f = gf($d,$metric_id,$t);
	$formula_copy = $metriccalculations[$metric_id];
	foreach($metricvars as $variable => $k) {
		$n='$'.rtrim(ltrim($variable));if((strpos($formula_copy,$n)) OR ($n==$formula_copy)) {
			$q=$k.$f;if(!$r=$db->query($q)){cl($q);cl($db->error);}$e=$r->fetch_assoc();
			$formula_copy=str_replace($n,$e[$variable],$formula_copy);
		}
	}
	eval("\$mc = $formula_copy;");
	cachevalue($metric_id, $t, $d, $mc);
	return $mc;
}
function display_metric($v,$m,$t,$d){
	echo "\t\t\t\t\t\t<div";
	if($v!=""){global $startdate;if(!$d){$d=$startdate;}$r=gmr($m);$s=gms($m);
		list($c,$diff)=tclass($v,$m,$t,$d);
		echo " class='metric'>";
		echo  round($v,$r);
		echo "<span class='symbol'>$s</span>";
	}
	elseif ($v===0) {
		echo ">$v";
	}
	else {echo ">--";}
	echo "</div>\n";
}
function display_target($m,$t,$d){
	$tgt = target($m,$t,$d);
	if ($tgt) {
		$s=gms($m);
		et(7,$tgt);
		et(7,$s);
	}
}
function build_team_vmfilter($t) {
	global $db;$f='';$s="SELECT raw_data_column,raw_data_data FROM team_data_definitions WHERE team_id='$t'";
	if(!$result=$db->query($s)){cl($s);cl($db->error);}
	while($row=$result->fetch_assoc()){$f.=$row[raw_data_column]."='".$row[raw_data_data]."' OR ";}
	$f=substr($f,0,-4);return $f;
}

echo "
<!DOCTYPE html>
<html>
	<head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0, minimum-scale=1.0'>
		<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
		<script src='sorttable.js'></script>
		<script src='/construct/modules/zm-construct-chart.js'></script>
		<script src='goldie.js'></script>
		<link rel=stylesheet href='/construct/modules/zm-construct-chart.css''>
		<link rel=stylesheet href='goldie.css''>
		<title>Dashcore</title>
	</head>
	<body onkeydown='checkkey()'>\n\n";

// Are we logged in?
if ($_POST['newpassword']) {
	$newhash = generatehash($_POST['newpassword']);
	$sql = "UPDATE users SET user_hash = '".$newhash."' WHERE user_id = '" . $_POST['userid'] . "'";
	$result = $db->query($sql);
	$sql = "UPDATE users SET user_new = '0' WHERE user_id = '" . $_POST['userid'] . "'";
	$result = $db->query($sql);
	echo "<script>location.href='?';</script>";
}
if ($_POST['username']) {
	if (validatepassword($_POST['username'],$_POST['password'])) {
		//$_SESSION['username'] = $_POST['username'];
    $_SESSION['user_id'] = getuserid($_POST['username']);
	$_SESSION['logged_in'] = true;
	echo "<script>location.href='?';</script>";
	}
	else {
		$login_error = "Incorrect username and/or password.";
	}
}
// Allow new users to set a password
if ($_GET['n']) {
	$sql = "SELECT user_new FROM users WHERE user_id = '".$_GET['n']."'  LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row = $result->fetch_assoc();
	if($row['user_new'] == '1') { showsetnewpassword(); }
	else { showlogin(); }
}
else {
	if (!$_SESSION['logged_in']) { // Not logged in
		showlogin();
	}
	else { // Logged in
		$user_id = $_SESSION['user_id'];
		if (isset($_GET[viewpage])) { $a = $_GET[viewpage]; }
		else { $a = get_usersetting('viewpage'); }
		$b = $_GET[b];$c = $_GET[c];
		$usersettings=sqr("SELECT * FROM users WHERE user_id='$user_id'");
		$startdate=$usersettings[user_startdate];$enddate=$usersettings[user_enddate];
		$datefilter = cd($startdate,$enddate,0);
		$tgtdate = substr($startdate,0,10);
		ss('settings_fullname');ss('settings_ntid');ss('settings_adminlevel');ss('settings_showteams[]');
		ss('settings_dashboardstackchoice');
		ss('settings_dashboardmicrographintervals');
		// Retrieve the user's preferred list of teams.
		$teamarray=explode(', ',get_usersetting('settings_showteams'));
		foreach($teamarray as $k){$teamvmfilter[$k]=build_team_vmfilter($k);}

		et(2,"<div class='overlaybar'>");
			et(3,"<div class='dropdown'>");
			et(3,"<div class='logo'>");
				et(4,"<button onclick='location.href=\"?viewpage=dashboard\";'>&gscr;&oscr;&lscr;&dscr;</button>");
				et(4,"<ul class='dropdown-menu'>");
					et(5,"<li><span><b>Updated:</b></span></li>");
					et(5,"<li><span>Platform: ".dd("$application_version_major $application_version_minor")."</span></li>");
					et(5,"<li><span>Medallia: ".dd(gg('systemsettings','settingvalue','settingname','medalliadata'))."</span></li>");
					et(5,"<li><span>PRT060p: ".dd(gg('systemsettings','settingvalue','settingname','prt060pdata'))."</span></li>");
					et(5,"<li><span>VM055p: ".dd(gg('systemsettings','settingvalue','settingname','vm055data'))."</span></li>");
				et(4,"</ul></div>");
			et(3,"</div>");
et(4,"<div class='topicon adashboard'><a href='?viewpage=dashboard'><img src='images/dashboard.png' title='Home (Dashboard)'></a></div>");

et(4,"<div class='topicon atrends'><a href='?viewpage=trends'><img src='images/trends.png' title='Trends'></a></div>");

et(4,"<div class='topicon a121'><a href='?viewpage=121'><img src='images/1to1.png' title='1to1'></a></div>");

et(4,"<div class='topicon abonus'><a href='?viewpage=bonus'><img src='images/bonus.png' title='Bonus'></a></div>");
et(4,"<div class='topicon acallouts'><a href='?viewpage=callouts'><img  src='images/callouts.png' title='Callouts'></a></div>");
et(4,"<div class='topicon amasters'><a href='?viewpage=masters'><img src='images/masters.png' title='Masters'></a></div>");
et(4,"<div class='topicon unimportant ametrics'><a href='?viewpage=metrics'><img src='images/metrics.png' title='Metrics'></a></div>");
et(4,"<div class='topicon asurveys'><a href='?viewpage=surveys'><img src='images/surveys.png' title='Surveys'></a></div>");
et(4,"<div class='topicon asettings'><a href='?viewpage=settings'><img src='images/settings.png' title='settings'></a></div>");
et(4,"<div class='topicon unimportant atargets'><a href='?viewpage=targets'><img src='images/targets.png' title='Targets'></a></div>");
et(4,"<div class='topicon unimportant aqds'><a href='?viewpage=qds'><img src='images/qds.png' title='QDs'></a></div>");
et(4,"<div class='topicon unimportant apdp'><a href='?viewpage=pdp'><img src='images/pdp.png' title='PDP'></a></div>");
et(4,"<div class='topicon unimportant amystats'><a href='?viewpage=mystats'><img src='images/mystats.png' title='Mystats'></a></div>");
et(4,"<div class='topicon unimportant aideas'><a href='?viewpage=ideas'><img src='images/ideas.png' title='Ideas'></a></div>");
et(4,"<div class='topicon unimportant ateams'><a href='?viewpage=teams'><img src='images/teams.png' title='Teams'></a></div>");
et(4,"<div class='topicon unimportant aemployees'><a href='?viewpage=employees'><img src='images/employees.png' title='Employees'></a></div>");
et(4,"<div class='topicon unimportant ahours'><a href='?viewpage=hours'><img src='images/hours.png' title='Hours'></a></div>");
et(4,"<div class='topicon unimportant acoaching'><a href='?viewpage=coaching'><img src='images/coaching.png' title='Coaching'></a></div>");
et(4,"<div class='topicon unimportant acontracts'><a href='?viewpage=contracts'><img src='images/contracts.png' title='Contracts'></a></div>");
et(4,"<div class='topicon unimportant acalendar'><a href='?viewpage=calendar'><img src='images/calendar.png' title='Calendar'></a></div>");
et(4,"<div class='topicon unimportant acertification'><a href='?viewpage=certification'><img src='images/certification.png' title='Certification'></a></div>");

			// Month
			$displaymonth = date("M");
			$startdates=substr($startdate,0,10);
			$enddates=substr($enddate,0,10);
			et(2,"<div class='date-picker'>");
				et(3,"<div class='date-picker-prevmonth'><a href='?viewpage=$a&b=$b&c=$c&d=prevmonth'><<</a></div>");
				et(3,"<div class='date-picker-monthdisplay'><a href=''>$displaymonth</a></div>");
				et(3,"<div class='date-picker-intervaldisplay'><input type=date value='$startdates'> - <input type=date 	value='$enddates'></div>");
				et(3,"<div class='date-picker-nextmonth'><a href='?viewpage=$a&b=$b&c=$c&d=nextmonth'>>></a></div>");
			et(2,"</div>");
		et(2,"</div>");
		et(0,'');
		et(2,"<div class='fullscreen'>");
		if ($a!='dashboard'){
			et(3,"<div class='wrapper'>");
			et(3,"<h1>" . s($a,"b=$b",transform_title($a)) . "</h1>");
		}
		if ($a=='') { $a = 'dashboard'; }

		if ($a=='masters'){
			$teamvalue = $b;
			$yearvalue = $c;
			$defaultteam = sqr("SELECT user_preferred_team FROM users WHERE user_id = '$user_id'");
			et(4,"These are the <select onChange='fillmasters()' id='teamselector'>");
			et(4,"<option>--</option>");
			$teamdata = sqrr("SELECT * FROM teams ORDER by team_name");
			foreach($teamdata as $keys) {
				$e='';
				if($defaultteam['user_preferred_team']==$keys['id']){$e=' selected';}
				if($b==$keys['id']){$e=' selected';}
				et(5,"<option $e value={$keys[id]}>{$keys[team_race]}</option>");
			}
			et(4,"</select> masters of ");
			$current_year = date("Y");
			$current_month = date("m");

			et(4,"<select onChange='fillmasters()' id='monthselector'>");
			for($m=1;$m<13;$m++) {
				$e='';if($d==$m){$e=' selected';}
				et(5,"<option$e value='$m'>$monthy[$m]</option>");
			}
			et(4,"</select>");

			et(4,"<select onChange='fillmasters()' id='yearselector'>");
			for($year = $current_year; $year > 2012; $year--) {
				$e='';if($c==$year){$e=' selected';}
				et(5,"<option$e>$year</option>");
			}
			et(4,"</select>.");


			// Display the list of masters.
			for ($x = 1; $x < 6; $x++) {
				et(4, "<div class='masters'>");
				et(5, "<div class='masternumbers'>$x</div>");
				et(5, "<div class='mastername'></div>");
				et(4,"</div>");
			}
			et(4,"<script>fillmasters();</script>");
		}

		if ($a=='metricdetail') {
			et(4,"<div class='wrapper'>");
				echo "<h1>".gg("metrics", "metric_longname", "metric_id", $b)."</h1>";
				echo "<div class='metric-graph'>";
					echo "<div class='metric-title'>";
						echo "2017 trend graph";
					echo "</div>";
				echo "</div>";
			echo "</div>";
		}
		elseif ($a=='metric_trigger') {
			et(3,"<h1>".gg("metrics", "metric_longname", "metric_id", $b)."</h1>");
			et(3,"<div class='wrapper'>");
				$trigger=gg("metrics", "metric_trigger_var", "metric_id", $b);
				et(4,"Trigger variable: <input id='trigger' value='$trigger' onkeydown='updatetrigger(this,\"$b\")'>");
			et(3,"</div>");
		}
		elseif ($a=='bonus') {
			et(3,"<h1>Team " . sqr("SELECT team_name FROM teams WHERE id='{$usersettings[user_preferred_team]}' LIMIT 1")[team_name] . "</h1>");
			$bonuscalculations = sqrr("SELECT * FROM bonuscalculations LIMIT 100");
			echo "<div><a href='?viewpage=bonus&b=edit'>Edit</a><br>";
			if ($b=='edit') {
				$c = $_GET[c];
				echo "<div class='editbonus'><h2>Edit bonus</h2>";
				echo "<button onClick='location.href=\"?viewpage=bonus&b=edit&c=addcolumn\"'>Add column to display</button>";
				echo "<button onClick='location.href=\"?viewpage=bonus&b=edit&c=addrow\"'>Choose row to display</button>";
				if ($c=='addcolumn') {
					echo "<h3>Add column</h3>";
					$d = $_GET[d];
					$e = $_GET[e];
					if ($d!='') {
						$tablename = $d;
						$columnname = $e;
						$sql = "INSERT INTO bonuscalculations (actionname, actionvalue) VALUES('$d','$e')";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					}

					$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='raw_data' AND table_schema='concentrix'";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					et(4,"<br>Medallia data: <select name='rawdata_column' id='rawdata_column'>");
					while($row=$result->fetch_assoc()){
						echo "<option>{$row[column_name]}</option>";
					}
					echo "</select><button onClick='location.href=\"?viewpage=bonus&b=edit&c=addcolumn&d=raw_data&e=\" + q(\"rawdata_column\");'>Add</button>";

					$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='ahtreport_data' AND table_schema='concentrix'";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					et(4,"<br>PRT060p data: <select name='prtdata_column' id='prtdata_column'>");
					while($row=$result->fetch_assoc()){
						echo "<option>{$row[column_name]}</option>";
					}
					echo "</select><button onClick='location.href=\"?viewpage=bonus&b=edit&c=addcolumn&d=ahtreport_data&e=\" + q(\"prtdata_column\");'>Add</button>";
				}

				echo "</div>";
			}
			foreach ($bonuscalculations as $key) {
				echo "moo";
			}
			echo "</div>";
		}
		elseif ($a=='metric_calculation') {
			et(3,"<h1>".gg("metrics", "metric_longname", "metric_id", $b)."</h1>");
			et(3,"<div class='wrapper'>");
				et(4,"<div title='Add' class='buttonround'>");
					et(5,"<a href='?viewpage=$a&b=$b&c=new_var'>+</a>");
				et(4,"</div>");
				et(4,"<div title='Run' class='buttonround'>");
					et(5,s($a,"&b=$b&c=runcalc",'Run'));
				et(4,"</div>");
				et(4,"<br><br><br>");
				if ($c=='new_var') {
					et(4,"Save <select name='functions' id='functions'>");
					et(5,"<option>COUNT</option>");
					et(5,"<option>SUM</option>");
					et(5,"<option>AVG</option>");
					et(5,"<option>MIN</option>");
					et(5,"<option>MAX</option>");
					et(5,"<option>SQRT</option>");
					et(5,"<option>ABS</option>");
					et(5,"<option>ACOS</option>");
					et(5,"<option>ASIN</option>");
					et(5,"<option>ATAN</option>");
					et(5,"<option>ATAN2</option>");
					et(5,"<option>CEILING</option>");
					et(5,"<option>COS</option>");
					et(5,"<option>COT</option>");
					et(5,"<option>DIV</option>");
					et(5,"<option>EXP</option>");
					et(5,"<option>FLOOR</option>");
					et(5,"<option>GREATEST</option>");
					et(5,"<option>LEAST</option>");
					et(5,"<option>LN</option>");
					et(5,"<option>LOG</option>");
					et(5,"<option>LOG10</option>");
					et(5,"<option>LOG2</option>");
					et(5,"<option>MOD</option>");
					et(5,"<option>PI</option>");
					et(5,"<option>POWER</option>");
					et(5,"<option>RAND</option>");
					et(5,"<option>ROUND</option>");
					et(5,"<option>SIGN</option>");
					et(5,"<option>SIN</option>");
					et(5,"<option>TAN</option>");
				et(4,"</select> (");
				if (gg('metrics','metric_vm055','metric_id',$b)) { $tablename='vm055_data';}
				elseif (gg('metrics','metric_quality','metric_id',$b)) { $tablename='raw_data';}
				else {$tablename='ahtreport_data';}
				$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='$tablename' AND table_schema='concentrix'";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				et(4,"<select name='metric_column' id='metric_column'>");
					while($row=$result->fetch_assoc()){
						et(5,"<option>".$row[column_name] . '</option>');
					}
				et(4,"</select>) if ");
				$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='$tablename' AND table_schema='concentrix'";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				et(4,"<select name='ifcol' id='ifcol'>");
					while($row=$result->fetch_assoc()){
						et(5,"<option>".$row[column_name] . '</option>');
					}
				et(4,"</select>");
				et(4,"<select id='operator'>");
					et(5,"<option>></option>");
					et(5,"<option>>=</option>");
					et(5,"<option>=</option>");
					et(5,"<option><</option>");
					et(5,"<option><=</option>");
					et(5,"<option><></option>");
					et(5,"<option>LIKE</option>");
					et(5,"<option>IN</option>");
					et(5,"<option>IS NULL</option>");
				et(4,"</select>");
				et(4,"<input id='filter'>");
				et(4," as $<input name='varname' id='varname'><input id='varsaver' type=submit value='Save' onClick='buildmetricvariable(\"$tablename\")'>");
				}
				et(4,"<hr>Defined variables:");
				$sql = "SELECT * FROM metricvariables";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){et(4,"<a href='?viewpage=$a&b=$b&c=test_var&d={$row[id]}'>$".$row[varname]."</a>, ");}
				if($c=='test_var') {
					$d=$_GET[d];
					if ($d) {
						$vardef = g('metricvariables','vardef',$d) . " AND ($datefilter)";
						$varname = g('metricvariables','varname',$d);
						echo '<hr><b>Query</b>: '.$vardef;
						if(!$result=$db->query($vardef)){cl($vardef);cl($db->error);}
						$row=$result->fetch_assoc();
						echo "<br><b>Results</b>: ";
						echo $row[$varname];
					}
				}
					$calculation = gg('metrics','metric_calculation','metric_id',$b);
					et(4,"<hr>Calculation: <input type=hidden id='d' value='$b'><input value='$calculation' id='calculation' size=100>
					<input type=submit id='saver' value='Save' onClick='savecalc()'>");
					if ($c=='runcalc') {echo "<hr>";display_metric(get_value($b,$datefilter),$b);}
			et(3,"</div>");
		}
		elseif ($a=='metric_info') {
			et(3,"<div class='wrapper'>");
				et(4,"<h1>".gg('metrics','metric_longname','metric_id',$b)."</h1>");
				et(4,"<table class='nice-table'>");
					et(5,"<thead>");
						et(6,"<th>Setting</th>");
						et(6,"<th>Data</th>");
					et(5,"</thead>");
					et(5,"<tr>");
						et(6,"<td>");
							et(7,'Name');
						et(6,"</td>");
						et(6,"<td>");
							et(7,gg('metrics','metric_name','metric_id',$b));
						et(6,"</td>");
					et(5,"</tr>");
					et(5,"<tr>");
						et(6,"<td>");
							et(7,'Long name');
						et(6,"</td>");
						et(6,"<td>");
							et(7,gg('metrics','metric_longname','metric_id',$b));
						et(6,"</td>");
					et(5,"</tr>");
					et(5,"<tr>");
						et(6,"<td>");
							et(7,s('metric_calculation',"b=$b",'Metric calculation'));
						et(6,"</td>");
						et(6,"<td>");
							et(7,gg('metrics','metric_calculation','metric_id',$b));
						et(6,"</td>");
					et(5,"</tr>");
					et(5,"<tr>");
						et(6,"<td>");
							et(7,s('metric_trigger',"b=$b",'Metric trigger'));
						et(6,"</td>");
						et(6,"<td>");
							et(7,gg('metrics','metric_trigger_var','metric_id',$b));
						et(6,"</td>");
					et(5,"</tr>");
				et(4,"</table>");
			et(3,"</div>");
		}
		elseif ($a=='administration') {
			et(3,"<div class='wrapper'>");
				et(4,"<h2>Metrics</h2>");
				$sql = "SELECT * FROM metrics";
				et(4,"<table class='nice-table'>");
					et(5,"<thead>");
						et(6,"<th>Name</th>");
						et(6,"<th>Contract</th>");
						et(6,"<th>Calculation</th>");
						et(6,"<th>Symbol</th>");
						et(6,"<th>Rounding</th>");
						et(6,"<th>VM055p metric?</th>");
						et(6,"<th>Long name</th>");
						et(6,"<th>Trigger var</th>");
					et(5,"</thead>");
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					while ($row = $result->fetch_assoc()) {
						et(5,"<tr>");
						et(6,"<td>" . s('metric_info',"b={$row[metric_id]}",$row[metric_name]). "</td>");
						et(6,"<td>".gg('contracts','contract_name','contract_id',$row[contract_id])."</td>");
						et(6,"<td>{$row[metric_calculation]}</td>");
						et(6,"<td>{$row[metric_symbol]}</td>");
						et(6,"<td>{$row[metric_rounding]}</td>");
						et(6,"<td>{$row[metric_vm055]}</td>");
						et(6,"<td>{$row[metric_longname]}</td>");
						et(6,"<td>{$row[metric_trigger_var]}</td>");
						et(5,"</tr>");
					}
				et(4,"</table>");
			et(3,"</div>");
		}
		elseif ($a=='settings') {
				et(4,"<form>");
					et(5,"<table>");
						et(6,"<tr>");
							et(7,"<td>");
								et(8,"Your full name:");
							et(7,"</td>");
							et(7,"<td>");
								et(8,"<input id='settings_fullname' name='settings_fullname' value='".get_usersetting('settings_fullname')."'>");
							et(7,"</td>");
						et(6,"</tr>");
						et(6,"<tr>");
							et(7,"<td>");
								et(8,"Your NT id:");
							et(7,"</td>");
							et(7,"<td>");
								et(8,"<input id='settings_ntid' name='settings_ntid' value='".get_usersetting('settings_ntid')."'>");
							et(7,"<a href='#' onClick='getfullname()'>Get full name</button></td>");
						et(6,"</tr>");
						et(6,"<tr>");
							et(7,"<td>");
								et(8,"Administrator level:");
							et(7,"</td>");
							et(7,"<td>");
								et(8,"<input name='settings_adminlevel' value='".get_usersetting('settings_adminlevel')."'>");
							et(7,"</td>");
						et(6,"</tr>");
						et(6,"<tr>");
							et(7,"<td>");
								et(8,"Show teams:");
							et(7,"</td>");
							et(7,"<td>");
								et(8,"<select multiple name='settings_showteams[]'>");
								$sql = "SELECT id,team_race FROM teams ORDER by team_race";
								if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
								while ($row = $result->fetch_assoc()) {
									et(9,"<option value='{$row[id]}'>{$row[team_race]}</option>");
								}
								et(8,"</select>");
							et(7,"</td>");
						et(6,"</tr>");
						et(6,"<tr>");
							et(7,"<td><b>Dashboard:</b><br> Stack choice:");
								et(8,"</td><td><input type=checkbox> Stacked");
							//ss('settings_dashboardstackchoice');
							et(7,"</td>");
						et(6,"</tr>");
						et(6,"<tr>");
							et(7,"<td>Micrograph intervals:");
								et(8,"</td><td><input id='settings_dashboardmicrographintervals' name='settings_dashboardmicrographintervals'  value='".get_usersetting('settings_dashboardmicrographintervals')."'>");
							et(7,"</td>");
						et(6,"</tr>");
					et(5,"</table>");
					et(5,"<input type=hidden name=a value='settings'>");
					et(5,"<input type=submit>");
				et(4,"</form>");
		}
		elseif ($a=='dashboard') {
			if ($b=='showmetric') {
				$metric_name = $c;
				$sql = "SELECT metric_id FROM metrics WHERE metric_name = '$metric_name'";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				$row = $result->fetch_assoc();
				if ($row[metric_id]!='') {
					$sql = "SELECT metric_id FROM showmetrics WHERE user_id = '$user_id' AND metric_id = '{$row[metric_id]}'";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					$rowb = $result->fetch_assoc();
					if ($rowb[metric_id]=='') {
						$sql = "INSERT INTO showmetrics (metric_id, user_id) VALUES('{$row[metric_id]}','$user_id')";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					}
				}
			}
			// Dashboard

			// TODO: replace with ajax.
			et(3,"<div class='buttonround metric-add' title='Add metric to display'>");
			et(4,"<a href='#' onClick='var x=prompt(\"Metric name:\");location.href=\"?viewpage=dashboard&b=showmetric&c=\"+x;'>+</a>");
			et(3,"</div>");

			// Retrieve the user's preferred list of metrics.
			$sql = "SELECT metrics.metric_vm055,metrics.metric_name, metrics.metric_id FROM metrics INNER JOIN showmetrics ON metrics.metric_id = showmetrics.metric_id WHERE showmetrics.user_id = '$user_id'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){
				et(3,"<div class='cell' id='cell{$row[metric_id]}'>");

				// Close icon
				et(4,"<div class='cell-close'>");
				et(5,"<a onClick='closecell(\"{$row[metric_id]}\")'>X</a>");
				et(4,"</div>");

				// Metric Title
				et(4,"<h1 onClick='location.href=\"?viewpage=metricdetail&b={$row[metric_id]}\";'>{$row[metric_name]}</h1>");

				// Iterate through all teams selected to display.
				foreach($teamarray as $key) {
					// Team Box

					$value = get_value($row[metric_id], $datefilter, $key);
					list($class,$diff)=tclass($value, $row[metric_id],$key,$startdate);
					et(4,"<div class='team-box $class-dark'>");

						// Team Title
						et(5,"<div class='team-box-title $class'>");
							et(6,s('metricdetail',"b={$row[metric_id]}&c=$key",g('teams','team_shortname',$key)));
						et(5,"</div>");

						// Team surveys
						if (!$row[metric_vm055]){
							$f=sqr("SELECT count(external_survey_id) as q FROM raw_data WHERE ($datefilter) AND ({$teamvmfilter[$key]})");
							et(5,"<div class='team-box-surveys' title='Surveys'>");
								et(6,$f[q]);
							et(5,"</div>");
						}

						$valuevar = '';
						$intervals = get_usersetting('settings_dashboardmicrographintervals');
						$anyvalue = false;
						for($i = ($intervals-1); $i > 0; $i--) {
							$tdatefilter = cd("$startdate -$i months","$enddate -$i months",0);
							$tvalue = get_value($row[metric_id], $tdatefilter, $key, 'forcecache');
							if ($tvalue) {
								$valuevar .= round($tvalue,1) . ",";
								$anyvalue = true;
							}
						}
						$valuevar .= round($value,1);
						if ($anyvalue) {
							et(5,"<div class='team-box-microchart' id='mc-{$row[metric_id]}-$key'>");
							echo $valuevar;
							et(5,"</div>");
							et(5,"<script>microchart('mc-{$row[metric_id]}-$key');</script>");
						}

						et(5,"<div class='team-box-value'>");
							display_metric( $value, $row[metric_id], $key);
						et(5,"</div>");

						et(5,"<table class='deltabox'>");
							et(6,"<tr>");
								et(7,"<td class='deltatitle'>");
								et(7,"&ofcir;");
								display_target($row[metric_id], $key, $tgtdate);
								et(7,"</td>");
								et(7,"<td class='deltaleft'>&Delta;</td>");
								et(7,"<td class='deltaright'>".round($diff*100,1)."<span class=symbol>%</span></td>");
							et(6,"</tr>");
						et(5,"</table>");

						// value, deltaagainst, text
						//display_delta($value, )
						echo "<div style='height:100px'></div>";
					et(4,"</div>");
				}
				et(3,"</div>");
			}
		}
		et(2,"</div>\n");
	}
}
showquote();
et(2,"<script src='goldie.js'></script>");
if ($_GET[settings_fullname]){
	echo "<script>s('dashboard','');</script>";
}
et(1,"</body>");
et(0,"</html>");
 ?>
