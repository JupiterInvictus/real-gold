<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
		echo "Upload a PRT058p report for daily AHT.";
		echo "<form name=uploadformprt method=post enctype='multipart/form-data'>
		<input type=hidden name=a value=uploadprt>
		<input type=file name='filedataprt' id='filedataprt'>
		<input type=submit></form>";

		echo "<h1>Week</h1>";
		echo "<a href='?a=certification&e=useprevious'>Use previous week</a>";
		if ($_GET[b]=='addntid'){
			echo "Adding <i>{$_GET[d]}</i> to the list.";
			$sql = "INSERT INTO certificationdata (ntid,date) VALUES('{$_GET[d]}','$today')";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
		if ($_GET[b]=='removeid'){
			if ($_GET[d]=='remove') {
				echo "Removing <i>{$_GET[c]}</i> from the list.";
				$sql = "DELETE FROM certificationdata WHERE id = {$_GET[c]}";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
		}

	echo "<table id='qdtable' class=tabler width=100%>";
	if ($_GET[e]=='useprevious'){
		$today = strtotime("$today -1week");
		$today = date("Y-m-d",$today);
		$e='useprevious';
	}


	$dayofweek = date('w', strtotime($today));
	$dayofweek++;
	$firstdayofweek = 1;
	$lastdayofweek = 5;
	$diff = $dayofweek - $firstdayofweek;

	echo "<thead><th rowspan=3>Name</th>";
	echo "<tr><th colspan=10>PHONE</th><th colspan=10>EMAIL</th></tr>";
	echo "<tr>";

	for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
		$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
		$date_of_day = date('Y-m-d', $dator);
		echo "<th colspan=2>";
		echo date("l",$dator);
		echo "<br>$date_of_day</th>";
	}
	for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
		$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
		$date_of_day = date('Y-m-d', $dator);
		echo "<th colspan=2>";
		echo date("l",$dator);
		echo "<br>$date_of_day</th>";
	}
	echo "</tr><tr><th></th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "<th>#</th><th>AHT</th>";
	echo "</thead>";
	echo "<tr><td>PRT058 uploaded?</td>";
	for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
		$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
		$date_of_day = date('Y-m-d', $dator);
		$exceldator = round(unixdate_to_exceldate($dator));
		$sql = "SELECT id FROM prt058data WHERE date = '$exceldator' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		echo "<td colspan=2>";
		if ($row[id]) {echo "<b>yes</b>";}
		else {echo "<i>no</i>";}
		echo "</td>";
	}
	echo "</tr>";

	$sql = "SELECT DISTINCT teammate_name, teammate_nt_id FROM raw_data ORDER by response_date DESC LIMIT 20";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$tmlist = '<option>--- add ---</option>';
	while($row=$result->fetch_assoc()){
		$tmlist .= "<option value='" . $row[teammate_nt_id] . "'>{$row[teammate_name]}</option>";
	}
	$sql = "SELECT id,ntid FROM certificationdata WHERE ntid <> '' LIMIT 40";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		while($row=$result->fetch_assoc()){
			echo "<tr><td><select onChange='location.href=\"?e=$e&a=certification&b=removeid&c={$row[id]}&d=\" + this.value;' id='cert_ntid_select_{$row[ntid]}'>";
			echo "<option>$row[ntid]</option>";
			echo "<option value=remove>--- remove ---</option>";
			echo "</select></td>";

			for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
				$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
				$date_of_day = date('Y-m-d', $dator);
				$exceldator = round(unixdate_to_exceldate($dator));
			$sql = "SELECT phone_answered,phone_aht_secs FROM prt058data WHERE ntid = '{$row[ntid]}' AND date = '$exceldator' and queue_name='Total' LIMIT 1";
			if(!$results=$db->query($sql)){cl($sql);cl($db->error);}
			$rows=$results->fetch_assoc();
			// Phone
			echo "<td style='border-bottom: 1px solid #666;'>";
			if ($rows[phone_answered]>0){echo round($rows[phone_answered],0);}
			else {echo "";}
			echo "</td>";
			echo "<td style='border-right: 3px solid #666;border-bottom: 1px solid #666;'>";
			if ($rows[phone_aht_secs]>0){echo round($rows[phone_aht_secs],0);}
			else {echo "";}
			echo "</td>";

		}
		for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
			$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
			$date_of_day = date('Y-m-d', $dator);
			$exceldator = round(unixdate_to_exceldate($dator));
			$sql = "SELECT email_worked,email_aht_secs FROM prt058data WHERE ntid = '{$row[ntid]}' AND date = '$exceldator' and queue_name='Total' LIMIT 1";
			if(!$results=$db->query($sql)){cl($sql);cl($db->error);}
			$rows=$results->fetch_assoc();
			// Phone
			echo "<td style='border-bottom: 1px solid #666;'>";
			if ($rows[email_worked]>0){echo round($rows[email_worked],0);}
			else {echo "";}
			echo "</td>";
			echo "<td style='border-right: 3px solid #666;border-bottom: 1px solid #666;'>";
			if ($rows[email_aht_secs]>0){echo round($rows[email_aht_secs],0);}
			else {echo "";}
			echo "</td>";

		}
		echo "</tr>";
	}
	echo "<tr><td><select onChange='location.href=\"?e=$e&a=certification&b=addntid&c={$row[id]}&d=\" + this.value;' id='cert_ntid_select_new'>$tmlist</select></td></tr>";


		echo "</table>";
	}
		// s3ttings
