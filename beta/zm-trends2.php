<?php

function show_module() {
	global $teamdefinition, $ahtteamdefinition;
	global $db, $_GET, $app_action, $b, $c, $d, $e, $f, $team, $sqldater, $ahtteamdefinition, $teamdefinition, $contract, $startdate, $enddate, $startdate_month, $startdate_year, $enddate_month, $enddate_year, $enddate_day, $startdate_day, $month, $monthy;
	$a = $app_action;
	$teamdefinition = getTeamDefinitions($team);
	$ahtteamdefinition = getTeamAhtDefinitions($team);
	if ($team>0){
		$m = $_GET[m];
		$qualityMetric = db_get("SELECT metric_quality FROM metrics WHERE metric_id = '{$m}' LIMIT 1")['metric_quality'];
		$view_startdate_month = $startdate_month;
		$view_startdate_year = $startdate_year;
		$view_enddate_year=$enddate_year;
		$view_enddate_month=$enddate_month;
		$view_startdate_day="01";
		$view_enddate_day = cal_days_in_month(CAL_GREGORIAN,$view_startdate_month,$view_enddate_year);
		$view_startdate = "$view_startdate_year-$view_startdate_month-01";
		$view_enddate = "$view_startdate_year-$view_startdate_month-$view_enddate_day";
		if ($enddate_day!=$view_enddate_day){echo"<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";}
		if ($view_startdate_month!=$view_enddate_month){echo"<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";}


		echo "<h2>12 months rolling until " . $month[$view_startdate_month]." $view_startdate_year</h2>";


		echo "<br>Step 1. Select a metric: ";
		// Dropdown of available metrics.
		$sql = "SELECT metric_id,metric_name FROM metrics WHERE metric_active = '1' ORDER by metric_name ASC";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$contract = 5;
		$x=0;
		echo "<select name=m id=m onChange='location.href=\"?a=$a&b=$b&team=$team&m=\" + this.value;'>";
		echo "<option>--</option>";
		while($row=$result->fetch_assoc()){
			echo "<option value='{$row[metric_id]}'";
			if ($m == $row[metric_id]){echo " selected";}
			echo ">{$row[metric_name]}</option>";
		}
		echo "</select> ";

		echo "<br>";

		if (isset($m)) {
			echo "Step 2. Select a report column: ";

			// Drop down of available columns.
			echo "<select name=c onChange='location.href=\"?a=$a&b=$b&team=$team&m=$m&c=\" + this.value;'>";
			if ($qualityMetric == 1) {
				$sql = "SELECT column_name FROM surveycolumns";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "<option>--</option>";
				while($row=$result->fetch_assoc()){
					echo "<option";
					if ($c==$row[column_name]){echo" selected";}
					echo ">".$row[column_name]."</option>";
				}
			}
			else {
				$sql = "SELECT column_name FROM prt060columns ORDER by column_name";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "<option>--</option>";
				while($row=$result->fetch_assoc()){
					echo "<option";
					if ($c==$row[column_name]){echo" selected";}
					echo ">".$row[column_name]."</option>";
				}
			}
			echo "</select> ";
		}
		// Show or hide agents not part of this team.
		$h = $_GET[h];
		$newh=1;if ($h==1){$newh=0;}
		if
			(
				($c == "Teammate_NT_ID")
				or
				($c == "NTID")
			)
		{
			echo "<span id='hidetms' style='cursor:pointer;' onClick='location.href=\"?a=$a&b=$b&c=$c&m=$m&h=$newh\";'><input ";
			if ($h==1){echo "checked ";}
			echo "type=checkbox> Exclude teammates not part of this team.</span>";
		}
		echo "<br><br>";

		$startyear = $startdate_year;
		$startmonth = $startdate_month - 11;
		if ($startmonth<1){$startmonth=12+$startmonth;$startyear--;}
		$showyear = $startyear;
		$showmonth = $startmonth;

		echo "<table class='tabler sortable' width='100%'>";
		echo "<thead>";
		echo "<tr><th>{$c}</th>";

		for ($x=0;$x<12;$x++){
			if ($showmonth > 12) {
				$showmonth = 1;
				$showyear++;
			}
			$throwme = "{$showyear}-" . str_pad($showmonth,2,"0",STR_PAD_LEFT) . "-";
			$trend_startdate[$x] = "{$throwme}01";
			$trend_enddate[$x] = "{$throwme}" . str_pad(getlastday($showyear,$showmonth),2,"0",STR_PAD_LEFT);
			echo "<th><a href='/?a=$a&c=$c&h=$h&m=$m'>$month[$showmonth]</a>";
			$showmonth++;
			echo "</th>";

			// Get target for this month.
			//$target[$x] = gettarget(5,2,$team,$view_startdate,'low');

		}
		echo "</thead>";

		// Is this KDI etc or AHT?
		if ($qualityMetric == 0) {
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
						else {echo " style='background-color:#ddd;color:#aaa;'>no data";}
						echo " </td>\n";
						$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
					}
					echo "</tr>";
				}
			}
			echo "</table>";
		}
		if (($c!='') && ($m!='')){
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
				if (($_GET[h]) and
				(
					($c == 'Teammate_NT_ID')
					or
					($c == 'NTID')
				)
				)
				{
					$displayme = false;
					if (guessteam($qrow[$c])==$team){ $displayme = true; }
				}
				if ($displayme) {
					echo "<tr><td>";
					echo $qrow[$c];
					echo "</td>";
					$showmonth = $startmonth;
					$showyear = $startyear;
					for ($x=1;$x<13;$x++){
						$ldm=cal_days_in_month(CAL_GREGORIAN,$showmonth,$showyear);
						echo "<td";
						$sde = unixdate_to_exceldate(mktime(0,0,0,$showmonth,1,$showyear));
						$ede = unixdate_to_exceldate(mktime(23,59,59,$showmonth,$ldm,$showyear));
						$tmptimedef = " WHERE teammate_contact_date > '$sde' AND teammate_contact_date < '$ede' ";
						list($qvalue, $qsurveys) = svs($m, " AND $c = '{$qrow[$c]}'", 0,$tmptimedef);
						if ($qsurveys > 0) {
							list($bg,$fg) = targetcolor($qvalue, 5, $m, $team, "$showyear-$showmonth-01");
							echo " style='background-color:#$bg;color:#$fg;' title='$qsurveys'>";
							echo round($qvalue,gmr($m));
							echo gms($m);
						}
						else {
							echo " style='background-color:#ddd;color:#aaa;'>no data";
						}
						echo "</td>";
						$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
					}
					echo "</tr>";
				}
			}
			echo "</table>";
		}
	}
		else {
			echo "This view only works with a team selected.";}
	}
