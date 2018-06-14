<?php
function show_module() {
	global $db, $_GET, $app_action, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate, $startdate_month, $startdate_year, $enddate_month, $enddate_year, $enddate_day, $startdate_day, $month;
	if ($team>0){
		echo "<div class='unselectable'>";

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
		if(1) {
			echo "<div class='pad'>";
			echo "<div class='before'></div>";
			echo "<div class=title><b>Month:</b> <i>" . $month[$view_startdate_month]." $view_startdate_year</i></div>";

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
			<th width=10>Kicker: NPS</th>
			<th width=10 id='ppth'>Stat Points</th>";
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

				// Bonus weighting
				$sql = "SELECT newweight,metric_id,newkicker FROM bonus_weighting WHERE newweight > 0";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){
					$bonus_weight[$row[metric_id]]=$row[newweight];
				}

				$teamahtdef = getTeamAhtDefinitions($team);
				for($x=1;$x<=$teammate_counter;$x++){
					//if (guessteam($teammate[$x])==$team){
					if (1) {

						$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}'";
						$ahtdef = $teamahtdef . " AND ntid = '{$teammate[$x]}'";
						$bd[contacts][$teammate[$x]] = av("answered",$startdate,$ahtdef) + av("worked",$startdate,$ahtdef);
						$bd[ratio][$teammate[$x]] = av("worked",$startdate,$ahtdef) / av("answered",$startdate,$ahtdef);
						$bd[aht][$teammate[$x]] = av("aht",$startdate,$ahtdef);
						$bd[kdi][$teammate[$x]] = sv(5,$surveydefinition,$bd[surveys][$teammate[$x]],'');
						$bd[tr][$teammate[$x]] = av("tr",$startdate,$ahtdef);
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
						$bd[performancepoints][$teammate[$x]] = ($weight_aht * $bonus_weight[2]) + ($weight_kdi * $bonus_weight[5]) + ($weight_tr * $bonus_weight[6]);
					}
				}
				arsort($bd[performancepoints]);
				$counti = 0;
				foreach($bd[performancepoints] as $tm => $points){
					//if (guessteam($tm)!=$team){
					//}
					//else {
					if (1) {
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
			echo "<td>".gettarget(5,4,$team,$view_startdate,'high')."</td>"; // nps
			echo "<td></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td colspan=6><b>GOOD</b></td>";
			echo "<td>".getgoodtarget(5,2,$team,$view_startdate,'low')."</td>"; // aht
			echo "<td>".getgoodtarget(5,5,$team,$view_startdate,'high')."</td>"; // kdi
			echo "<td>".getgoodtarget(5,6,$team,$view_startdate,'low')."</td>"; // tr
			echo "<td colspan=6></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td colspan=6><b>GREAT</b></td>";
			echo "<td>".getgreattarget(5,2,$team,$view_startdate,'low')."</td>"; // aht
			echo "<td>".getgreattarget(5,5,$team,$view_startdate,'high')."</td>"; // kdi
			echo "<td>".getgreattarget(5,6,$team,$view_startdate,'low')."</td>"; // tr
			echo "<td colspan=6></td>";
			echo "</tr>";
			echo "</tfoot>";
			echo "</table>";
			echo "</div>";
		}
	}
	else{echo "This view only works with a team selected.";}
}
