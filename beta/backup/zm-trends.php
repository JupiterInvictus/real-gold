<?php

function show_module() {
	global $teamdefinition, $ahtteamdefinition;
	global $db, $_GET, $app_action, $b, $c, $d, $e, $f, $team, $sqldater, $ahtteamdefinition, $teamdefinition, $contract, $startdate, $enddate, $startdate_month, $startdate_year, $enddate_month, $enddate_year, $enddate_day, $startdate_day, $month, $monthy;
	$a = $app_action;
	$teamdefinition = getTeamDefinitions($team);
	$ahtteamdefinition = getTeamAhtDefinitions($team);
	if ($team>0){
		$m = $_GET[m];
		$view_startdate_month=$startdate_month;
		$view_startdate_year=$startdate_year;
		$view_enddate_year=$enddate_year;
		$view_enddate_month=$enddate_month;
		$view_startdate_day="01";
		$view_enddate_day = cal_days_in_month(CAL_GREGORIAN,$view_startdate_month,$view_enddate_year);
		$view_startdate = "$view_startdate_year-$view_startdate_month-01";
		$view_enddate = "$view_startdate_year-$view_startdate_month-$view_enddate_day";
		if ($enddate_day!=$view_enddate_day){echo"<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";}
		if ($view_startdate_month!=$view_enddate_month){echo"<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";}
		echo "<h2>12 months rolling until " . $month[$view_startdate_month]." $view_startdate_year</h2>";
		$h = $_GET[h];
		$newh=1;if ($h==1){$newh=0;}
		if ($c == "Teammate_NT_ID"){
			echo "<div id='hidetms' style='cursor:pointer;' onClick='location.href=\"?a=$a&b=$b&c=$c&m=$m&h=$newh\";'><input ";
			if ($h==1){echo "checked ";}
			echo "type=checkbox> Exclude teammates not part of this team.</div>";
		}

		$sql = "SELECT column_name FROM surveycolumns";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "Medallia column: <select name=c onChange='location.href=\"?a=$a&b=$b&team=$team&m=$m&c=\" + this.value;'>";
		echo "<option></option>";
		while($row=$result->fetch_assoc()){
			echo "<option";
			if ($c == $row[column_name]){echo" selected";}
			echo ">".$row[column_name]."</option>";
		}
		echo "</select>";
		$sql = "SELECT metric_id,metric_name FROM metrics WHERE metric_quality = '1' AND metric_active = '1' ORDER by metric_name ASC";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$contract = 5;
		$x=0;
		echo " Quality Metric: <select name=m id=m onChange='location.href=\"?a=$a&b=$b&team=$team&c=$c&m=\" + this.value;'>";
		echo "<option></option>";
		while($row=$result->fetch_assoc()){
		echo "<option value='{$row[metric_id]}'";
		if ($m == $row[metric_id]){echo " selected";}
		echo ">{$row[metric_name]}</option>";
	}
	echo "</select><br>";
	$m2 = $_GET[m2];
	$c2 = $_GET[c2];
	$sql = "SELECT column_name FROM prt060columns ORDER by column_name";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	echo "PRT060P column: <select name=c onChange='location.href=\"?a=$a&b=$b&team=$team&&m2=$m2&m=$m&c2=\" + this.value;'>";
	echo "<option></option>";
	while($row=$result->fetch_assoc()){
		echo "<option";
		if ($c2==$row[column_name]){echo" selected";}
		echo ">".$row[column_name]."</option>";
	}
	echo "</select>";
	$sql = "SELECT metric_id,metric_name FROM metrics WHERE metric_prt060p = '1' AND metric_active = '1' ORDER by metric_name ASC";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$contract = 5;
	$x=0;
	echo " PRT060P Metric: <select name=m2 id=m2 onChange='location.href=\"?a=$a&b=$b&team=$team&c2=$c2&c=$c&m2=\" + this.value;'>";
	echo "<option></option>";
	while($row=$result->fetch_assoc()){
	echo "<option value='{$row[metric_id]}'";
	if ($m2 == $row[metric_id]){echo " selected";}
	echo ">{$row[metric_name]}</option>";
}
echo "</select>";
if (($c2!='') && ($m2!='')){
	echo "<table class='sortable' width='100%'>";
	echo "<thead>";
	echo "<tr><th>$c2</th>";
	$startyear = $startdate_year;
	$startmonth = $startdate_month - 11;
	if ($startmonth<1){$startmonth=12+$startmonth;$startyear--;}
	$showyear = $startyear;
	$showmonth = $startmonth;
	for ($x=0;$x<12;$x++){
		if ($showmonth > 12){$showmonth = 1;$showyear++;}
		echo "<th><a href='?a=$a&b=$b&team=$team&c2=$c2&m2=$m2&c=$c&d=$d&startdate=$showyear-";
		echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
		echo "-01&enddate=$showyear-";
		echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
		echo "-";
		echo str_pad(getlastday($showyear,$showmonth),2,"0",STR_PAD_LEFT);
		echo "'>$month[$showmonth]</a>";
		$showmonth++;
		echo "</th>";

		// Get target for this month.
		//$target[$x] = gettarget(5,2,$team,$view_startdate,'low');

	}
	echo "</tr>";
	echo "</thead>";
	$curmonth = $startdate_month;
	$curyear = $startdate_year;
	$monti = $monthy[$curmonth] . "-" . $curyear;
	$qsql = "SELECT distinct $c2 FROM prt060data WHERE month = '$monti' $ahtteamdefinition ORDER by $c2 ASC";
	$contract = 5;
	$teammate_counter=0;
	if(!$qresult=$db->query($qsql)){cl($qsql);cl($db->error);}
	$contract = 5;$x=0;
	$displayme = true;
	while($qrow=$qresult->fetch_assoc()){
		if ($displayme) {
			echo "<tr><td>";
			if ($c2 == 'NTID') {
 				echo "<a href='?a=surveys&c=Teammate_NT_ID&d={$qrow[$c2]}'>";
			}
		echo $qrow[$c2];
		echo "</td>";
		$showmonth = $startmonth;
		$showyear = $startyear;
		for ($x=1;$x<13;$x++){
			echo "<td";
			$showmonth = str_pad($showmonth,2,"0",STR_PAD_LEFT);
			$qvalue = av($m2,"$showyear-$showmonth-01"," AND $c2 = '{$qrow[$c2]}'");
			if ($qvalue!='--'){
				if ($m2 == 17) { $qvalue *= 100;}
				if ($m2 == 6) { $qvalue *= 100;}
				list($bg,$fg) = targetcolor($qvalue, 5, $m2, $team, "$showyear-$showmonth-01");
				echo " style='background-color:#$bg;color:#$fg;'>";
				echo round($qvalue,gmr($m2));
				echo gms($m2);
			}
			else {echo " style='background-color:#000;color:#aaa;'>";}
			echo " </td>\n";
			$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
		}
		echo "</tr>";
	}
}
				echo "</table>";
			}
			if (($c != '') && ($m != '')){
				echo "<table class='sortable trend-table'>";
				echo "<thead>";
				echo "<tr><th>$c</th>";
				$startyear = $startdate_year;
				$startmonth = $startdate_month - 11;
				if ($startmonth<1){$startmonth=12+$startmonth;$startyear--;}
				$showyear = $startyear;
				$showmonth = $startmonth;
				for ($x=0;$x<12;$x++){
					if ($showmonth > 12){$showmonth = 1;$showyear++;}
					echo "<th><a href='?a=$a&b=$b&team=$team&c=$c&d=$d&startdate=$showyear-";
					echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
					echo "-01&enddate=$showyear-";
					echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
					echo "-";
					echo str_pad(getlastday($showyear,$showmonth),2,"0",STR_PAD_LEFT);
					echo "'>$month[$showmonth]</a>";
					$showmonth++;
					echo "</th>";

					// Get target
					// if kdi or nps one, if the rest another.
					$target[$x] = gettarget(5,$m,$team,$view_startdate,'low');

				}
				echo "</tr>";
				echo "</thead>";
				$m = $_GET[m];
				$qsql = "SELECT distinct $c FROM raw_data $sqldater $teamdefinition ORDER by $c ASC";
				$contract = 5;
				$teammate_counter = 0;
				if(!$qresult = $db->query($qsql)){
					cl($qsql);
					cl($db->error);
				}
				$contract = 5; $x=0;
				$displayme = true;
				while($qrow = $qresult->fetch_assoc()) {
					$displayme = true;
					if (($_GET[h]) and ($c == 'Teammate_NT_ID')){
						$displayme = false;
						if (guessteam($qrow[$c])==$team){ $displayme = true; }
					}
					if ($displayme) {
						echo "<tr><td>{$qrow[$c]}</td>";

						$showmonth = $startmonth;
						$showyear = $startyear;

						for ($x = 1; $x < 13; $x++) {
							$ldm = cal_days_in_month(CAL_GREGORIAN, $showmonth, $showyear);
							echo "<td";
							$sde = unixdate_to_exceldate(mktime(0, 0, 0, $showmonth, 1, $showyear));
							$ede = unixdate_to_exceldate(mktime(23, 59, 59, $showmonth, $ldm, $showyear));
							$tmptimedef = " WHERE teammate_contact_date > '$sde' AND teammate_contact_date < '$ede' ";
							list($qvalue, $qsurveys) = svs($m, " AND $c = '{$qrow[$c]}'", 0,$tmptimedef);
							if ($qsurveys > 0) {
								list($bg,$fg) = targetcolor($qvalue, 5, $m, $team, "$showyear-$showmonth-01");
								echo " style='background-color:#$bg;color:#$fg;";
								if ($qrow[$c] != '') {
									$fontsize = round(7+$qsurveys*0.1,0);
									if ($fontsize > 25) { $fontsize = 25; }
									echo "font-size: {$fontsize}pt;";
								}
								echo "' title='$qsurveys'>";
								echo round($qvalue,gmr($m));
								echo gms($m);
							}
							else {
								echo " style='background-color:#444;color:#666;'>";
							}
							echo "</td>";
							$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
						}
						echo "</tr>";
					}
				}
				echo "</table>";
			}
			else {
				echo "<br><br>Pick a data column and metric.";
			}
		}
		else{
			echo "This view only works with a team selected.";}
	}
