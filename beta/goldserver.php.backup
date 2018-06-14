<?php
session_start();

include "config.php";

include "$path../../zm-core-db.php"; $ddb = 'concentrix'; db_connect();
//
$application_version_major = "2017-08-07";
$application_version_minor = "09:19";
$uid = $_SESSION[user_id];
$user_id = $uid;
header('Content-Type: text/plain');
$a = $_GET['a'];
$b = $_GET['b'];
$c = $_GET['c'];
$d = $_GET['d'];
$e = $_GET['e'];
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

function exceldate_to_unixedate($exceldate){ return ($exceldate - 25569) * 86400; }
function unixdate_to_exceldate($unixdate){ return 25569 + ($unixdate / 86400); }

function cd($a,$b){
	$a = strtotime($a);
	$b = strtotime($b . " +1 day");
	$i = unixdate_to_exceldate($a);
	$j = unixdate_to_exceldate($b);
	$s=" Teammate_Contact_Date >= $i";$s.=" AND Teammate_Contact_Date <= $j";return $s;
}
function cl($t){
	echo "error:$t\n";
}
function sq($q){global $db;if(!$s=$db->query($q)){echo $q;echo $db->error;}}
function sqr($q){global $db;if(!$s=$db->query($q)){cl($q);cl($db->error);}return $s->fetch_assoc();}

function sqrr($q){
	global $db;
	$count = 0;
	if(!$sx = $db->query($q)){
		cl($q);
		cl($db->error);
	}
	else {
	while($x = $sx->fetch_assoc()){
		$xx[$count] = $x;
		$count++;
	}
	}
	return $xx;
}

function build_team_ahtfilter($t) {
	if ($t==''){return;}
	$d=sqrr("SELECT * FROM team_aht_definitions WHERE team_id=$t");
	$a='';
	foreach($d as $q){
		if ($a){$a.=' OR ';}
		$a .= $q[ahtreport_data_column] . "='{$q[ahtreport_data_data]}'";
	}
	$a = "($a) AND ";
	return $a;
}

function build_team_vmfilter($t) {
	global $db;$f='';$s="SELECT raw_data_column,raw_data_data FROM team_data_definitions WHERE team_id='$t'";
	if(!$result=$db->query($s)){cl($s);cl($db->error);}
	while($row=$result->fetch_assoc()){$f.=$row[raw_data_column]."='".$row[raw_data_data]."' OR ";}
	$f=substr($f,0,-4);return $f;
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

if ($uid) {
	//echo "$uid told me $a on '$b' with extra '$c'\n";
	if ($a=='removemetric') {
		// Hide a metric from display
		if ($b > 0) {
			$sql = "DELETE FROM showmetrics WHERE user_id = '$uid' AND metric_id = '$b'";
			if(!$result=$db->query($sql)){echo($sql);echo($db->error);}

		}
	}

	if($a == 'seat') {
		$xcoord = $_GET['xcoord'];
		$ycoord = $_GET['ycoord'];
		$rotation = $_GET['rotation'];
		$teamid = $_GET['teamid'];
		$ntid = $_GET['ntid'];
		$o = sqr("SELECT rotation, ntid, teamid FROM seating WHERE xcoord = '$xcoord' AND ycoord = '$ycoord'");

		if ($rotation == -1) {
			sq("DELETE FROM seating WHERE xcoord = '$xcoord' AND ycoord = '$ycoord'");
		}
		else {
			if ($rotation == "-2") { $rotation = $o[rotation]; }
			if ($teamid == "-2") { $teamid = $o[teamid]; }
			if ($ntid == "-2") { $ntid = $o[ntid]; }
			sq("DELETE FROM seating WHERE xcoord = '$xcoord' AND ycoord = '$ycoord'");
			sq("INSERT INTO seating (xcoord, ycoord, rotation, ntid, teamid) VALUES('$xcoord', '$ycoord', '$rotation', '$ntid', '$teamid')");
		}
	}

	if ($a=='getteam'){
		$q = sqrr("SELECT teammate_nt_id FROM users_teams WHERE team_id = $b");
		foreach($q as $f => $k) {
			echo $k[teammate_nt_id] . ",";
		}

	}

	if ($a=='saveview'){
		// Updated the saved view.
		if ($b != '') {
			set_usersetting('viewpage',$b);
		}
	}
	if ($a=='masters') {
		$teamname = $b;
		$yearvalue = $c;
		$positionvalue = $d;
		$monthvalue = $e;
		$teamdefinition = build_team_vmfilter($teamname);

		// get full list of teammates
		$datedefinition = cd("$c-01-01", "$c-12-31");
		$forcerefresh = true;

		// if the year is not the current year, only pull cache, don't refresh data.
		//if ($c != date("Y")) { $forcerefresh = false; }

// TODO: Check if stats are cached for masters.

	if ($forcerefresh) {
		$queuefilter = build_team_ahtfilter($b);

		// Get a list of all teammates for this month and year of this team.
			$s = "SELECT distinct ntid FROM ahtreport_data WHERE $queuefilter (month = '".$monthy[$monthvalue]."-$yearvalue') order by ntid";
			if(!$result=$db->query($s)){cl($s);cl($db->error);}
			$teammate_counter = 0;
			while($row=$result->fetch_assoc()){
				$teammate[$teammate_counter] = $row['ntid'];
				$teammate_counter++;
			}

		// Loop through tms to get a list of contacts, aht
		for ($x = 0; $x <= $teammate_counter; $x++) {
			$s = "SELECT phone_rcr, contacts_handled,total_aht_secs, (contacts_handled * total_aht_secs) as totalhandlingtime FROM ahtreport_data WHERE $queuefilter (month = '".$monthy[$monthvalue]."-$yearvalue') AND ntid = '".$teammate[$x]."'";
			if(!$result=$db->query($s)){cl($s);cl($db->error);}
			while($row=$result->fetch_assoc()){
				$tminfo[total_contacts_handled][$teammate[$x]] += $row[contacts_handled];
				$tminfo[total_handling_time][$teammate[$x]] += $row[totalhandlingtime];
			}
			// Calculate AHT for all relevant queues.
			$tminfo[combined_aht][$teammate[$x]] = $tminfo[total_handling_time][$teammate[$x]] / $tminfo[total_contacts_handled][$teammate[$x]];

			// Use 400 seconds as AHT benchmark and lifetime target.
			$target_lifetime_aht = 400;
			$tminfo[aht_multiplier][$teammate[$x]] = (( round($target_lifetime_aht/$tminfo[combined_aht][$teammate[$x]],2) - 1)/2)+1;


			///////////////////////////////////
			// Calculate the master level.

			// 1 is the base level.
			$tminfo[master_level][$teammate[$x]] = 1;

			// Apply the AHT multiplier
			$tminfo[master_level][$teammate[$x]] *= $tminfo[aht_multiplier][$teammate[$x]];
		}

		// Sort the teammates in reverse order by master level.
		arsort($tminfo[master_level]);
		$counter = 0;
		foreach($tminfo['master_level'] as $ntid => $points) {
			if ($counter == $d) {
				echo "<span class=skilllevel>" . 	round($points,2) . "</span> &nbsp;";
				echo "<b>$ntid</b> ";

			}
			$counter++;
		}


		/*foreach($tmqueueinfo as $key) {
			$tm = $key['ntid'];
			$tminfo['ntid'][$tm] = $tm;
			$tminfo['total_handling_time'][$tm] += ($key['total_aht_secs'] * $key['contacts_handled']);
			$tminfo['total_contacts_handled'][$tm] += $key['contacts_handled'];
		}*/

		// Calculate total_handling_time and total_contacts_handled
		/*arsort($tminfo['total_contacts_handled']);

		$counter = 0;
		foreach($tminfo as $tmview) {
			if ($counter == $d) {
				$ff = $tmview['ntid'];
				echo sqr("SELECT teammate_name FROm raw_data WHERE teammate_nt_id='$ff' 	LIMIT 1")[teammate_name];
				echo " / " . $tmview['total_contacts_handled'];
				echo " / " . round($tmview['total_handling_time'],0);
			}
			$counter++;
		}*/
	}

	else {
		$teamtext=''; if ($b!='--'){ $teamtext = " AND teamid = '$b'"; }
		$masterscache = sqrr("SELECT (sp/ep*100) as esr, ntid, ep, sp FROM masters_cache WHERE year = '$c' $teamtext ORDER by esr DESC LIMIT 5");

	/*	$teammates = sqrr("
				SELECT COUNT(external_survey_id) as surveys, teammate_name, teammate_nt_id
				FROM raw_data
				WHERE
						($teamdefinition)
					AND
						($datedefinition)
				GROUP BY teammate_name
				ORDER BY surveys DESC
				LIMIT 100");
		foreach($teammates as $key) {
			$tminfo[$key['teammate_nt_id']] = $key;
		}
*/
	}
		if ($masterscache[$d]['ntid']) {
			//echo "<span class=skilllevel>" . 	round($masterscache[$d]['esr'],0) . "</span> &nbsp;";
			//echo "<b>" . sqr("SELECT teammate_name FROM raw_data WHERE teammate_nt_id = '".$masterscache[$d]['ntid']."' LIMIT 1")['teammate_name']. "</b> " . $masterscache[$d]['sp'] . "/" . $masterscache[$d]['ep'];

		}




	}




	if ($a=='getfullname'){
		echo sqr("SELECT teammate_name FROm raw_data WHERE teammate_nt_id='$b' LIMIT 1")[teammate_name];
	}

	if ($a=='updatetrigger') {
		if ($b != '') {
			$var = $b;
			$metric = $_GET[c];
			$sql = "UPDATE metrics SET metric_trigger_var = '$var' WHERE metric_id = '$metric'";
			if(!$result=$db->query($sql)){echo($sql);echo($db->error);}
			echo $sql;
		}
	}
	if ($a=='savemetriccalculation') {
		// Save the metric calculation.
		if ($b != '') {
			$medalliacalc = $b;
			$c = $_GET[c];
			$b = str_replace("'","\'",$b);
			$sql = "UPDATE metrics SET metric_calculation = '$b' WHERE metric_id = '$c'";
			if(!$result=$db->query($sql)){echo($sql);echo($db->error);}
		}
	}
	if ($a=='savemetricvar') {
		// Save a new metric variable
		if ($b != '') {
			$c = str_replace("'","\'",$c);
			$sql = "INSERT INTO metricvariables (varname, vardef) VALUES('$b','$c')";
			if(!$result=$db->query($sql)){echo($sql);echo($db->error);}
		}
	}
}
else {
	echo "Unauthorized";
}

?>
