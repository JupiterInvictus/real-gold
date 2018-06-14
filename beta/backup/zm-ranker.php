<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate, $today;

	// if a team has been specified, only show elites in that team.
	if ($team > 0) {
		echo "<h1>Team " . getteamname($team) . "</h1>";
	}
	else { echo "<h1>Belfast</h1>"; }

	if ($enddate > $today) {
		echo "<b>Warning:</b> using the current month will display incomplete data potentially leading to premature conclusions.<br><br>";
	}

	/*$statement = "SELECT id, title, left_or_right FROM titles";
	if (!$result = $db->query($statement)) {
		cl($statement);
		cl($db->error);
	}
	while ($row = $result->fetch_assoc()) {
		$titles[$row['id']] = $row;
	}

	$statement = "SELECT id, titleid FROM ranks";
	if (!$result = $db->query($statement)) {
		cl($statement);
		cl($db->error);
	}
	while ($row = $result->fetch_assoc()) {
		$ranks[$row['id']] = $row;
	}*/

	$statement = "SELECT teammate_nt_id, teammate_name, COUNT(external_survey_id) AS surveys FROM raw_data $sqldater $teamdefinition AND likely_to_recommend_paypal > 7 AND (kdi___email > 75 OR kdi___phone > 75) AND issue_resolved = 'Yes' GROUP BY teammate_name ORDER BY teammate_name ASC";
	if (!$result = $db->query($statement)) {
		cl($statement);
		cl($db->error);
	}
	while ($row = $result->fetch_assoc()) {
		$teammate['name'][$row['teammate_nt_id']] = $row['teammate_name'];
		$teammate['goodsurveys'][$row['teammate_nt_id']] = $row['surveys'];
	}

	$totalsurveys = 0;
	$totalgoodsurveys = 0;

	$xp_per_level = 3900;

	$average_level = 0;
	$total_xp = 0;
	$total_agents = 0;

	$statement = "SELECT teammate_nt_id, teammate_name, COUNT(external_survey_id) AS surveys FROM raw_data $sqldater 	$teamdefinition AND (kdi___email <> '' OR kdi___phone <> '') GROUP BY teammate_name ORDER BY teammate_name ASC";
	if (!$result = $db->query($statement)) {
		cl($sqla);
		cl($db->error);
	}

	$total_contacts_handled = 0;

	while ($row = $result->fetch_assoc()) {
		// Rank signifies what your skill level is. This is based on the selected date range.
		$teammate['surveys'][$row['teammate_nt_id']] = $row['surveys'];
		$teammate['rank'][$row['teammate_nt_id']] = round($teammate['goodsurveys'][$row['teammate_nt_id']] / $teammate['surveys'][$row['teammate_nt_id']] * 10, 0);
		$totalsurveys = $totalsurveys + $teammate['surveys'][$row['teammate_nt_id']];
		$totalgoodsurveys = $totalgoodsurveys + $teammate['goodsurveys'][$row['teammate_nt_id']];
		$teammate['guild'][$row['teammate_nt_id']] = guessteam($row['teammate_nt_id']);
		$teammate['guildname'][$row['teammate_nt_id']] = g("teams","team_name",$teammate['guild'][$row['teammate_nt_id']]);

	}
	arsort($teammate['rank']);
	foreach ($teammate['rank'] as $ntid => $level) {
		if (($team < 1) or (guessteam($ntid) == $team)) {
			$teammate['level'][$ntid] = agent_getlevel($ntid);
			if ($teammate['level'][$ntid] >= 0) {
				$total_xp += $teammate['level'][$ntid];
				$total_agents++;
				$r = round($teammate['rank'][$ntid],0);
				if (($r != $oldrank) && ($r < 11)) {
					echo "<div class='clearer'>";
					echo "</div>";
				}
				$fgcolor = g("teams","team_fgcolor",$teammate['guild'][$ntid]);
				$bgcolor = g("teams","team_bgcolor",$teammate['guild'][$ntid]);
				$brcolor = g("teams","team_border",$teammate['guild'][$ntid]);
				$ra = round($r*$r/1.5);
				echo "<div class='ranker' onClick='location.href=\"?a=user&b=$ntid\";' style='background: #$bgcolor; color: #$fgcolor; min-width: {$ra}vw;'>";
				$names = explode(' ', $teammate['name'][$ntid]);
				echo "<div class='ranker-name'>";
					//echo $teammate['name'][$ntid];
					echo $names[0];
				echo "</div>";
				echo "<div class='ranker-username'>";
					echo $ntid;
				echo "</div>";
				echo "<div class='ranker-photo'>";
				echo getphoto($ntid, 48);
				echo "</div>";

				echo "</div>";
			}
		}
	}
}
