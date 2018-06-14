<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
		if ($team>0){
			echo "<h2>Team ". getteamname($team) ."</h2>";
		}
		else {
			echo "<h2>All Teams</h2>";
		}
		$daychosen = $today;
		if ($_GET[showday]){$daychosen=$_GET[showday];}
		echo "<input onChange='location.href=\"?a=$a&team=$team&showday=\" + this.value;' type=date value='$daychosen'>";
		if ($daychosen != $today){
			echo " <a href='?a=$a&team=$team&showday=$today'>Show today</a>";
		}
		if ($_GET[msid]){
			$msid = $_GET[msid];
			$sql = "SELECT completed FROM mystats WHERE msid = '$msid'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			$newcompleted=0;if($row[completed]==0){$newcompleted=1;}
			$sql = "UPDATE mystats SET completed = '$newcompleted' WHERE msid = {$_GET[msid]}";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}

		$sql = "SELECT * FROM mystats WHERE msdate = '$daychosen'";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "<table border=1>";
		echo "<th>ntid</th>";
		echo "<th>empid</th>";
		echo "<th>starttime</th>";
		echo "<th>Extend break by</th>";
		echo "<th>Trn/Mtg Minutes</th>";
		echo "<th>Trn/Mtg Explanation</th>";
		echo "<th>Completed</th>";
		while($row=$result->fetch_assoc()){
			if (($team<0) or (guessteam($row[ntid])==$team)){
				echo "<tr>";
				echo "<td>" . $row[ntid] . "</td>";
				echo "<td>" . $row[empid] . "</td>";
				echo "<td>" . $row[starthour] . ":" . $row[startmin]. "</td>";
				echo "<td>" . $row[deduct] . "</td>";
				echo "<td>" . $row[trnmtgmins] . "</td>";
				echo "<td>" . $row[trnmtgexplanation] . "</td>";
				$totaldeduct += $row[deduct];
				$totaltrnmtg += $row[trnmtgmins];
				$cc='c20';
				$msid = $row[msid];
				$checked='';if ($row[completed]>0){$checked=' checked';$cc='02c';}
				echo "<td onClick='location.href=\"?a=$a&b=$b&c=$c&d=$d&startdate=$startdate&enddate=$enddate&team=$team&showday=$daychosen&msid=$msid\";' style='background-color:#$cc'><input type='checkbox'$checked>";
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</table>";
		echo "Total deducted break minutes: $totaldeduct.<br>";
		echo "Total training/meeting minutes: $totaltrnmtg.<br>";
	}
