<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate, $month, $startdate_year, $startdate_month, $currentmonth, $currentyear;

	$a = $_GET['a'];
	$team_definition = $teamdefinition;
	$team_id = $team;


	echo "<a href='?a=$a'>View</a> | ";
	echo "<a href='?a=$a&b=$b&x=1'>Edit</a>";
	$x = $_GET[x];
	$latestsurvey = getlatest($team_definition);
	echo "<div id='message'>";
	$date1 = new DateTime($today);
	//$date2 = new DateTime($latestsurvey);
	//$interval = $date1->diff($date2);
	echo "<br><br><b>Call Outs</b>";
	echo "<ul>";
	// Metrics
	$aht = vm(2,$team_id, $startdate);
	$ahttarget = gettarget(5,2,$team_id, $startdate,"low");

	$kdi = getvalue(5,$startdate,$enddate);
	$kditarget = gettarget(5,5,$team_id,$startdate,"high");

	$rcr = vm(17, $team_id, $enddate);
	$rcrtarget = gettarget(5, 17, $team_id, $startdate, "low");

	$nps = getvalue(4,$startdate,$enddate);;
	$npstarget = gettarget(5,4,$team_id,$startdate,"high");

	$pvol = vm("pvol",$team_id,$startdate); $evol = vm("evol",$team_id,$startdate);
	$ptr = vm("ptr",$team_id,$startdate); $etr = vm("etr",$team_id,$startdate);
	$tr = round((($pvol*$ptr + $evol*$etr)/($pvol + $evol))*100,1);
	$trtarget = gettarget(5,6,$team_id,$startdate,"low");
	$ahtmet = -1;$kdimet = -1;$rcrmet = -1;$npsmet = -1;$trmet = -1;
	if ($aht <= $ahttarget) { $ahtmet = 1; }
	if ($kdi >= $kditarget) { $kdimet = 1; }
	if ($rcr <= $rcrtarget) { $rcrmet = 1; }
	if ($nps >= $npstarget) { $npsmet = 1; }
	if ($tr <= $trtarget) { $trmet = 1; }
	if ($c == "removeaction") {
		if ($d) {
			$sql = "DELETE FROM monthlyactions WHERE id = '$d'";
			if(!$result = $db->query($sql)) {
				cl($sql);
				cl($db->error);
			}
		}
	}
	$sql = "SELECT * FROM calloutactions";
	if(!$result = $db->query($sql)){
		cl($sql);
		cl($db->error);
	}
	while ($row = $result->fetch_assoc()) {
		$actions[$row[id]] = $row['actiontext'];
	}
	if ($c == "addactiondone") {
		$sql = "SELECT id FROM monthlyactions WHERE teamid = '$team_id' AND year = '$startdate_year' AND month = '$startdate_month' AND calloutactionid = '$d' AND metricid = '{$_GET[m]}' LIMIT 1";
		if(!$result = $db->query($sql)) {
			cl($sql);
			cl($db->error);
		}
		$row = $result->fetch_assoc();
		if ($row['id'] == ''){
			db_set("INSERT INTO monthlyactions (calloutactionid,teamid,year,month,metricid) VALUES('{$d}','{$team_id}','{$startdate_year}','{$startdate_month}','{$_GET[m]}')");
		}
	}

	echo "<li>AHT "; if ($ahtmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($aht-$ahttarget,0)."s / ".round((($aht-$ahttarget)/$ahttarget)*100,0)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==2)){
			echo "<select onChange='location.href=\"?a=team&b=$team_id&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($ahtmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=2&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '2'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }

	echo "<li>KDI "; if ($kdimet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($kdi-$kditarget,1)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==5)){
			echo "<select onChange='location.href=\"?a=team&b=$team_id&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($kdimet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=5&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team_id' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '5'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }
	echo "<li>RCR "; if ($rcrmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($rcr-$rcrtarget,1)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==17)){
			echo "<select onChange='location.href=\"?a=team&b=$team_id&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($rcrmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=17&x=1'>Add action</a>";}
	}
	echo "</li>";

	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team_id' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '17'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }
	echo "</li>";
	echo "<li>NPS "; if ($npsmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($nps-$npstarget,1)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==4)){
			echo "<select onChange='location.href=\"?a=team&b=$team_id&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($npsmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=4&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team_id' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '4'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }

	echo "</li>";
	echo "<li>TR "; if ($trmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($tr-$trtarget,1)."%)";
	if ($x) {
		if (($c=='addaction') && ($_GET[m]==6)){
			echo "<select onChange='location.href=\"?a=team&b=$team_id&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
			echo "<option></option>";
			$sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while ($row=$result->fetch_assoc()){
				echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
			}
			echo "</select>";
		}
		elseif ($trmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=6&x=1'>Add action</a>";}
	}
	echo "</li>";
	$sql = "SELECT * FROM monthlyactions WHERE teamid = '$team_id' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '6'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$actios=0;
	while($row=$result->fetch_assoc()){
		$actios++;
		if ($actios==1){echo "<ul>"; }
		echo "<li>";
		if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
		echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
	}
	if ($actios>0){echo "</ul>"; }
	echo "</li>";
	echo "</li>";
	echo "</ul>";
	echo "<br><b>Focus Areas</b>";
	echo "<hr>";
	echo "<h1>Top 5 contact reasons</h1>";

	$total_surveys = db_get("SELECT count(external_survey_id) as esi FROM raw_data $sqldater $teamdefinition")['esi'];
	$query = "SELECT contact_tracking_reason, count(contact_tracking_reason) as ctr FROM raw_data $sqldater $teamdefinition GROUP BY contact_tracking_reason ORDER BY ctr DESC LIMIT 5";
	if(!$result=$db->query($query)){cl($query);cl($db->error);}
	echo "<table class='no-padding'>";
	echo "<thead><tr><th rowspan=2>Surveys</th><th rowspan=2>Contact tracking reason</th>
	<th colspan=2>Combined</th>
	<th></th>
	<th colspan=2>Phone</th>
	<th></th>
	<th colspan=2>Email</th>
	</tr>
	<tr>
	<th>KDI</th>
	<th>NPS</th>
	<th></th>
	<th>KDI</th>
	<th>NPS</th>
	<th></th>
	<th>KDI</th>
	<th>NPS</th>
	</tr>
	</thead>";

	while($row=$result->fetch_assoc()){
		echo "<tr>";
		echo "<td><b>" . round($row['ctr'] / $total_surveys* 100) . "</b>% <i>({$row['ctr']})</i>";
		echo "</td>";
		echo "<td><a href='?a=surveys&c=contact_tracking_reason&d={$row['contact_tracking_reason']}'>{$row['contact_tracking_reason']}</a></td>";


		// Combined
		$kdi = sv(5," AND contact_tracking_reason='{$row['contact_tracking_reason']}' ",$total_surveys);
		list($bg,$fg)=targetcolor($kdi, 5, 5, $team, $startdate);
		echo "<td style='background:#$bg;color:#$fg' class='combined'>$kdi</td>";
		$nps = sv(4," AND contact_tracking_reason='{$row['contact_tracking_reason']}' ",$total_surveys);
		list($bg,$fg) = targetcolor($nps, 5, 4, $team, $startdate);
		echo "<td style='background:#$bg;color:#$fg' class='combined'>$nps</td>";

		echo "<td></td>";

		// Phone
		$kdi = sv(5," AND contact_tracking_reason='{$row['contact_tracking_reason']}' AND queue_source_name LIKE '%voice' ",$total_surveys);
		list($bg,$fg)=targetcolor($kdi, 5, 5, $team, $startdate);
		echo "<td style='background:#$bg;color:#$fg'>$kdi</td>";
		$nps = sv(4," AND contact_tracking_reason='{$row['contact_tracking_reason']}' AND queue_source_name LIKE '%voice' ",$total_surveys);
		list($bg,$fg) = targetcolor($nps, 5, 4, $team, $startdate);
		echo "<td style='background:#$bg;color:#$fg'>$nps</td>";

		echo "<td></td>";

		// Email
		$kdi = sv(5," AND contact_tracking_reason='{$row['contact_tracking_reason']}' AND queue_source_name LIKE '%email'",$total_surveys);
		list($bg,$fg)=targetcolor($kdi, 5, 5, $team, $startdate);
		echo "<td style='background:#$bg;color:#$fg'>$kdi</td>";
		$nps = sv(4," AND contact_tracking_reason='{$row['contact_tracking_reason']}' AND queue_source_name LIKE '%email' ",$total_surveys);
		list($bg,$fg) = targetcolor($nps, 5, 4, $team, $startdate);
		echo "<td style='background:#$bg;color:#$fg'>$nps</td>";


		echo "</tr>";
	}
	echo "</table>";

	echo "<h1>Top 5 detractors</h1>";
	echo "<h1>Underperformers<h1>";
}
