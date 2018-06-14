<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	if ($team>0){
		$view_startdate_month=$startdate_month;
		$view_startdate_year=$startdate_year;
		$view_enddate_year=$enddate_year;
		$view_enddate_month=$enddate_month;
		$view_startdate_day="01";
		$view_enddate_day = cal_days_in_month(CAL_GREGORIAN,$view_startdate_month,$view_enddate_year);
		$view_startdate = "$view_startdate_year-$view_startdate_month-01";
		$view_enddate = "$view_startdate_year-$view_startdate_month-$view_enddate_day";
		if ($enddate_day != $view_enddate_day){
			echo "<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
		}
		if ($view_startdate_month != $view_enddate_month){
			echo "<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
		}
		echo "<h2>Month: " . $month[$view_startdate_month]." $view_startdate_year</h2>";
		echo "<form method=get>";
		if(1) {
			echo "<table  class='sortable' id='bonustable' cellpadding=0 cellspacing=0>";
			echo "<thead><tr>
			<th class=rotated>Position</th>
			<th>Name</th>
			<th>Surname</th>
			<th class=rotated>Phone<br>Answered</th>
			<th class=rotated>Phone<br>Surveys</th>
			<th class=rotated>Phone AHT</th>
			<th class=rotated>Email Worked</th>
			<th class=rotated>Email Surveys</th>
			<th class=rotated>Email AHT</th>
			<th class=rotated>Phone KDI</th>
			<th class=rotated>RCR</th>
			<th class=rotated>Phone TR</th>
			<th class=rotated>Email KDI</th>
			<th class=rotated>Email TR</th>
			<th class=rotated>NPS</th>";
			echo "</tr></thead>";
			echo "<tbody>";
			$sql = "SELECT distinct teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition ORDER by teammate_nt_id ASC";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$contract = 5;
			$teammate_counter=0;
			while($row=$result->fetch_assoc()){
				$teammate_counter++; $teammate[$teammate_counter]=$row[teammate_nt_id];
				$teammatename[$row[teammate_nt_id]]=$row[teammate_name];

				// Count emails.
				$sqlb="SELECT distinct external_survey_id FROM raw_data $sqldater $teamdefinition AND teammate_nt_id = '{$row[teammate_nt_id]}' AND queue_source_name LIKE '%EMAIL'";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$survey_counter=0;
				while($rowb=$resultb->fetch_assoc()){$survey_counter++;}
				$bd[emailsurveys][$row[teammate_nt_id]]=$survey_counter;

				// Count phone
				$sqlb="SELECT distinct external_survey_id FROM raw_data $sqldater $teamdefinition AND 	teammate_nt_id = '{$row[teammate_nt_id]}' AND queue_source_name LIKE '%VOICE'";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$survey_counter=0;
				while($rowb=$resultb->fetch_assoc()){$survey_counter++;}
				$bd[phonesurveys][$row[teammate_nt_id]]=$survey_counter;
			}
			$im=ismgr();

			// Bonus weighting
			$sql = "SELECT * FROM bonus_weighting";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){$bonus_weight[$row[metric_id]]=$row[weight];}
			for($x=1;$x<=$teammate_counter;$x++){
				if (guessteam($teammate[$x])==$team){
					$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%VOICE'";
					$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Voice'";
					$bd[phonekdi][$teammate[$x]] = 	sv(5,$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
					$bd[phoneanswered][$teammate[$x]] = av("answered",$startdate,$ahtdef);
					$bd[phonetr][$teammate[$x]] = av("tr",$startdate,$ahtdef)*100;
					$bd[phoneaht][$teammate[$x]] = av("paht",$startdate,$ahtdef);
					$bd[rcr][$teammate[$x]] = av(17, $startdate, $ahtdef)*100;
					$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%EMAIL'";
					$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Email'";
					$bd[emailkdi][$teammate[$x]] = sv(5,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
					$bd[emailworked][$teammate[$x]] = av("worked",$startdate,$ahtdef);
					$bd[emailtr][$teammate[$x]] = av("tr",$startdate,$ahtdef)*100;
					$bd[emailaht][$teammate[$x]] = av("eaht",$startdate,$ahtdef);
					$combinedsurveydefinition = " AND teammate_nt_id = '{$teammate[$x]}'";
					$bd[combinedsurveys][$teammate[$x]] = $bd[phonesurveys][$teammate[$x]] + 	$bd[emailsurveys][$teammate[$x]];
					$bd[nps][$teammate[$x]] = sv(4,$combinedsurveydefinition,$bd[combinedsurveys][$teammate[$x]]);
					$weight_paht = min(1.3, getsubtarget(5,2,$team,$view_startdate,'phone')/$bd[phoneaht][$teammate[$x]]);
					$weight_eaht = min(1.3, getsubtarget(5,2,$team,$view_startdate,'email')/$bd[emailaht][$teammate[$x]]);
					$weight_pkdi=0;  if ($bd[phonekdi][$teammate[$x]]){$weight_pkdi = min(1.3, max(0.7,$bd[phonekdi][$teammate[$x]]/getsubtarget(5,5,$team,$view_startdate,'phone')));}
					$weight_pcrrr=0; if ($bd[phonecrrr][$teammate[$x]]){$weight_pcrrr = min(1.3, max(0.7,$bd[phonecrrr][$teammate[$x]]/getsubtarget(5,3,$team,$view_startdate,'phone')));}
					$weight_pnps = 0;
					if (($bd[phonenps][$teammate[$x]]<0) or ($bd[phonenps][$teammate[$x]]>0) or ($bd[phonenps][$teammate[$x]]==0)){
						$weight_pnps = min(1.3, max(0.7,$bd[phonenps][$teammate[$x]]/getsubtarget(5,4,$team,$view_startdate,'phone')));
					}
					$weight_ekdi=0;  if ($bd[emailkdi][$teammate[$x]]){$weight_ekdi = min(1.3, max(0.7,$bd[emailkdi][$teammate[$x]]/getsubtarget(5,5,$team,$view_startdate,'email')));}
					$weight_ecrrr=0; if ($bd[emailcrrr][$teammate[$x]]){$weight_ecrrr = min(1.3, max(0.7,$bd[emailcrrr][$teammate[$x]]/getsubtarget(5,3,$team,$view_startdate,'email')));}
					$weight_enps=0;  if ($bd[emailnps][$teammate[$x]]){$weight_enps = min(1.3, max(0.7,$bd[emailnps][$teammate[$x]]/getsubtarget(5,4,$team,$view_startdate,'email')));}
					$combinedaht = ($weight_paht*$bd[phoneanswered][$teammate[$x]] + $weight_eaht*$bd[emailworked][$teammate[$x]]) / ($bd[phoneanswered][$teammate[$x]]+$bd[emailworked][$teammate[$x]]);
					$combinedkdi = ($weight_pkdi*$bd[phonesurveys][$teammate[$x]] + $weight_ekdi*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
					$combinedcrrr = ($weight_pcrrr*$bd[phonesurveys][$teammate[$x]] + $weight_ecrrr*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
					$combinednps = ($weight_pnps*$bd[phonesurveys][$teammate[$x]] + $weight_enps*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
					$bd[performancepoints][$teammate[$x]] = $combinedaht*$bonus_weight[2] + $combinedkdi*$bonus_weight[5] + $combinedcrrr*$bonus_weight[3] + $combinednps*$bonus_weight[4];
				}
			}
			arsort($bd[performancepoints]);
			$counti = 0;
			foreach($bd[performancepoints] as $tm => $points){
				if (guessteam($tm)!=$team){}
				else {
					$counti++;
					echo "<tr>";
					// Position
					echo "<td>$counti</td>";
					// Name
					$spacer = strpos($teammatename[$tm]," ");
					$name = substr($teammatename[$tm],0,$spacer);
					$surname = substr($teammatename[$tm],$spacer,strlen($teammatename[$tm])-$spacer);
					echo "<td class=tmname>$name</td>";
					echo "<td class=tmname>$surname</td>";
					// Phone answered
					echo "<td>{$bd[phoneanswered][$tm]}</td>";
					// Phone surveys
					echo "<td>{$bd[phonesurveys][$tm]}</td>";
					// Phone AHT
					list($bg,$fg)=targetcolor($bd[phoneaht][$tm], $contract, 2, $team, $view_startdate,"phone");
					echo "<td	 style='color:#$fg;background-color:#$bg'>";
					echo round($bd[phoneaht][$tm],0);
					echo "</td>";
					// Email Worked
					echo "<td>{$bd[emailworked][$tm]}</td>";
					// Email Surveys
					echo "<td>{$bd[emailsurveys][$tm]}</td>";

					// Email AHT
					list($bg,$fg)=targetcolor($bd[emailaht][$tm], $contract, 2, $team, $view_startdate,"email");
					echo "<td	 style='color:#$fg;background-color:#$bg'>";
					echo round($bd[emailaht][$tm],0);
					echo "</td>";

					// Phone KDI
					list($bg,$fg)=targetcolor($bd[phonekdi][$tm], $contract, 5, $team, $view_startdate,"phone");
					echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[phonekdi][$tm]}</td>";

					// RCR
					list($bg,$fg)=targetcolor($bd[rcr][$tm], $contract, 17, $team, $view_startdate,"phone");
					echo "<td	 style='color:#$fg;background-color:#$bg'>".round($bd[rcr][$tm],2)."%</td>";

					// Phone TR
					list($bg,$fg)=targetcolor($bd[phonetr][$tm], $contract, 6, $team, $view_startdate,"phone");
					echo "<td	 style='color:#$fg;background-color:#$bg'>".round($bd[phonetr][$tm],2)."%</td>";

					// Email KDI
					list($bg,$fg)=targetcolor($bd[emailkdi][$tm], $contract, 5, $team, $view_startdate,"email");
					echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[emailkdi][$tm]}</td>";

					// Email TR
					list($bg,$fg)=targetcolor($bd[emailtr][$tm], $contract, 5, $team, $view_startdate,"email");
					echo "<td	 style='color:#$fg;background-color:#$bg'>". round($bd[emailtr][$tm],2)."%</td>";

					// NPS
					echo "<td	style='color:#$fg;background-color:#$bg'>{$bd[nps][$tm]}</td>";
					echo "</tr>\n";

				}
			}
			echo "</tbody></table>";
			if(ismgr()){
				if ($b=='e'){
					echo "<input type=submit><input type=hidden name=c value='saved'><input type=hidden name=a value=$a><input type=hidden name=b value=$b>
					<input type=hidden name=tms value=$counti><input type=hidden name=month value='$startdate_year-$startdate_month'></form>";
				}
			}
		}
	}
	else{echo "This view only works with a team selected.";}
}
