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
			$emails_worked = db_get("SELECT sum(email_worked) as esi FROM prt060data WHERE ntid = '{$b}' AND queue_name <> 'Total'")['esi'];
			echo "<td>$emails_worked</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>+ Lifetime phone calls:</td>";
			$calls_answered = db_get("SELECT sum(phone_answered) as esi FROM prt060data WHERE ntid = '{$b}' AND queue_name <> 'Total'")['esi'];
			echo "<td>$calls_answered</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>= Lifetime contacts:</td>";
			$contacts = $emails_worked + $calls_answered;
			echo "<td>$contacts</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>Months</td>";
			$months = getUserMonths($b);
			echo "<td>$months</td>";
		echo "</tr>";

		echo "</table>";

	}
}
