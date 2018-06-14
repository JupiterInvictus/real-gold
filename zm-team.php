<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate, $team_id_def;
	global $db, $startdate, $sqldater, $currentyear, $currentmonth, $monthy, $contract, $team_id_def, $uid, $simplecolors, $app_action, $showNewHires;

	$teamname = agent_getteamname($team);
	echo "<h1>";
	if (isset($teamname)) { echo "Team " . $teamname; }
	else { echo "All teams"; }
	echo "</h1>";

	echo "<h2>Team Leaders</h2>";
	echo "<ul>";
	$query = "SELECT team_leader_name, teammate_nt_id FROM raw_data $sqldater $teamdefinition GROUP BY team_leader_name ORDER BY team_leader_name";
	if (!$result = $db->query($query)) { cl($query); cl($db->error); }
	while ($row = $result->fetch_assoc()) {

		if ($team == -1) {
			echo "<li>" . $row['team_leader_name'];
		}
		else {
			if (guessteam($row['teammate_nt_id']) == $team) {
				echo "<li>" . $row['team_leader_name'];
			}
		}
	}
	echo "</ul>";

	if (isset($teamname)) {
		// Display a list of all metrics. Combined and per channel.
		echo "<h2>Metrics</h2>";
		echo "<table width='100%'>";
		echo "<thead>";
			echo "<th>AHT</th>";
			echo "<th>KDI</th>";
			echo "<th>TR</th>";
			echo "<th>RCR</th>";
			echo "<th>NPS</th>";
		echo "</thead>";
		echo "<tbody>";

		$surveys = surveycount("",$team_id_def[$team]);
		$pvalue=vm("pvol",$team, $startdate);
		$evalue=vm("evol",$team, $startdate);
		$ptr=vm("ptr",$team, $startdate);
		$etr=vm("etr",$team, $startdate);
		echo displayvmbox($team, 2);
		echo displaydashboardbox($team, 5);
		$value=((($etr*$evalue)+($ptr*$pvalue))/($evalue+$pvalue)*100);
		echo displayvmbox(4,6,$value);
		echo displayvmbox($team, 17);
		echo displaydashboardbox($team, 4);

		echo "</tbody>";
		echo "</table>";

	}

	// Display a list of all agents.
	echo "<h2>Active Team Mates</h2>";
	echo "<table class='sortable'>";
	echo "<thead>";
	echo "<th>Badge</th>";
	echo "<th>Level</th>";
	echo "<th>Rank Icon</th>";
	echo "<th>Rank</th>";
	echo "<th>Name</th>";
	echo "<th>Team Leader</th>";
	echo "</thead>";

	$query = "SELECT teammate_nt_id, teammate_name, team_leader_name FROM raw_data $sqldater $teamdefinition GROUP BY teammate_nt_id ORDER BY teammate_name";
	if (!$result = $db->query($query)) { cl($query); cl($db->error); }
	while ($row = $result->fetch_assoc()) {
		if ($team == -1) {
			echo "<tr>\n";
			echo "<td>" . getUserBadge($row['teammate_nt_id']) . "</td>\n";
			echo "<td>" . agent_getlevel($row['teammate_nt_id']) . "</td>\n";
			echo "<td>" . agent_getrankicon(agent_getrank($row['teammate_nt_id'])) . "</td>\n";
			echo "<td>" . agent_getrank($row['teammate_nt_id']) . "</td>\n";
			echo "<td>" . $row['teammate_name'] . "</td>\n";
			echo "<td>" . $row['team_leader_name'] . "</td>\n";
			echo "</tr>\n\n";
		}
		else {
			if (guessteam($row['teammate_nt_id']) == $team) {
			echo "<tr>\n";
			echo "<td>" . getUserBadge($row['teammate_nt_id']) . "</td>\n";
			echo "<td>" . agent_getlevel($row['teammate_nt_id']) . "</td>\n";
			echo "<td>" . agent_getrankicon(agent_getrank($row['teammate_nt_id'])) . "</td>\n";
			echo "<td>" . agent_getrank($row['teammate_nt_id']) . "</td>\n";
			echo "<td>" . $row['teammate_name'] . "</td>\n";
			echo "<td>" . $row['team_leader_name'] . "</td>\n";
			echo "</tr>\n\n";
		}
		}
	}
	echo "</table>";

	// Display a list of top contact reasons. Combined and per channel.
	echo "<h2>Top 5 Contract Reasons</h2>";

}
