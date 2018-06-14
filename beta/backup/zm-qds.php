<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
		$lastday=cal_days_in_month(CAL_GREGORIAN,$startdate_month,$startdate_year);
		$pstartdate_year = $startdate_year;
		$pstartdate_month = $startdate_month - 1;
		if ($startdate_month == '01') { $pstartdate_year--; $pstartdate_month = '12'; }
		$plastday = cal_days_in_month(CAL_GREGORIAN,$pstartdate_month,$pstartdate_year);
		$previous_month = date("m",mktime(0,0,0,$startdate_month-1,1,$startdate_year));
		$previous_year = date("Y",mktime(0,0,0,$startdate_month-1,1,$startdate_year));
		$startdate_excel = unixdate_to_exceldate(mktime(0,0,0,$startdate_month,1,$startdate_year));
		$enddate_excel = unixdate_to_exceldate(mktime(23,59,59,$startdate_month,$lastday,$startdate_year));
		$pstartdate_excel = unixdate_to_exceldate(mktime(0,0,0,$startdate_month-1,1,$startdate_year));
		$penddate_excel = unixdate_to_exceldate(mktime(23,59,59,$startdate_month-1,$plastday,$startdate_year));
		$previous_date = "$previous_year-$previous_month-01";
		$sqldater = " WHERE Teammate_Contact_Date > $startdate_excel";
		$sqldater .= " AND Teammate_Contact_Date < $enddate_excel";
		$psqldater = " WHERE Teammate_Contact_Date > $pstartdate_excel";
		$psqldater .= " AND Teammate_Contact_Date < $penddate_excel";
		$ahttgt = gettarget(5,2,$team,$startdate,"low");
		$crrrtgt = gettarget(5,3,$team,$startdate,"high");
		$npstgt = gettarget(5,4,$team,$startdate,"high");
		$kditgt = gettarget(5,5,$team,$startdate,"high");
		if ($b=='aq'){
			echo "Last month, <a href='?a=employees&b=show_employee&c=$c'>$c</a>'s biggest opportunity was {$_GET[e]}.<br>";
			echo "The following is a table of all surveys where {$_GET[e]} was not met.<br>";
			echo "Click on one of the surveys to mark it as reviewed:<br><br>";
			if ($_GET[e]=='KDI'){
				$kpifilter = "AND ((kdi___email < $kditgt AND kdi___phone = '') OR (kdi___email = '' AND kdi___phone < $kditgt))";
			}
			$sql = "SELECT kdi___email,kdi___phone,external_survey_id FROM raw_data $sqldater AND teammate_nt_id = '$c' $kpifilter ORDER by external_survey_id LIMIT 10";
			cl($sql);
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$counta = 0;
			while($row=$result->fetch_assoc()){
				$counta++;
				echo "$counta. <a href='?a=qds&b=aqq&c={$row[external_survey_id]}'>{$row[external_survey_id]}</a><br>";
			}
			echo "<br>";
		}
		echo "QD overview for Team $teamname in $month[$startdate_month] $startdate_year.<br>";
		$sql = "SELECT distinct Teammate_NT_ID,Teammate_Name FROM raw_data $sqldater ORDER by Teammate_Name ASC";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$empnumber = 0;
		echo "<table id=qdtable cellspacing=0 class='tabler'>";
		echo "<thead>";
		echo "<tr><th rowspan=2>Pos</th><th rowspan=2>Teammate</th>
		<th colspan=3>AHT</th>
		<th colspan=3>KDI</th>
		<th colspan=3>CrRR</th>
		<th colspan=3>NPS</th>
		<th colspan=8>QD</th>
		</tr>";
		echo "<tr>
		<th>$previous_month</th>
		<th>$startdate_month</th>
		<th>&Delta;</th>
		<th>$previous_month</th>
		<th>$startdate_month</th>
		<th>&Delta;</th>
		<th>$previous_month</th>
		<th>$startdate_month</th>
		<th>&Delta;</th>
		<th>$previous_month</th>
		<th>$startdate_month</th>
		<th>&Delta;</th>
		<th class=qdth>#1</th>
		<th class=qdth>#2</th>
		<th class=qdth>#3</th>
		<th class=qdth>#4</th>
		<th class=qdth>#5</th>
		<th class=qdth>#6</th>
		<th class=qdth>#7</th>
		<th class=qdth>#8</th>
		</tr>";
		echo "</thead>";
		while($row=$result->fetch_assoc()){
			$bo_metric="";
			$bo_value=0;
			$empteam=guessteam($row[Teammate_NT_ID]);
			if ($team>-1){
				if ($empteam == $team) {
					$empnumber++;
					echo "<tr>";
					echo "<td>$empnumber.</td>";
					echo "<td>".substr($row[Teammate_Name],0,11)."</td>";

					//aht
					$aht = av("paht",$startdate,"AND ntid = '$row[Teammate_NT_ID]'");
					$rsqldater = $sqldater;$sqldater = $psqldater;
					$paht = av("paht",$previous_date,"AND ntid = '$row[Teammate_NT_ID]'");
					$sqldater = $rsqldater;
					$delta = round($aht-$paht,0);
					$deltaproc = round(100*$delta/$paht);
					if ($deltaproc>$bo_value){$bo_value=-$deltaproc;$bo_metric="AHT";}

					echo "<td";
					echo ">".round($paht)."</td>";
					echo "<td";
					echo ">".round($aht)."</td>";
					echo "<td style='background-color:#";
					if ($delta>0){echo '660000;color:#ff0000;';}
					elseif ($delta<0){echo '006600;color:#00ff00;';}
					echo "'>$delta</td>";

					//kdi
					$surveys = countsurveys($row[Teammate_NT_ID]);
					$kdi = sv(5,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
					$rsqldater = $sqldater;

					$sqldater = $psqldater;
					$psurveys = countsurveys($row[Teammate_NT_ID]);
					$pkdi = sv(5,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);

					$sqldater = $rsqldater;
					$delta = round($kdi-$pkdi,0);
					$deltaproc = round(100*$delta/$pkdi);
					if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="KDI";}
					echo "<td";
					echo ">".round($pkdi)."</td>";
					echo "<td";
					echo ">".round($kdi)."</td>";
					echo "<td style='background-color:#";
					if ($delta>0){echo '006600;color:#00ff00;';}
					elseif ($delta<0){echo '660000;color:#ff0000;';}
					echo "'>$delta</td>";

					//crrr
					$crrr = sv(3,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
					$rsqldater = $sqldater;$sqldater = $psqldater;
					$pcrrr = sv(3,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);
					$sqldater = $rsqldater;
					$delta = round($crrr-$pcrrr,0);
					$deltaproc = round(100*$delta/$pcrrr);
					if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="CrRR";}
					echo "<td";
					echo ">".round($pcrrr)."</td>";
					echo "<td";
					echo ">".round($crrr)."</td>";
					echo "<td style='background-color:#";
					if ($delta>0){echo '006600;color:#00ff00;';}
					elseif ($delta<0){echo '660000;color:#ff0000;';}
					echo "'>$delta</td>";

					//nps
					$nps = sv(4,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
					$rsqldater = $sqldater;$sqldater = $psqldater;
					$pnps = sv(4,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);
					$sqldater = $rsqldater;
					$delta = round($nps-$pnps,0);
					$deltaproc = round(100*$delta/$pnps);
					//if ($nps>$pnps){$deltaproc=abs($deltaproc);}
					if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="NPS";}
					echo "<td";
					echo ">".round($pnps)."</td>";
					echo "<td";
					echo ">".round($nps)."</td>";
					echo "<td style='background-color:#";
					if ($delta>0){echo '006600;color:#00ff00;';}
					elseif ($delta<0){echo '660000;color:#ff0000';}
					echo "'>$delta</td>";

					$sqla = "SELECT external_survey_id,qd_id FROM qds WHERE user_id = '$uid' AND teammate_nt_id = '{$row[Teammate_NT_ID]}' AND month = '$startdate_year-$startdate_month' LIMIT 4";
					if(!$resulta=$db->query($sqla)){cl($sqla);cl($db->error);}
					$qds = 0;
					while($rowa=$resulta->fetch_assoc()){
						$qds++;
						echo "<td><a href='?a=qds&b=q&c={$rowa[qd_id]}'>{$rowa[qd_id]}</a></td>";
					}
					for ($x=$qds+1;$x<5;$x++){
						echo "<td><a href='?a=qds&b=aq&c={$row[Teammate_NT_ID]}&d=$startdate_year-$startdate_month&e=$bo_metric'>Add</a></td>";
					}
					echo "</tr>";
				}
			}
		}

	}
