<?php
function show_module() {
	global $db, $_GET, $app_action, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate, $startdate_month, $startdate_year, $enddate_month, $enddate_year, $enddate_day, $startdate_day, $month, $bad_color, $good_color, $great_color;

	$a = $app_action;
		// A survey id has been supplied
		if ($e!=''){
			$sql = "SELECT * FROM raw_data WHERE external_survey_id = '$e'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			echo "<h2>Survey $e</h2>";
			echo "<table>";
			echo "<tr><td>Account number</td><td><a href='https://admin.paypal.com/cgi-bin/admin?node=loaduserpage_home&account_number={$row[Customer_Account_ID]}'>{$row[Customer_Account_ID]}</a></td></tr>";
			echo "<tr><td>Teammate name:</td><td>{$row[Teammate_Name]}</td></tr>";
			echo "<tr><td>Teammate NT ID:</td><td>{$row[Teammate_NT_ID]}</td></tr>";
			echo "<tr><td>Teammate tenure</td><td>{$row[Teammate_Tenure]}</td></tr>";
			echo "<tr><td>Team leader name:</td><td>{$row[Team_Leader_Name]}</td></tr>";
			echo "<tr><td>Response date:</td><td>".date("D M jS y, H:i:s", exceldate_to_unixedate($row['Response_Date']))."</td></tr>";
			echo "<tr><td>Contact date:</td><td>".date("D M jS y, H:i:s", exceldate_to_unixedate($row['Teammate_Contact_Date']))."</td></tr>";
			echo "<tr><td>Queue:</td><td>{$row[Queue_Source_Name]}</td></tr>";
			echo "<tr><td>Contact tracking reason:</td><td>{$row[Contact_Tracking_Reason]}</td></tr>";
			echo "<tr><td>UCID</td><td>{$row[Work_Item_Phone_UCID]}</td></tr>";
			echo "<tr><td>ASAT</td><td>{$row[Teammate_Satisfaction__ASAT_]}</td></tr>";
			echo "<tr><td>LTR</td><td>{$row[Likely_to_recommend_PayPal]}</td></tr>";
			echo "<tr><td>Issue Resolved</td><td>{$row[Issue_resolved]}</td></tr>";
			echo "<tr><td>Customer effort to handle issue</td><td>{$row[Customer_Effort_to_Handle_Issu]}</td></tr>";
			echo "<tr><td>Reason for contact</td><td>{$row[Reason_for_contact]}</td></tr>";
			echo "<tr><td>Handled professionally</td><td>{$row[Handled_professionally]}</td></tr>";
			echo "<tr><td>Showed genuine interest</td><td>{$row[Showed_genuine_interest]}</td></tr>";
			echo "<tr><td>Took ownership</td><td>{$row[Took_ownership]}</td></tr>";
			echo "<tr><td>Knowledge to handle request</td><td>{$row[Knowledge_to_handle_request]}</td></tr>";
			echo "<tr><td>Value customer</td><td>{$row[Valued_customer]}</td></tr>";
			echo "<tr><td>Was professional</td><td>{$row[Was_professional]}</td></tr>";
			echo "<tr><td>Easy to understand</td><td>{$row[Easy_to_understand]}</td></tr>";
			echo "<tr><td>Provided accurate info</td><td>{$row[Provided_accurate_info]}</td></tr>";
			echo "<tr><td>Helpful response</td><td>{$row[Helpful_response]}</td></tr>";
			echo "<tr><td>Answered concisely</td><td>{$row[Answered_concisely]}</td></tr>";
			echo "<tr><td>Sent in a timely manner</td><td>{$row[Sent_in_timely_manner]}</td></tr>";
			echo "<tr><td>What would it take to earn higher LTR?</td><td>{$row[What_would_it_take_to_earn_hig]}</td></tr>";
			echo "<tr><td>What would it take to earn 10 LTR?</td><td>{$row[What_would_it_take_to_earn_10_]}</td></tr>";
			echo "<tr><td>Like most about PayPal</td><td>{$row[Like_most_about_PayPal__LTR_]}</td></tr>";
			echo "<tr><td>What could be done differently?</td><td>{$row[What_could_be_done_differently]}</td></tr>";
			echo "<tr><td>What could be done to earn 10 ASAT?</td><td>{$row[What_could_be_done_to_earn_10_]}</td></tr>";
			echo "<tr><td>What teammate did to earn satisfaction?</td><td>{$row[What_teammate_did_to_earn_sati]}</td></tr>";
			echo "<tr><td>How to improve knowledge to handle request?</td><td>{$row[Improve_Knowledge_to_Handle_Re]}</td></tr>";
			echo "<tr><td>How to improve handled professionally?</td><td>{$row[Improve_Handled_Professionally]}</td></tr>";
			echo "<tr><td>How to improve took ownership?</td><td>{$row[Improve_Took_Ownership]}</td></tr>";
			echo "<tr><td>How to improve genuine interest?</td><td>{$row[Improve_Genuine_Interest]}</td></tr>";
			echo "<tr><td>How to improve valued customer?</td><td>{$row[Improve_Valued_Customer]}</td></tr>";
			echo "<tr><td>Why was issue not resolved?</td><td>{$row[Why_issue_not_resolved]}</td></tr>";
			echo "<tr><td>Why are you not sure issue is resolved?</td><td>{$row[Not_sure_issue_is_resolved]}</td></tr>";
			echo "<tr><td>How could we have reduced effort?</td><td>{$row[How_could_have_reduced_custome]}</td></tr>";
			echo "<tr><td>Customer contact count</td><td>{$row[customer_contact_count]}</td></tr>";
			echo "<tr><td>Customer's primary country of residence</td><td>{$row[Customers_Primary_Country_of_]}</td></tr>";
			echo "<tr><td>Talk time</td><td>{$row[Workitem_Phone_talk_time]}</td></tr>";
			echo "<tr><td>KDI email</td><td>{$row[KDI___email]}</td></tr>";
			echo "<tr><td>KDI phone</td><td>{$row[KDI___phone]}</td></tr>";
			echo "<tr><td>Log notes</td><td>{$row[All_log_notes_combined__if_any]}</td></tr>";

			echo "</table>";
			$sql = "SELECT * FROM prt085data WHERE Customer_ID = '{$row[Customer_Account_ID]}'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($rowx=$result->fetch_assoc()){
				echo "-- {$rowx[Employee]}<br />";
			}
		}
		//$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='raw_data' AND table_schema='concentrix'";
		$sql = "SELECT column_name FROM surveycolumns";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "Column filter: <select name=column_name onChange='location.href=\"?a=$a&b=$b&team=$team&c=\" + this.value;'>";
		echo "<option></option>";
		while($row=$result->fetch_assoc()){
			echo "<option";
			if ($c==$row[column_name]){echo" selected";}
			echo ">".$row[column_name]."</option>";
		}
		echo "</select>";
		echo "<table class='sortable'>";
		echo "<thead><tr><th>%</th><th>Surveys</th>";
		echo "<th>$c</th><th>KDI</th><th>NPS</th><th>FCR</th><th>ATT</th></tr></thead>";

		if ($c){
			$column_name = $c;
			$sql = "SELECT distinct $column_name FROM raw_data $sqldater $teamdefinition ORDER by $column_name ASC";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					echo "<br>Column data: <select name=data onChange='location.href=\"?a=$a&b=$b&team=$team&c=$column_name&d=\" + this.value;'>";
					echo "<option></option>";
					while($row=$result->fetch_assoc()){
						echo "<option";
						$tmpe = $row[$column_name];
						echo " value='$tmpe'";
						$dc = str_replace(";and;","&",$d);
						$dc = str_replace(";slash;","/",$dc);
						if ($dc==$row[$column_name]){echo" selected";}
						echo ">".$row[$column_name]."</option>";
					}
					echo "</select><br><br><br>";
					if($d!=''){
						if($teamdefinition){ $surveydefinition="AND "; }
						else {
							if ($sqldater){ $surveydefinition="AND "; }
							else{ $surveydefinition="WHERE "; }
						}
						$d=html_entity_decode($d);
						$td = $d;
						$td = str_replace(";and;","&",$d);
						$td = str_replace(";slash;","/",$td);


						if ($c == 'Contact_Tracking_Reasons') {
							$dkf = explode(" ", $d);
							$dddk = $dkf[count($dkf)-1];
							if ($dddk) {
								$surveydefinition .= "$c LIKE '%" . $dkf[count($dkf)-1] . "'";
							}
							else {
								$surveydefinition.="$c='$td'";
							}
						}

						else {
							$surveydefinition.="$c='$td'";
						}
					}
					else{
						$contract = 5;
						$sql = "SELECT COUNT(*) as id FROM raw_data $sqldater $teamdefinition $surveydefinition";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						$row=$result->fetch_assoc();
						$surveys=$row[id];
						$totalsurveys = $surveys;
						echo "<tfoot>";
						echo "<tr>";
						echo "<td>100%</td>";
						echo "<td>" . $surveys . "</td>";
						echo "<td>TOTAL</td>";
						$kdi=sv(5,$surveydefinition,$surveys);
						$fcr=sv(16,$surveydefinition,$surveys);
						$nps=sv(4,$surveydefinition,$surveys);
						$att=sv(15,$surveydefinition,$surveys);
						list($bg,$fg)=targetcolor($kdi, $contract, 5, $team, $startdate);
						echo "<td style='color:#$fg;background-color:#$bg'>";
						echo $kdi;
						echo "</td>\n";
						list($bg,$fg)=targetcolor($nps, $contract, 4, $team, $startdate);
						echo "<td style='color:#$fg;background-color:#$bg'>";
						echo $nps;
						echo "</td>\n";
						list($bg,$fg)=targetcolor($fcr, $contract, 16, $team, $startdate);
						echo "<td style='color:#$fg;background-color:#$bg'>";
						echo $fcr;
						echo "</td>\n";
						echo "<td>";
						echo $att;
						echo "</td>\n";
						echo "</tr>\n\n";
						echo "</tr>";
						echo "</tfoot>";
						echo "<tbody>";
						$sql = "SELECT distinct $column_name FROM raw_data $sqldater $teamdefinition ORDER by $column_name ASC LIMIT 100";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						$contract = 5;
						while($row=$result->fetch_assoc()){
							echo "<tr>";
							if($teamdefinition){
								$surveydefinitions="AND ";
							}
							else {
								if ($sqldater){
									$surveydefinitions="AND ";
								}
								else{
									$surveydefinitions="WHERE ";
								}
							}
							$tmpd = $row[$c];

							// BUG: causing the bug.
							if ($c == 'Contact_Tracking_Reason') {
								$dkf = explode(" ", $row[$c]);
								$dddk = $dkf[count($dkf)-1];
								if ($dddk) {
									$surveydefinitions .= "$c LIKE '%" . $dkf[count($dkf)-1] . "'";
								}
								else {
									$surveydefinitions.="$c='{$row[$c]}'";
								}
							}
							else {
								//$tmpd = str_replace("&",";and;",$row[$c]);
								//$tmpd = str_replace("/",";slash;",$tmpd);
								$surveydefinitions.="$c='{$row[$c]}'";
							}
							$sqla = "SELECT COUNT(*) as id FROM raw_data $sqldater $teamdefinition $surveydefinitions LIMIT 50";
							if(!$resulta=$db->query($sqla)){cl($sqla);cl($db->error);}
							$rowa=$resulta->fetch_assoc();
							$surveysa=$rowa[id];
							echo "<td>".round(($surveysa/$totalsurveys)*100,2) . "%</td>";
							echo "<td>$surveysa</td>";
							echo "<td><a href='?&a=$a&b=$b&c=$c&d=";
							echo $tmpd;
							echo "&team=$team'>";
							if ($c == 'Teammate_NT_ID') {
								echo getUserBadge($row[$c]);
							}
							else {
								echo $row[$c];
							}
							echo "</a></td>\n";
							$kdi=sv(5,$surveydefinitions,$surveysa);
							$fcr=sv(16,$surveydefinitions,$surveysa);
							$nps=sv(4,$surveydefinitions,$surveysa);
							$att=sv(15,$surveydefinitions,$surveysa);
							list($bg,$fg)=targetcolor($kdi, $contract, 5, $team, $startdate);
							echo "<td style='color:#$fg;background-color:#$bg'>";
							echo $kdi;
							echo "</td>\n";
							list($bg,$fg)=targetcolor($nps, $contract, 4, $team, $startdate);
							echo "<td style='color:#$fg;background-color:#$bg'>";
							echo $nps;
							echo "</td>\n";
							list($bg,$fg)=targetcolor($fcr, $contract, 16, $team, $startdate);
							echo "<td style='color:#$fg;background-color:#$bg'>";
							echo $fcr;
							echo "</td>\n";
							echo "<td>";
							echo $att;
							echo "</td>\n";
							echo "</tr>\n\n";
						}
					}
				}
				echo "</tbody>";
				echo "</table>";


				// Column data has been specified.
				if($d!=''){
					if (true){
						echo "<table width=100%>";
						echo "<thead>";
						echo "<tr><th>Metric</th>";
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
						}
						echo "</tr>";
						echo "</thead>";
						echo "<tr><td>Surveys</td>";
						$showmonth = $startmonth;
						$showyear = $startyear;
						for ($x = 1; $x < 13; $x++){
							$ldm = cal_days_in_month(CAL_GREGORIAN, $showmonth, $showyear);
							$sde = unixdate_to_exceldate(mktime(0, 0, 0, $showmonth, 1, $showyear));
							$ede = unixdate_to_exceldate(mktime(23, 59, 59, $showmonth, $ldm,$showyear));
							$tmptimedef = " WHERE teammate_contact_date > '{$sde}' AND teammate_contact_date < '{$ede}' ";
							$qsql = "SELECT COUNT(*) as id FROM raw_data $tmptimedef $teamdefinition AND $c = '$d'";
							if(!$qresult = $db->query($qsql)){
								cl($qsql);
								cl($db->error);
							}
							$qrow = $qresult->fetch_assoc();
							$surveys = $qrow[id];
							$surveysm[$showyear][$showmonth] = $qrow[id];
							echo "<td>$surveys</td>";
							$showmonth++;
							if($showmonth > 12) {
								$showmonth = 1;
								$showyear++;
							}
						}
						echo "</tr>";
						$teamname = getteamname($team);
						echo "<tr><td>AHT ($teamname)</td>";
						$showmonth = $startmonth;
						$showyear = $startyear;
						$rounding = gmr(2);
						for ($x=1;$x<13;$x++){
							echo "<td";
							$monti = $monthy[$showmonth];
							$dator = $monti.'-'.$showyear;
							$mm = str_pad($showmonth,2,"0",STR_PAD_LEFT);
							$qvalue = vm(2, $team, "$showyear-$mm-01");
							list($bg,$fg) = targetcolor($qvalue, 5, 2, $team, "$showyear-$mm-01");
							echo " style='background-color:#$bg;color:#$fg'>";
							echo round($qvalue,$rounding);
							echo "</td>";
							$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
						}
						echo "</tr>";
						if ($c == 'Teammate_NT_ID') {
							echo "<tr><td>AHT ($d)</td>";
							$showmonth = $startmonth;
							$showyear = $startyear;
							$rounding = gmr(2);
							$ahtdef = " AND ntid = '$d'";
							for ($x=1;$x<13;$x++){
								echo "<td";
								$monti = $monthy[$showmonth];
								$dator = $monti.'-'.$showyear;
								$mm = str_pad($showmonth,2,"0",STR_PAD_LEFT);
								$qvalue = av("aht","$showyear-$mm-01",$ahtdef);
								list($bg,$fg) = targetcolor($qvalue, 5, 2, $team, "$showyear-$mm-01");
								echo " style='background-color:#$bg;color:#$fg'>";
								echo round($qvalue,$rounding);
								echo "</td>";
								$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
							}
							echo "</tr>";

							echo "<tr><td>RCR</td>";
							$showmonth = $startmonth;
							$showyear = $startyear;
							$rounding = gmr(17);
							$ahtdef = " AND ntid = '$d'";
							for ($x=1;$x<13;$x++){
								echo "<td";
								$monti = $monthy[$showmonth];
								$dator = $monti.'-'.$showyear;
								$mm = str_pad($showmonth,2,"0",STR_PAD_LEFT);
								$qvalue = av("rcr","$showyear-$mm-01",$ahtdef)*100;
								list($bg,$fg) = targetcolor($qvalue, 5, 17, $team, "$showyear-$mm-01");
								echo " style='background-color:#$bg;color:#$fg'>";
								echo round($qvalue,$rounding);
								echo gms(17);
								echo "</td>";
								$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
							}
							echo "</tr>";

							echo "<tr><td>TR</td>";
							$showmonth = $startmonth;
							$showyear = $startyear;
							$rounding = gmr(6);
							$ahtdef = " AND ntid = '$d'";
							for ($x=1;$x<13;$x++){
								echo "<td";
								$monti = $monthy[$showmonth];
								$dator = $monti.'-'.$showyear;
								$mm = str_pad($showmonth,2,"0",STR_PAD_LEFT);
								$qvalue = av("tr","$showyear-$mm-01",$ahtdef)*100;
								list($bg,$fg) = targetcolor($qvalue, 5, 6, $team, "$showyear-$mm-01");
								echo " style='background-color:#$bg;color:#$fg'>";
								echo round($qvalue,$rounding);
								echo gms(6);
								echo "</td>";
								$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
							}
							echo "</tr>";
						}
						$qsql = "SELECT metric_id,metric_name FROM metrics WHERE metric_quality = '1' ORDER by metric_name ASC";
						if(!$qresult=$db->query($qsql)){cl($qsql);cl($db->error);}
						$contract = 5;$x=0;
						while($qrow=$qresult->fetch_assoc()){
							echo "<tr><td>$qrow[metric_name]</td>";
							$showmonth = $startmonth;
							$showyear = $startyear;
							for ($x=1;$x<13;$x++){
								$ldm=cal_days_in_month(CAL_GREGORIAN,$showmonth,$showyear);
								echo "<td";
								$sde = unixdate_to_exceldate(mktime(0,0,0,$showmonth,1,$showyear));
								$ede = unixdate_to_exceldate(mktime(23,59,59,$showmonth,$ldm,$showyear));
								$tmptimedef = " WHERE teammate_contact_date > '$sde' AND teammate_contact_date < '$ede' ";
								$qvalue = sv($qrow[metric_id], "AND $c = '$d'", $surveysm[$showyear][$showmonth],$tmptimedef);
								list($bg,$fg) = targetcolor($qvalue, 5, $qrow[metric_id], $team, "$showyear-$showmonth-01");
								echo " style='background-color:#$bg;color:#$fg'>";
								echo round($qvalue,gmr($qrow[metric_id]));
								echo gms($qrow[metric_id]);
								echo "</td>";
								$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
							}
							echo "</tr>";
						}
					}
					echo "</table>";
					echo "<table class='sortable tabler' width=95%><thead><tr><th>Num</th><th>Survey ID</th><th>Queue</th>
					<th>Teammate</th>
					<th>LTR</th>
					<th>Issue Resolved?</th>
					<th>Customer Contact Count</th>
					<th>KDI</th>
					</tr></thead>";
					$sql = "SELECT likely_to_recommend_paypal,issue_resolved,customer_contact_count,customer_account_id,kdi___email,kdi___phone,external_survey_id,$c,queue_source_name,teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition $surveydefinition LIMIT 100";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					$counter=0;
					while($row=$result->fetch_assoc()){
						$counter++;
						echo "<tr>";
						echo "<td>$counter</td>";
						echo "<td><a href='?a=$a&b=$b&c=$c&d=$d&e=$row[external_survey_id]'>$row[external_survey_id]</a>";
						echo "</td>";
						echo "<td><a href='?a=$a&b=$b&c=Queue_Source_Name&d=$row[queue_source_name]'>$row[queue_source_name]</a>";
						echo "</td>";
						echo "<td><a href='?a=$a&b=$b&c=Teammate_Name&d=$row[teammate_name]'>$row[teammate_name]</a>";
						echo "</td>";
						echo "<td";
						if ($row[likely_to_recommend_paypal]>8){echo " style='background-color:#$great_color;color:#8f8;'";}
						elseif ($row[likely_to_recommend_paypal]<5){echo " style='background-color:#$bad_color;color:#8f8;'";}
						else{echo " style='background:#fdae61;'";}
						echo ">$row[likely_to_recommend_paypal]</td>";
						echo "<td";
						if ($row[issue_resolved]=="Yes"){echo " style='background-color:#$great_color;color:#8f8;'";}
						else{echo " style='background-color:#$bad_color;color:#f88;'";}
						echo "></td>";
						echo "<td";
						if ($row[customer_contact_count]<2){echo " style='background-color:#$great_color;color:#8f8;'";}
						else{echo " style='background-color:#$bad_color;color:#f88;'";}
						echo ">$row[customer_contact_count]</td>";
						echo "<td";
						if ($row[kdi___email]>=75){echo " style='background-color:#$great_color;color:#8f8;'";}
						else if ($row[kdi___phone]>=75){echo " style='background-color:#$great_color;color:#8f8;'";}
						else{echo " style='background-color:#$bad_color;color:#f88;'";}
						echo ">$row[kdi___email]$row[kdi___phone]</td>";
						echo "</tr>\n";
					}
					echo "</table>\n";
				}
}
