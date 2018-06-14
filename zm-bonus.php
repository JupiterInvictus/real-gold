<?php
function show_module() {
	global $db, $_GET, $app_action, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate, $startdate_month, $startdate_year, $enddate_month, $enddate_year, $enddate_day, $startdate_day, $month;
	if ($team>0){
		echo "<div class='unselectable'>";
		if (ismgr()){
			echo "<a href='?a=$app_action&startdate=$startdate&enddate=$enddate&team=$team'>View</a> / ";
			echo "<a href='?a=$app_action&b=e&startdate=$startdate&enddate=$enddate&team=$team'>Edit</a> / ";
		}
		echo " <a href='?a=statranker'>Statranker</a></div>";
		$view_startdate_month=$startdate_month;
		$view_startdate_year=$startdate_year;
		$view_enddate_year=$enddate_year;
		$view_enddate_month=$enddate_month;
		$view_startdate_day="01";
		$view_enddate_day = cal_days_in_month(CAL_GREGORIAN,$view_startdate_month,$view_enddate_year);
		$view_startdate = "$view_startdate_year-$view_startdate_month-01";
		$view_enddate = "$view_startdate_year-$view_startdate_month-$view_enddate_day";
		if ($enddate_day != $view_enddate_day){
			echo "<script>location.href='?a=$app_action&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
		}
		if ($view_startdate_month != $view_enddate_month){
			echo "<script>location.href='?a=$app_action&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
		}
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
			$showphoto = showphotos();
			echo "<div class='pad'>";
			echo "<div class='before'></div>";
			echo "<div class=title><i class='em em-moneybag'></i> Month: " . $month[$view_startdate_month]." $view_startdate_year</div>";
			echo "<table class='sortable'>";
			echo "<thead><tr>";
			echo "<th width=10>Position</th>";
			echo "
			<th>Badge</th>
			<th>Sur name</th>
			<th width=10>Contacts</th>
			<th width=10>Ratio</th>
			<th width=10>Surveys</th>
			<th width=10>AHT</th>
			<th width=10>KDI</th>
			<th width=10>TR</th>
			<th width=10>RCR</th>
			<th width=10>Kicker: NPS</th>
			<th width=10 id='ppth'>Bonus Points</th>
			<th width=10>Hours Worked</th>
			<th width=10>Bonus before sick</th>";
			if (ismgr()){
				echo "<th width=10>Sick Instances</th>
				<th width=10>Bonus Paid Out</th>";
			}
			echo "</tr></thead>";

			echo "<tbody>";

			$sql = "SELECT distinct teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition ORDER by teammate_nt_id ASC";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$contract = 5;
			$teammate_counter = 0;

			$tgt_aht = gettarget(5,2,$team,$view_startdate,'low');
			$tgt_goodaht = getgoodtarget(5,2,$team,$view_startdate,'low');
			$tgt_greataht = getgreattarget(5,2,$team,$view_startdate,'low');

			$tgt_kdi = gettarget(5,5,$team,$view_startdate,'high');
			$tgt_goodkdi = getgoodtarget(5,5,$team,$view_startdate,'high');
			$tgt_greatkdi = getgreattarget(5,5,$team,$view_startdate,'high');

			$tgt_tr = gettarget(5,6,$team,$view_startdate,'low');
			$tgt_goodtr = getgoodtarget(5,6,$team,$view_startdate,'low');
			$tgt_greattr = getgreattarget(5,6,$team,$view_startdate,'low');

			$tgt_rcr = gettarget(5,17,$team,$view_startdate,'low');
			$tgt_goodrcr = getgoodtarget(5,17,$team,$view_startdate,'low');
			$tgt_greatrcr = getgreattarget(5,17,$team,$view_startdate,'low');

			$npstarget = gettarget(5,4,$team,$view_startdate,'high');

			$fair_weight = 0.9;
			$good_weight = 1.1;
			$great_weight = 1.2;



			while($row=$result->fetch_assoc()){
				$teammate_counter++;
				$teammate[$teammate_counter]=$row[teammate_nt_id];
				$teammatename[$row[teammate_nt_id]]=$row[teammate_name];
				$sqlb = "SELECT DISTINCT external_survey_id FROM raw_data $sqldater $teamdefinition AND teammate_nt_id = '{$row[teammate_nt_id]}'";
				if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
				$survey_counter=0;
				while($rowb=$resultb->fetch_assoc()){$survey_counter++;}
					$bd[surveys][$row[teammate_nt_id]]=$survey_counter;
				}
				$im=ismgr();

				// Bonus weighting
				$sql = "SELECT newweight,metric_id,newkicker FROM bonus_weighting WHERE newweight > 0";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){
					$bonus_weight[$row[metric_id]]=$row[newweight];
				}

				$teamahtdef = getTeamAhtDefinitions($team);
				for($x=1;$x<=$teammate_counter;$x++){
					if (guessteam($teammate[$x])==$team){

						$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}'";
						$ahtdef = $teamahtdef . " AND ntid = '{$teammate[$x]}'";
						$bd[contacts][$teammate[$x]] = av("answered",$startdate,$ahtdef) + av("worked",$startdate,$ahtdef);
						$bd[ratio][$teammate[$x]] = av("worked",$startdate,$ahtdef) / av("answered",$startdate,$ahtdef);
						$bd[aht][$teammate[$x]] = av("aht",$startdate,$ahtdef);
						$bd[kdi][$teammate[$x]] = sv(5,$surveydefinition,$bd[surveys][$teammate[$x]],'');
						$bd[tr][$teammate[$x]] = round(av("tr",$startdate,$ahtdef),3);
						$bd[rcr][$teammate[$x]] = round(av("rcr",$startdate,$ahtdef), 3);
						$bd[nps][$teammate[$x]] = sv(4,$surveydefinition,$bd[surveys][$teammate[$x]],'');
						$bd[hours][$teammate[$x]] = gethours($teammate[$x],$startdate);
						$bd[instances][$teammate[$x]] = getinstances($teammate[$x],$startdate);

						$weight_aht = 0;
						if ($bd[contacts][$teammate[$x]]> 0 ) {
							if ($bd[aht][$teammate[$x]] <= $tgt_greataht){ $weight_aht = $great_weight; }
							else if ($bd[aht][$teammate[$x]] <= $tgt_goodaht){ $weight_aht = $good_weight; }
							else if ($bd[aht][$teammate[$x]] <= $tgt_aht){ $weight_aht = $fair_weight; }
						}
						$weight_kdi = 0;
						if ($bd[surveys][$teammate[$x]]>0) {
							if ($bd[kdi][$teammate[$x]] >= $tgt_greatkdi) { $weight_kdi = $great_weight; }
							else if ($bd[kdi][$teammate[$x]] >= $tgt_goodkdi) { $weight_kdi = $good_weight; }
							else if ($bd[kdi][$teammate[$x]] >= $tgt_kdi) { $weight_kdi = $fair_weight; }
						}

						$weight_tr = 0;
						if ($bd[contacts][$teammate[$x]]> 0 ) {
							if ($bd[tr][$teammate[$x]]*100 <= $tgt_greattr) { $weight_tr = $great_weight; }
							else if ($bd[tr][$teammate[$x]]*100 <= $tgt_goodtr) { $weight_tr = $good_weight; }
							else if ($bd[tr][$teammate[$x]]*100 <= $tgt_tr) { $weight_tr = $fair_weight; }
						}
						$weight_rcr = 0;
						if ($bd[contacts][$teammate[$x]]> 0 ) {
							if ($bd[rcr][$teammate[$x]]*100 <= $tgt_greatrcr) { $weight_rcr = $great_weight; }
							else if ($bd[rcr][$teammate[$x]]*100 <= $tgt_goodrcr) { $weight_rcr = $good_weight; }
							else if ($bd[rcr][$teammate[$x]]*100 <= $tgt_rcr) { $weight_rcr = $fair_weight; }
						}
						$bd[performancepoints][$teammate[$x]] = ($weight_aht * $bonus_weight[2]) + ($weight_kdi * $bonus_weight[5]) + ($weight_tr * $bonus_weight[6]) + ($weight_rcr * $bonus_weight[17]);
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
						echo "<td class=tmname>";
						echo getUserBadge($tm);
						echo "<td>";
						echo $surname;
						echo "</td>";

						// contacts
						echo "<td class=xtoggle>{$bd[contacts][$tm]}</td>";
						echo "<td class=xtoggle>" . round($bd[ratio][$tm]*100,0) . "%</td>";

						// Surveys
						echo "<td class=xtoggle>{$bd[surveys][$tm]}</td>";

						// AHT
						list($bg,$fg)=targetcolor($bd[aht][$tm], $contract, 2, $team, $view_startdate);
						echo "<td class='mainmetric' style='color:#$fg;background-color:#$bg'>";
						echo round($bd[aht][$tm],0);
						echo "</td>";

						// KDI
						list($bg,$fg)=targetcolor($bd[kdi][$tm], $contract, 5, $team, $view_startdate);
						echo "<td class='mainmetric' style='color:#$fg;background-color:#$bg'>";
						echo round($bd[kdi][$tm],1);
						echo "</td>";

						// TR
						list($bg,$fg)=targetcolor($bd[tr][$tm]*100, $contract, 6, $team, $view_startdate);
						echo "<td class='mainmetric' style='color:#$fg;background-color:#$bg'>";
						if (is_numeric($bd[tr][$tm])) {
							echo round($bd[tr][$tm]*100,1);
							echo "%";
						}
						else {
							echo $bd[tr][$tm];
						}
						echo "</td>";

						// RCR
						list($bg,$fg)=targetcolor($bd[rcr][$tm]*100, $contract, 17, $team, $view_startdate);
						echo "<td class='mainmetric' style='color:#$fg;background-color:#$bg'>";
						echo round($bd[rcr][$tm]*100,1);
						echo "%</td>";


						// NPS
						$gob = 'bad';
						if ($bd[nps][$tm] >= $npstarget) {
							$gob = 'good';
						}
						echo "<td class='bonuskicker kicker$gob'>";
						echo round($bd[nps][$tm],1);
						echo "</td>";

						// Performance Points
						list($bg,$fg)=targetcolor($bd[performancepoints][$tm], $contract, 11,5);
						echo "<td class=pptd style='color:#$fg;background-color:#$bg'>";
						echo round($bd[performancepoints][$tm]*100,2);
						echo "%</td>\n";

						// Hours Worked
						echo "<td>";
						if ($im && $b=='e'){
							echo "<input class='editor' size=3 name='hw$counti' value='{$bd[hours][$tm]}'>";
							echo "<input type=hidden name='tm$counti' value='$tm'>";
						}
						else {
							echo $bd[hours][$tm];
						}
						echo "</td>";

						// Bonus before sick
						$bonusbeforesick[$tm] = $bd[hours][$tm] * $bd[performancepoints][$tm] * $bd[performancepoints][$tm]*$bd[performancepoints][$tm] * 0.8;
						echo "<td";
						if (($bd[nps][$tm]>=$npstarget) && ($bonusbeforesick[$tm]>0)){
							$bonusbeforesick[$tm]*=1.2;
							echo " style='color:#06f;'>£";
							echo round($bonusbeforesick[$tm],2);
						}
						elseif ($bonusbeforesick[$tm]>0) {
							echo " style='color:#0f0'>£";
							echo round($bonusbeforesick[$tm],2);
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
							$bonuspaidout[$tm] = round($bonusbeforesick[$tm]*$multo,2);
							$totalaftersick += $bonuspaidout[$tm];
							$totalbeforesick += $bonusbeforesick[$tm];
						}

						// Bonus paid out
						if (ismgr()){
							echo "<td";
							if ($bonuspaidout[$tm]>0){
								echo " style='color:#0f0;'>£{$bonuspaidout[$tm]}";
							}
							else {
								echo ">--";
							}
							echo "</td>";
						}
						echo "</tr>\n";
					}
				}
			echo "</tbody>";
			echo "<tfoot>";
			echo "<tr>";
			echo "<td colspan=4><b>FAIR</b></td>";
			echo "<td>41%</td>";
			echo "<td></td>";
			echo "<td>".gettarget(5,2,$team,$view_startdate,'low')."</td>"; // aht
			echo "<td>".gettarget(5,5,$team,$view_startdate,'high')."</td>"; // kdi
			echo "<td>".gettarget(5,6,$team,$view_startdate,'low')."</td>"; // tr
			echo "<td>".gettarget(5,17,$team,$view_startdate,'low')."</td>"; // rcr
			echo "<td>".gettarget(5,4,$team,$view_startdate,'high')."</td>"; // nps
			echo "<td></td>";
			echo "<td></td>";
			echo "<td>". round($totalbeforesick,2) . "</td>";
			echo "<td></td>";
			echo "<td>". round($totalaftersick, 2) . "</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td colspan=6><b>GOOD</b></td>";
			echo "<td>".getgoodtarget(5,2,$team,$view_startdate,'low')."</td>"; // aht
			echo "<td>".getgoodtarget(5,5,$team,$view_startdate,'high')."</td>"; // kdi
			echo "<td>".getgoodtarget(5,6,$team,$view_startdate,'low')."</td>"; // tr
			echo "<td>".getgoodtarget(5,17,$team,$view_startdate,'low')."</td>"; // rcr
			echo "<td colspan=6></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td colspan=6><b>GREAT</b></td>";
			echo "<td>".getgreattarget(5,2,$team,$view_startdate,'low')."</td>"; // aht
			echo "<td>".getgreattarget(5,5,$team,$view_startdate,'high')."</td>"; // kdi
			echo "<td>".getgreattarget(5,6,$team,$view_startdate,'low')."</td>"; // tr
			echo "<td>".getgreattarget(5,17,$team,$view_startdate,'low')."</td>"; // rcr
			echo "<td colspan=6></td>";
			echo "</tr>";
			echo "</tfoot>";
			echo "</table>";
			echo "</div>";
			if(ismgr()){
				if ($b=='e'){
					echo "<input type=submit><input type=hidden name=c value='saved'><input type=hidden name=a value='$app_action'><input type=hidden name=b value=$b>
					<input type=hidden name=tms value=$counti><input type=hidden name=month value='$startdate_year-$startdate_month'></form>";
				}
			}
		}
	}
	else{echo "This view only works with a team selected.";}
}
