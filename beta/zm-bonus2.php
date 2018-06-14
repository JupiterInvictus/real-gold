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
			<th>ACW</th>
			<th class=rotated width=10>Phone AHT</th>
			<th class=rotated>Email Worked</th>
			<th class=rotated width=10>Email Surveys</th>
			<th class=rotated width=10>Email AHT</th>
			<th class=rotated width=10>Phone KDI</th>
			<th class=rotated width=10>RCR</th>
			<th class=rotated width=10>Phone TR</th>
			<th class=rotated width=10>Email KDI</th>
			<th class=rotated width=10>Email TR</th>";
			echo "</tr></thead>";
			echo "<tbody>";
			$sql = "SELECT distinct teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition ORDER by teammate_name ASC";
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
					$bd[ptr][$teammate[$x]] = round(av("tr",$startdate,$ahtdef) * 100, 1);
					$bd[acw][$teammate[$x]] = round(av("acw",$startdate,$ahtdef), 0);
					$bd[rcr][$teammate[$x]] = round(av("rcr",$startdate,$ahtdef), 3) * 100;
					$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%EMAIL'";
					$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Email'";
					$bd[emailkdi][$teammate[$x]] = sv(5,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
					$bd[emailcrrr][$teammate[$x]] = sv(3,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
					$bd[emailnps][$teammate[$x]] = sv(4,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
					$bd[emailworked][$teammate[$x]] = av("worked",$startdate,$ahtdef);
					$bd[emailaht][$teammate[$x]] = av("eaht",$startdate,$ahtdef);
					$bd[etr][$teammate[$x]] = round(av("tr",$startdate,$ahtdef) * 100,1);
					$combinedsurveydefinition = " AND teammate_nt_id = '{$teammate[$x]}'";
					$bd[combinedsurveys][$teammate[$x]] = $bd[phonesurveys][$teammate[$x]] + 	$bd[emailsurveys][$teammate[$x]];
				}
			}
			//arsort($bd[performancepoints]);
			$counti = 0;
			foreach($bd[emailworked] as $tm => $points){
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
					echo "<td>{$bd[phoneanswered][$tm]}</td>";
					echo "<td>{$bd[phonesurveys][$tm]}</td>";
					echo "<td>{$bd[acw][$tm]}</td>";
					echo "<td>".round($bd[phoneaht][$tm],0)."</td>";
					echo "<td>{$bd[emailworked][$tm]}</td>";
					echo "<td>{$bd[emailsurveys][$tm]}</td>";
					echo "<td>".round($bd[emailaht][$tm],0)."</td>";
					echo "<td>{$bd[phonekdi][$tm]}</td>";
					echo "<td>{$bd[rcr][$tm]}</td>";
					echo "<td>{$bd[ptr][$tm]}</td>";
					echo "<td>{$bd[emailkdi][$tm]}</td>";
					echo "<td>{$bd[etr][$tm]}</td>";
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
