<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
	$a = $_GET['a'];

	if(isadmin()){
		echo "<a href='?a=targets&b=add&f=$f'>Add target</a>.<br>";
		echo "<a href='?a=targets&b=autoadjusttargets'>Automatically adjust targets</a>.<br>";
		echo "<a href='?a=targets&b=addgroup'>Add a target group</a>.<br><br>";
		if ($b=='d'){
			if ($c!=''){
				$sql = "UPDATE targets SET active = 0 WHERE target_id = '$c' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
		}
		if ($b=='e'){
			if ($c!=''){
				if ($_GET[tevaluelow]!=''){
					$tevaluelow=$_GET[tevaluelow];
					$tevaluehigh=$_GET[tevaluehigh];
					$tecolor=$_GET[tecolor];
					$tefcolor=$_GET[tefcolor];
					$testartdate=$_GET[testartdate];
					$testopdate=$_GET[testopdate];
					$temetric=$_GET[temetric];
					$tesubmetric=$_GET[tesubmetric];
					$tecontract=$_GET[tecontract];
					$teteamid=$_GET[teteam];
					$tgtclass=$_GET[teclass];
					$teid=$c;
					$sql = "UPDATE targets SET target_value_low='$tevaluelow',target_value_high='$tevaluehigh',
					target_metric_id='$temetric',target_contract_id='$tecontract',target_team_id='$teteamid',target_start_date='$testartdate',
					target_stop_date='$testopdate',target_color='$tecolor',submetric='$tesubmetric',target_textcolor='$tefcolor',target_class='$tgtclass' WHERE target_id = '$c' LIMIT 1";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}
				else {
					$sql = "SELECT * FROM targets WHERE target_id = '$c' LIMIT 1";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					$tgt=$result->fetch_assoc();
					$tevaluelow = $tgt[target_value_low];
					$tevaluehigh = $tgt[target_value_high];
					$tecolor = $tgt[target_color];
					$tefcolor = $tgt[target_textcolor];
					$testartdate = substr($tgt[target_start_date],0,10);
					$testopdate = substr($tgt[target_stop_date],0,10);
					$temetricid = $tgt[target_metric_id];
					$tecontractid = $tgt[target_contract_id];
					$tesubmetric = $tgt[submetric];
					$teteamid = $tgt[target_team_id];
					$tgtclass = $tgt[target_class];
					echo "<form>
					Target value low: <input name='tevaluelow' value='$tevaluelow'><br>
					Target value high: <input name='tevaluehigh' value='$tevaluehigh'><br>
					Target bgcolor: <input name='tecolor' value='$tecolor'><br>
					Target fgcolor: <input name='tefcolor' value='$tefcolor'><br>
					Target start date: <input type=date name='testartdate' value='$testartdate'> (optional)<br>
					Target stop date: <input type=date name='testopdate' value='$testopdate'> (optional)<br>
					Metric: <select name='temetric' value='$temetric'>";
					$sql = "SELECT metric_id,metric_name FROM metrics ORDER by metric_name ASC";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					while($row=$result->fetch_assoc()){
						$selected='';if($row[metric_id]==$temetricid){$selected=' selected';}
						echo "<option value='{$row[metric_id]}'$selected>{$row[metric_name]}</option>";
					}
					echo "</select><br>
					Submetric: <select name='tesubmetric'>";
					$selected='';if($tesubmetric==''){$selected=' selected';}
					echo "<option value=''$selected>Combined</option>";
					$selected='';if($tesubmetric=='phone'){$selected=' selected';}
					echo "<option value='phone'$selected>Phone</option>";
					$selected='';if($tesubmetric=='email'){$selected=' selected';}
					echo "<option value='email'$selected>Email</option>";
					echo "</select><br>
					Contract: <select name='tecontract'>";
					$sql = "SELECT contract_id,contract_name FROM contracts ORDER by contract_name ASC";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					while($row=$result->fetch_assoc()){
						$selected='';if($row[contract_id]==$tecontractid){$selected=' selected';}
						echo "<option value='{$row[contract_id]}'$selected>{$row[contract_name]}</option>";
					}
					echo "</select><br>
					Team: <select name='teteam'>";
					$sql = "SELECT id, team_name FROM teams ORDER BY team_name ASC";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					while($row=$result->fetch_assoc()){
						$selected='';if($row[id]==$teteamid){$selected=' selected';}
						echo "<option value='{$row[id]}'$selected>{$row[team_name]}</option>";
					}
					echo "</select><br>";
		echo "Class: <input name=teclass value='$tgtclass'><br>";
					echo "
					<input name=a value=targets type=hidden>
					<input name=b value=e type=hidden>
					<input name=c value='$c' type=hidden>
					<input name=f value='$f' type=hidden>
					<input type=submit>
					</form><br>";
				}
			}
		}
elseif ($b=='addgroup'){
echo "<form>";
echo "Team: <select name='team'>";
echo "<option value='-1'>All teams</option>";
$sql = "SELECT id, team_name FROM teams ORDER BY team_name ASC";
if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
while($row=$result->fetch_assoc()){
	echo "<option value={$row[id]}";
	if ($team==$row[id]){echo " selected";}
	echo ">{$row[team_name]}</option>";
}
echo "</select><br>";
echo "Start date: $startdate<br>";
echo "End date: $enddate<br>";
			$sql = "SELECT metric_id, metric_name FROM metrics ORDER BY metric_name ASC";
echo "<table class=tabler width=100%><thead><tr><th></th>";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){
	echo "<th>$row[metric_name]</th>";
}
echo "<tr>";
echo "<td>Submetric</td>";
echo "<td><input name='submetric_aht' size=10></td>";
echo "<td><input name='submetric_crrr' size=10></td>";
echo "<td><input name='submetric_kdi' size=10></td>";
echo "<td><input name='submetric_nps' size=10></td>";
echo "<td><input name='submetric_pp' size=10></td>";
echo "<td><input name='submetric_sla' size=10></td>";
echo "<td><input name='submetric_tr' size=10></td>";
echo "<td></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Low</td>";
echo "<td><input name='low_aht' size=10></td>";
echo "<td><input name='low_crrr' size=10></td>";
echo "<td><input name='low_kdi' size=10></td>";
echo "<td><input name='low_nps' size=10></td>";
echo "<td><input name='low_pp' size=10></td>";
echo "<td><input name='low_sla' size=10></td>";
echo "<td><input name='low_tr' size=10></td>";

echo "</tr>";
echo "<tr>";
echo "<td>High</td>";
echo "<td><input name='high_aht' size=10></td>";
echo "<td><input name='high_crrr' size=10></td>";
echo "<td><input name='high_kdi' size=10></td>";
echo "<td><input name='high_nps' size=10></td>";
echo "<td><input name='high_pp' size=10></td>";
echo "<td><input name='high_sla' size=10></td>";
echo "<td><input name='high_tr' size=10></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Startdate</td>";
echo "</tr>";
echo "<tr>";
echo "<td>Enddate</td>";
echo "</tr>";
echo "<tr>";
echo "<td>Color</td>";
echo "</tr>";

echo "</tr></thead>";
echo "</table>";
echo "</form>";
}
elseif ($b=='add'){
			if ($_GET[contract]){
				$sql = "INSERT INTO targets (target_contract_id, target_team_id, target_metric_id, target_value_low, target_value_high,
				target_color,target_start_date,target_stop_date,submetric,target_textcolor)
				VALUES('{$_GET[contract]}','{$_GET[team]}','{$_GET[metric]}','{$_GET[value_low]}','{$_GET[value_high]}','{$_GET[bgcolor]}','{$_GET[startdate]}'
				,'{$_GET[stopdate]}','{$_GET[submetric]}','{$_GET[fgcolor]}')";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			else {
				echo "<form>
				Target value low: <input name='value_low'><br>
				Target value high: <input name='value_high'><br>
				Target bgcolor: <input name='bgcolor'><br>
				Target fgcolor: <input name='fgcolor'><br>
				Target start date: <input type=date name='startdate' value='{$startdate}'> (optional)<br>
				Target stop date: <input type=date name='stopdate' value='{$enddate}'> (optional)<br>
				Metric: <select name='metric'>";
				$sql = "SELECT metric_id,metric_name FROM metrics ORDER by metric_name ASC";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){
					$s='';if($f==$row[metric_id]){$s=' selected';}
					echo "<option value='{$row[metric_id]}'$s>{$row[metric_name]}</option>";
				}
				echo "</select><br>
				Submetric: <select name='submetric'>";
				echo "<option value=''>Combined</option>";
				echo "<option value='phone'>Phone</option>";
				echo "<option value='email'>Email</option>";
				echo "</select><br>
				Contract: <select name='contract'>";
				$sql = "SELECT contract_id,contract_name FROM contracts ORDER by contract_name ASC";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){ echo "<option value={$row[contract_id]}>{$row[contract_name]}</option>"; }
				echo "</select><br>
				Team: <select name='team'>";
				$sql = "SELECT id, team_name FROM teams ORDER BY team_name ASC";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){
					$selected='';if ($row[id]==$team){$selected = ' selected';}
					echo "<option value='{$row[id]}'$selected>{$row[team_name]}</option>";
				}
				echo "</select><br>";
				echo "<input type=hidden name=enddate value='$enddate'>";
				echo "<input type=hidden name=f value='$f'>";
				echo "<input name=a value=targets type=hidden><input name=b value=add type=hidden><input type=submit></form><br>";
			}
		}
	}
	$sql = "SELECT metric_id, metric_name FROM metrics LIMIT 50";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$x = 0;
	$f=$_GET[f];
	echo "Filter by metric: <select onChange='location.href=\"?a=$a&f=\" + this.value;'>";
	echo "<option value=''>---</option>";
	while($row=$result->fetch_assoc()){
		$x++;
		$metrics[$row[metric_id]] = $row[metric_name];
		$metric[$x] = $row;
		$s='';if($row[metric_id]==$f){$s=' selected';}
		echo "<option value='{$row[metric_id]}'$s>{$row[metric_name]}</option>";
	}
	echo "</select>";
	$mf='';if ($f){$mf=" AND target_metric_id='$f'";}
	$sql = "SELECT id, team_name FROM teams LIMIT 50";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){ $teams[$row[id]] = $row[team_name]; }
	if ($team>0){$teamster="AND target_team_id = '$team'";}
	$sql = "SELECT COUNT(DISTINCT submetric) as id FROM targets WHERE target_start_date <= '$startdate' AND target_stop_date >= '$enddate' $teamster AND active = 1 ORDER by target_team_id ASC, target_metric_id ASC LIMIT 500";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc(); $submetrics = $row[id];
	$sql = "SELECT DISTINCT submetric FROM targets WHERE target_start_date <= '$startdate' AND target_stop_date >= '$enddate' $teamster AND active = 1 ORDER by target_team_id ASC, target_metric_id ASC LIMIT 500";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$a = 0;
	while($row=$result->fetch_assoc()){
		$a++; $submetric[$a] = $row[submetric];
	}
	$sql = "SELECT * FROM targets WHERE target_start_date <= '$startdate' AND target_stop_date >= '$enddate' $teamster AND active = 1 ORDER by target_team_id ASC, target_metric_id ASC LIMIT 500";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){
		$tgtdata[$row[target_id]] = $row;
		$tds[$row[submetric]] = $row;
	}
	/*echo "<table class=tabler><thead><tr><th>submetric</th>";
	foreach($metrics as $key => $value){
		echo "<th>$value</th>";
	}
	echo "</tr></thead>";
	$smc = 1;
	foreach($tgtdata as $data) {
		// First the submetric.
		if ($smc <= $submetrics) {
			echo "<tr>";
			if ($submetric[$smc]==''){$submetric[$smc]='combined';}
			//$spanner = count($tgtdata[$metriccounter][$submetric[$smc]]);
			echo "<td rowspan='{$spanner}'>{$submetric[$smc]}</td>";
			echo "<td>$spanner</td>";
		}
	}
	echo "</table>";*/

	$sql = "SELECT * FROM targets WHERE target_start_date <= '$startdate' AND target_stop_date >= '$enddate' $teamster AND active = 1 $mf ORDER by target_team_id ASC, target_metric_id ASC, submetric ASC, target_value_low ASC LIMIT 500";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	echo "<table class='tabler sortable' cellspacing=0 cellpadding=5>";
	echo "<thead><tr>";
		if(isadmin()){
			echo "<th>Edit</th><th>Delete</th>";
		}
	echo "<th>Team</th>
	<th>Metric</th>
	<th>Submetric</th>
	<th>Low</th>
	<th>High</th>
	<th>Startdate</th>
	<th>Stopdate</th>
	<th>BG</th>
	<th>FG</th>
	</tr></thead>";
	while($row=$result->fetch_assoc()){
		echo "<tr>";
		$xo = " style='background:#$row[target_color];color:#$row[target_textcolor];'";
		$xx = " class='$row[target_class]'";
		if(isadmin()){
			echo "<td$xo><button onClick=\"location.href='?a=targets&b=e&c={$row[target_id]}';\">Edit</button></td>";
			echo "<td$xo><button onClick=\"location.href='?a=targets&b=d&c={$row[target_id]}';\">Delete</button></td>";
		}
		echo "<td$xo>{$teams[$row[target_team_id]]}</td>";
		echo "<td$xo>{$metrics[$row[target_metric_id]]}</td>";
		echo "<td$xo>$row[submetric]</td>";
		echo "<td$xo>$row[target_value_low]</td>";
		echo "<td$xo>$row[target_value_high]</td>";
		echo "<td$xo>$row[target_start_date]</td>";
		echo "<td$xo>$row[target_stop_date]</td>";
		echo "<td$xo>$row[target_color]</td>";
		echo "<td$xo>$row[target_textcolor]</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "<h2>Timeless targets {$teams[$team]}</h2>";
	$sql = "SELECT * FROM targets WHERE target_start_date = '0000-00-00 00:00:00' $teamster AND active = 1 $mf ORDER by target_team_id ASC, target_metric_id ASC, submetric ASC, target_value_low ASC LIMIT 500";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	echo "<table class='tabler sortable' cellspacing=0 cellpadding=5>";
	echo "<thead><tr>
	<th>Edit</th>
	<th>Delete</th>
	<th>Team</th>
	<th>Metric</th>
	<th>Submetric</th>
	<th>Low</th>
	<th>High</th>
	<th>Startdate</th>
	<th>Stopdate</th>
	<th>BG</th>
	<th>FG</th>
	</tr></thead>";
	while($row=$result->fetch_assoc()){
		echo "<tr>";
		$xo = " style='background:#$row[target_color];color:#$row[target_textcolor];'";
		echo "<td$xo><button onClick=\"location.href='?a=targets&b=e&c={$row[target_id]}';\">Edit</button></td>";
		echo "<td$xo><button onClick=\"location.href='?a=targets&b=d&c={$row[target_id]}';\">Delete</button></td>";
		echo "<td$xo>{$teams[$row[target_team_id]]}</td>";
		echo "<td$xo>{$metrics[$row[target_metric_id]]}</td>";
		echo "<td$xo>$row[submetric]</td>";
		echo "<td$xo>$row[target_value_low]</td>";
		echo "<td$xo>$row[target_value_high]</td>";
		echo "<td$xo>$row[target_start_date]</td>";
		echo "<td$xo>$row[target_stop_date]</td>";
		echo "<td$xo>$row[target_color]</td>";
		echo "<td$xo>$row[target_textcolor]</td>";
		echo "</tr>";
	}
	echo "</table>";
}
