<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	if ($b) {
		echo getUserBadge($b);
		echo "<table class='tabler'>";
		echo "<tr><td>Current rank:</td><td>" . agent_getrankicon(agent_getrank($b)) . "</td></tr>";
		echo "<tr><td>Team:</td><td>" . agent_getteamname(guessteam($b)) . "</td></tr>";

		echo "<tr>";
			echo "<td>Lifetime surveys:</td>";
			$surveys = db_get("SELECT count(external_survey_id) as esi FROM raw_data WHERE teammate_nt_id = '{$b}'")['esi'];
			echo "<td>$surveys</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>Lifetime emails:</td>";
			echo "<td>" . getContacts($b, 'email') . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>+ Lifetime phone calls:</td>";
			echo "<td>" . getContacts($b, 'phone') . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>= Lifetime contacts:</td>";
			echo "<td>" . getContacts($b) . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>Months</td>";
			echo "<td>" . getUserMonths($b) . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>Average contacts/month</td>";
			echo "<td>" . cpm($b) . "</td>";
		echo "</tr>";
		echo "</table>";

	}
}
