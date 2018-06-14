<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	if ($team>0){
		echo "Team " . getteamname($team) . "<br><br>";
	}
	if ($b=="show"){
		$showhide = "";
	}
	else {
		$onemonthago = unixdate_to_exceldate(mktime(0,0,0,$currentmonth-1,1,$currentyear));
		$showhide = "AND teammate_contact_date > $onemonthago";
	}
	$sql = "SELECT teammate_name,teammate_contact_date,teammate_nt_id, count(external_survey_id) as surveys FROM raw_data WHERE teammate_nt_id <> '' $showhide GROUP by teammate_nt_id ORDER BY teammate_name ASC";

	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$tm_count = 0;
$recommendedstartdate = date("Y-m-d",mktime(0,0,0,$currentmonth-3,1,$currentyear));
$recommendedenddate = date("Y-m-d");
	echo "<a href='?a=$a&b=show'>Display ex-employees</a> | ";
echo "<a href='?a=$a&b=$b&startdate=$recommendedstartdate&enddate=$recommendedenddate'>Recommended interval</a><br><br>";
	echo "<table class='sortable bt' id='queuelist' cellspacing=0 cellpadding=0><thead>";
echo "<th>Rank</th>";
echo "<th></th>";
	echo "<th>Name and Title</th>";
echo "<th>Level</th>";
	echo "</tr></thead>";

	// Define EP for each action
	$surveyxp = 1;
	$callxp = 1;
	$emailxp = 1;
	$EP_contact_tracking = 1;

$levelxp = 30;
$rankskill = 0;

$generalrankmultiplier = 1;

	$PVPPoints_KDI = $generalrankmultiplier * 1;
	$PVPPoints_NPS = $generalrankmultiplier * 2;
	$PVEPoints_CrRR = $generalrankmultiplier * 1;

$date_start_month = substr($startdate,5,2);
$date_start_day = substr($startdate,8,2);
$date_start_year = substr($startdate,0,4);

$date_end_month = substr($enddate,5,2);
$date_end_day = substr($enddate,8,2);
$date_end_year = substr($enddate,0,4);

$date_start_unix = mktime(0,0,0,$date_start_month, $date_start_day, $date_start_year);
$date_end_unix = mktime(23,59,59,$date_end_month, $date_end_day, $date_end_year);

$date_start_raw = unixdate_to_exceldate($date_start_unix);
$date_end_raw = unixdate_to_exceldate($date_end_unix);

// Starting values
$ahtsqltext = '';

$ahtstartyear = substr($startdate,0,4);
$ahtstartmonth =  substr($startdate,5,2);
$ahtendyear = substr($enddate,0,4);
$ahtendmonth =  substr($enddate,5,2);

$notfinished = true;
while ($notfinished) {
$notfinished = false;
}

//$ahtdates = "AND month >= '" . $monthy[substr($date_start,5,2)] . "-".substr($date_start,0,4)."'
//AND month <= '" . $monthy[substr($date_end,5,2)] . "-".substr($date_end,0,4)."'  ";
//echo $ahtdates;
$exceldates = "AND teammate_contact_date >= '$date_start_raw' AND teammate_contact_date <= '$date_end_raw'";

	while ($row=$result->fetch_assoc()){
$experiencepoints = 0;
			if (($team<0) || (guessteam($row[teammate_nt_id])==$team)){
				$pvppoints = 0; $kdi = 0; $tm_count++;

	// Contact tracking
	$sqlb = "SELECT count(contact_tracking_reason) as ctr from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' $exceldates";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$rowb=$resultb->fetch_assoc();
				$ctr = $rowb[ctr] *  $EP_contact_tracking;
	$experiencepoints = $ctr;
	//$experiencepoints = 0;

	// Contacts
				$sqlb = "SELECT SUM(Phone_Answered) as pa, SUM(Email_replied) as er FROM prt060data WHERE NTID = '{$row[teammate_nt_id]}' AND Queue_Name <> 'Total'  $ahtdates";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$rowb=$resultb->fetch_assoc();
				$ep = (($row[surveys]*$surveyxp) + ($rowb[pa]*$callxp) + ($rowb[er]*$emailxp));
	$experiencepoints += $ep;

				// NPS Skill (PVP);
				$sqlb = "SELECT count(likely_to_recommend_paypal) as nps from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND likely_to_recommend_paypal > 8 $exceldates";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$rowb=$resultb->fetch_assoc();
				$nps_good = $rowb[nps];
				$sqlb = "SELECT count(likely_to_recommend_paypal) as nps from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND likely_to_recommend_paypal < 7 $exceldates";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$rowb=$resultb->fetch_assoc();
				$pvppoints = ($nps_good - $rowb[nps])*$PVPPoints_NPS;

				// KDI Skill (PVP);
				$sqlb = "SELECT count(KDI___email) as ekdi, count(KDI___phone) as pkdi from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND (kdi___phone > 70 OR kdi___email > 70) $exceldates";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$rowb=$resultb->fetch_assoc();
				$kdi = $rowb[ekdi] + $rowb[pkdi];
	$pvppoints = $pvppoints + ($kdi*$PVPPoints_KDI);

	// KDI Skill (PVP);
				$sqlb = "SELECT count(KDI___email) as ekdi, count(KDI___phone) as pkdi from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND (kdi___phone < 70 OR kdi___email < 70) $exceldates";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$rowb=$resultb->fetch_assoc();
	$kdi = $rowb[ekdi] + $rowb[pkdi];
				$pvppoints = $pvppoints - $kdi*$PVPPoints_KDI;


				// CrRR Skill (PVE);
				$sqlb = "SELECT count(Issue_resolved) as ir from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND issue_resolved = 'Yes' $exceldates";
	//echo $sqlb . '<br><br>';
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$rowb=$resultb->fetch_assoc();
				$pvepoints = ($rowb[ir]*$PVEPoints_CrRR);
				$sqlb = "SELECT count(Issue_resolved) as ir from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND issue_resolved <> 'Yes' $exceldates";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$rowb=$resultb->fetch_assoc();
				$pvepoints = $pvepoints - ($rowb[ir]*$PVEPoints_CrRR);

	//$level = round($experiencepoints / $levelxp,0);
	$level = round(sqrt($experiencepoints/$levelxp),0);
	//$knowledgelevel = round($pvepoints/$level/4.2,0);
	//$softlevel = round($pvppoints/$level/3.45,0);
	//$rank = round(($pvepoints + $pvppoints)/($ctr*$rankskill),0);
	$rank = round(sqrt($pvepoints+$pvppoints),0);
	//$rank = round(($softlevel + $knowledgelevel - 24)/1.4,0);
	//if ((($knowledgelevel+$softlevel) > 24) && ($level>9)) {
	//if ((($knowledgelevel+$softlevel) > 2) && ($level>1)) {
	$bgc = round($rankcc * $rank,0);
	$titleid = g('ranks','titleid',$rank);
	$rankname = g('titles','title',$titleid);
	$rankposition = g('titles','left_or_right',$titleid);
	$guessteam = guessteam($row[teammate_nt_id]);
	$race = g('teams','team_race',$guessteam);
	$racefgcolor = g('teams','team_fgcolor',$guessteam);
	$racebgcolor = g('teams','team_bgcolor',$guessteam);
	$raceborder = g('teams','team_border',$guessteam);
	$racelinkcolor = g('teams','team_linkcolor',$guessteam);

	echo "<tr ";
	//echo "style='background:rgba(0, 0, " . (55+$bgc) . ",0.8);'";
	echo "style='background-color: #$racebgcolor; color: #$racefgcolor;'";
	echo ">";
	echo "<td>";
	if ($level<10) {
		//echo "($rank)";
	}
	else { echo $rank; }
	//echo " ($pvppoints/$pvepoints/$ctr) ";
	echo "</td>";
	echo "<td>";
	if ($level>9) {
		if ($rank>0) {
		echo "<img src='{$path}ranks/rank$rank.png' width=32 height=32>";
	}
	}
	echo "</td>";
					echo "<td>";
	echo "<a class='name-text' style='color:#$racelinkcolor' href='?a=levels&c={$row[teammate_nt_id]}'>";
	if ($level>9) {
		if ($rankname && ($rankposition=='left')) { echo "<span class='rank-text'>$rankname</span> "; }
	}
	echo "{$row[teammate_name]}";
	if ($level>9) {
		if ($rankname && ($rankposition=='right')) { echo "<span class='rank-text'>$rankname</span>"; }
	}
	echo "</a></td>";
	echo "<td>";
	echo "<span class='level-text'>$level</span> ";
	echo "</td>";
	echo "<td>";
	echo "<span class='race-text' style='color:#$racebgcolor;
	text-shadow: 2px 2px 0px #$raceborder, -1px -1px 10px #$raceborder;
	background-color:#$racefgcolor;'>$race</span> ";
	echo "<span class='class-text'>$class</span> ";
	echo "</td>";
					echo "</tr>\n\n";
	//}
			}
	}
	echo "</table>";
}
