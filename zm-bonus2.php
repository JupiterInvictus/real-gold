<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	if ($team>0){
		if (ismgr()){echo "<a href='?a=$a&startdate=$startdate&enddate=$enddate&team=$team'>View</a> / "; echo "<a href='?a=$a&b=e&startdate=$startdate&enddate=$enddate&team=$team'>Edit</a>";}
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
		if(ismgr()){
			if ($_GET[c]=='saved'){
				for($x=1;$x<=$_GET[tms];$x++){
					$t="tm$x";
					$h="hw$x";
					$s="si$x";
					$tmname[$x]= $_GET[$t];
					$tmhw[$x]=$_GET[$h];
					$tmsi[$x]=$_GET[$s];
					$sql = "SELECT teammate_nt_id FROM hours WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}' LIMIT 1";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					else {
						$row=$result->fetch_assoc();
						if ($row[teammate_nt_id]==''){
							$sql = "INSERT INTO hours (teammate_nt_id,date,worked_hours,sick_instances) VALUES('{$tmname[$x]}','{$_GET[month]}','{$tmhw[$x]}','{$tmsi[$x]}')";
							if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						}
						else {
							$sql = "UPDATE hours SET worked_hours = '{$tmhw[$x]}' WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}'";
							if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
							$sql = "UPDATE hours SET sick_instances = '{$tmsi[$x]}' WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}'";
							if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						}
					}
				}
			}
			echo "<form method=get>";
		}
		if(1) {
			echo "<table  class='sortable' id='bonustable' cellpadding=0 cellspacing=0>";
			echo "<thead><tr>
			<th class=rotated>Position</th>
			<th>Name</th>
			<th>Surname</th>
			<th class=rotated>Phone<br>Answered</th>
			<th class=rotated>Phone<br>Surveys</th>
			<th class=rotated>Email Worked</th>
			<th class=rotated width=10>Email Surveys</th>
			<th class=rotated width=10>Phone AHT</th>
			<th class=rotated width=10>Email AHT</th>
			<th class=rotated width=10>Phone KDI</th>
			<th class=rotated width=10>Phone CrRR</th>
			<th class=rotated width=10>Phone NPS</th>
			<th class=rotated width=10>Email KDI</th>
			<th class=rotated width=10>Email CrRR</th>
			<th class=rotated width=10>Email NPS</th>
			<th class=rotated width=10>Kicker: NPS</th>
			<th class=rotated width=10 id='ppth'>Performance Points</th>
			<th class=rotated width=10>Hours Worked</th>
			<th class=rotated width=10>Bonus before sick</th>";
			if (ismgr()){ echo "<th class=rotated width=10>Sick Instances</th><th class=rotated width=10>Bonus Paid Out</th>"; }
			echo "</tr></thead><tfoot>";
			echo "<tr>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td><b>TARGET</b></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td>".getsubtarget(5,2,$team,$view_startdate,'phone')."</td>"; // Phone AHT
			echo "<td>".getsubtarget(5,2,$team,$view_startdate,'email')."</td>"; // Email AHT
			echo "<td>".getsubtarget(5,5,$team,$view_startdate,'phone')."</td>"; // Phone KDI
			echo "<td>".getsubtarget(5,3,$team,$view_startdate,'phone')."</td>"; // Phone CrRR
			echo "<td>".getsubtarget(5,4,$team,$view_startdate,'phone')."</td>"; // Phone NPS
			echo "<td>".getsubtarget(5,5,$team,$view_startdate,'email')."</td>"; // Email KDI
			echo "<td>".getsubtarget(5,3,$team,$view_startdate,'email')."</td>"; // Email CrRR
			echo "<td>";
			echo  getsubtarget(5,4,$team,$view_startdate,'email');
			echo "</td>"; // Email NPS
			$npstarget = gettarget(5,4,$team,$view_startdate, "high");
			echo "<td>$npstarget</td>";
			echo "<td>98%</td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "</tr></tfoot><tbody>";
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
					$bd[phonecrrr][$teammate[$x]] = sv(3,$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
					$bd[phonenps][$teammate[$x]] = sv(4,$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
					$bd[phoneanswered][$teammate[$x]] = av("answered",$startdate,$ahtdef);
					$bd[phoneaht][$teammate[$x]] = av("paht",$startdate,$ahtdef);
					$bd[hours][$teammate[$x]] = gethours($teammate[$x],$startdate);
					$bd[instances][$teammate[$x]] = getinstances($teammate[$x],$startdate);
					$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%EMAIL'";
					$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Email'";
					$bd[emailkdi][$teammate[$x]] = sv(5,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
					$bd[emailcrrr][$teammate[$x]] = sv(3,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
					$bd[emailnps][$teammate[$x]] = sv(4,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
					$bd[emailworked][$teammate[$x]] = av("worked",$startdate,$ahtdef);
					$bd[emailaht][$teammate[$x]] = av("eaht",$startdate,$ahtdef);

					$combinedsurveydefinition = " AND teammate_nt_id = '{$teammate[$x]}'";
					$bd[combinedsurveys][$teammate[$x]] = $bd[phonesurveys][$teammate[$x]] + 	$bd[emailsurveys][$teammate[$x]];
					$bd[nps][$teammate[$x]] = sv(4,$combinedsurveydefinition,$bd[combinedsurveys][$teammate[$x]]);

					$weight_paht = min(1.3, getsubtarget(5,2,$team,$view_startdate,'phone')/$bd[phoneaht][$teammate[$x]]);
					$weight_eaht = min(1.3, getsubtarget(5,2,$team,$view_startdate,'email')/$bd[emailaht][$teammate[$x]]);
					$weight_pkdi=0;  if ($bd[phonekdi][$teammate[$x]]){$weight_pkdi = min(1.3, max(0.7,$bd[phonekdi][$teammate[$x]]/getsubtarget(5,5,$team,$view_startdate,'phone')));}
					$weight_pcrrr=0; if ($bd[phonecrrr][$teammate[$x]]){$weight_pcrrr = min(1.3, max(0.7,$bd[phonecrrr][$teammate[$x]]/getsubtarget(5,3,$team,$view_startdate,'phone')));}
					$weight_pnps=0;  if ($bd[phonenps][$teammate[$x]]){$weight_pnps = min(1.3, max(0.7,$bd[phonenps][$teammate[$x]]/getsubtarget(5,4,$team,$view_startdate,'phone')));}

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
				if (guessteam($tm)!=$team){
				}
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
					// Email Worked
					echo "<td>{$bd[emailworked][$tm]}</td>";
					// Email Surveys
					echo "<td>{$bd[emailsurveys][$tm]}</td>";
					// Phone AHT
									list($bg,$fg)=targetcolor($bd[phoneaht][$tm], $contract, 2, $team, $view_startdate,"phone");
					echo "<td	 style='color:#$fg;background-color:#$bg'>";
									echo round($bd[phoneaht][$tm],0);
									echo "</td>";
					// Email AHT
									list($bg,$fg)=targetcolor($bd[emailaht][$tm], $contract, 2, $team, $view_startdate,"email");
					echo "<td	 style='color:#$fg;background-color:#$bg'>";
					echo round($bd[emailaht][$tm],0);
					echo "</td>";
					// Phone KDI
									list($bg,$fg)=targetcolor($bd[phonekdi][$tm], $contract, 5, $team, $view_startdate,"phone");
					echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[phonekdi][$tm]}</td>";
					// Phone CrRR
									list($bg,$fg)=targetcolor($bd[phonecrrr][$tm], $contract, 3, $team, $view_startdate,"phone");
					echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[phonecrrr][$tm]}</td>";
					// Phone NPS
									list($bg,$fg)=targetcolor($bd[phonenps][$tm], $contract, 4, $team, $view_startdate,"phone");
					echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[phonenps][$tm]}</td>";
					// Email KDI
									list($bg,$fg)=targetcolor($bd[emailkdi][$tm], $contract, 5, $team, $view_startdate,"email");
					echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[emailkdi][$tm]}</td>";
					// Email CrRR
									list($bg,$fg)=targetcolor($bd[emailcrrr][$tm], $contract, 3, $team, $view_startdate,"email");
					echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[emailcrrr][$tm]}</td>";
					// Email NPS
									list($bg,$fg)=targetcolor($bd[emailnps][$tm], $contract, 4, $team, $view_startdate,"email");
					echo "<td	style='color:#$fg;background-color:#$bg'>{$bd[emailnps][$tm]}</td>";

					// Kicker
									list($bg,$fg)=targetcolor($bd[nps][$tm], $contract, 4, $team, $view_startdate);
					echo "<td style='color:#$fg;background-color:#$bg'>{$bd[nps][$tm]}</td>";
					// Performance Points
								list($bg,$fg)=targetcolor($bd[performancepoints][$tm], $contract, 11,5);
					echo "<td	class=pptd style='color:#$fg;background-color:#$bg'>";
					echo round($bd[performancepoints][$tm]*100,2);
					echo "%</td>\n";
					// Hours Worked
					echo "<td>";
					if ($im && $b=='e'){
						echo "<input class='editor' size=3 name='hw$counti' value='{$bd[hours][$tm]}'>";
						echo "<input type=hidden name='tm$counti' value='$tm'>";
					}
					else{	echo $bd[hours][$tm]; }
					echo "</td>";
					// Bonus before sick
					$bonusbeforesick[$tm] = $bd[hours][$tm] * 1.22;
					if ($bd[performancepoints][$tm]<=0.98){$bonusbeforesick[$tm]=0;}
					elseif ($bd[performancepoints][$tm]<=1.02){$bonusbeforesick[$tm]*=0.25;}
					elseif ($bd[performancepoints][$tm]<=1.06){$bonusbeforesick[$tm]*=0.5;}
					echo "<td";
									if (($bd[nps][$tm]>=$npstarget) && ($bonusbeforesick[$tm]>0)){
										$bonusbeforesick[$tm]*=1.2;
										echo " style='color:#06f'>";
										echo round($bonusbeforesick[$tm],0);
									}
									elseif ($bonusbeforesick[$tm]>0) {
										echo " style='color:#0f0'>";
										echo round($bonusbeforesick[$tm],0);
									}
									else {
										echo ">--";
									}
									echo "</td>";
					// Sick instances
									if (ismgr()){
										echo "<td";
										if ($bd[instances][$tm]==1){echo " style='color:#f80;'";}
										if ($bd[instances][$tm]>1){echo " style='color:#f00;'";}
										echo ">";
										if ($im&&$b=='e'){
							echo "<input class='editor' size=3 name='si$counti' value='{$bd[instances][$tm]}'>";
						}
									else{
							echo $bd[instances][$tm];
						}
										echo "</td>";
										$multo = 1;
										if ($bd[instances][$tm]>0){$multo='0.5';}
										if ($bd[instances][$tm]>1){$multo='0';}
										$bonuspaidout[$tm] = round($bonusbeforesick[$tm]*$multo,0);
									}
					// Bonus paid out
									if (ismgr()){
										echo "<td";
										if ($bonuspaidout[$tm]>0){
							echo " style='color:#0f0;'>{$bonuspaidout[$tm]}";
						}
										else {
												echo ">--";
						}
										echo "</td>";
									}
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
