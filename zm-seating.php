<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	$width = 16;
	$height = 20;
	$sql = "SELECT * FROM seating";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while ($row=$result->fetch_assoc()) {
		$xcoord = $row['xcoord'];
		$ycoord = $row['ycoord'];
		$seatingdata[$xcoord][$ycoord] = $row;
		if ($row['rotation'] < 4) {
			$seatcount[$row[teamid]]++;
		}
	}
	arsort($seatcount);
	echo "<div class='seating-legend'>";
	foreach ($seatcount as $team => $key) {
		$tmpname = sqr("SELECT team_name FROM teams WHERE id = '$team'")['team_name'];
		if ($tmpname == '') { $tmpname = 'Other'; }
		echo "$tmpname = $key<br>";
	}
	echo "</div>";
	echo "<div class='seating-tools'>";
	echo "Design <button id='desk'>Desk</button>";
	echo "<button id='wall'>Wall</button>";
	echo "<button id='door'>Door</button>";
	echo "<button id='issues'>Issues</button>";
	echo "Assign ";
	echo "team:<br> <select id='assignteam'>";
	echo "<option value='-1'>--</option>";
	$sql = "SELECT id,team_name FROM teams ORDER BY team_name ASC";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while ($row=$result->fetch_assoc()) {
		echo "<option value={$row[id]}>{$row[team_name]}</option>";
	}
	echo "</select>";
	echo "Assign person:<br> <select id='assignperson'>";
	echo "</select>";
	echo "<button id='manuallyAssign'>Manually assign</button>";
	echo "<button id='unassign'>Unassign</button>";
	echo "</div>";
	echo "<table class='seating'>";
	echo "<tr><td></td>";
	for ($w = 1; $w <= $width; $w++) {
		echo "<td>$w</td>";
	}
	echo "</tr>";
	$c_seatable = 'seatable';
	for ($h = 1; $h <= $height; $h++) {
		echo "<tr>";
		echo "<td>$h</td>";
		for ($w = 1; $w <= $width; $w++) {
			if ($seatingdata[$w][$h]['xcoord']>0) {
				if ($seatingdata[$w][$h]['rotation'] == 0) {
					$seatclass = 'seatedbottom';
				}
				else if ($seatingdata[$w][$h]['rotation'] == 1) {
					$seatclass = 'seatedleft';
				}
				else if ($seatingdata[$w][$h]['rotation'] == 2) {
					$seatclass = 'seatedtop';
				}
				else if ($seatingdata[$w][$h]['rotation'] == 3) {
					$seatclass = 'seatedright';
				}
				else if ($seatingdata[$w][$h]['rotation'] == 4) {
					$seatclass = 'wall';
				}
				else if ($seatingdata[$w][$h]['rotation'] == 5) {
					$seatclass = 'door';
				}
				else if ($seatingdata[$w][$h]['rotation'] == 6) {
					$seatclass = 'issues';
				}
				if ($seatingdata[$w][$h]['teamid']>0) {
					$seatclass .= ' team' . $seatingdata[$w][$h]['teamid'];
				}
			}
			else { $seatclass = $c_seatable; }
			echo "<td class='$seatclass' id='seat-$w-$h'>{$seatingdata[$w][$h]['ntid']}</td>\n";
		}
		echo "</tr>";
	}
}
