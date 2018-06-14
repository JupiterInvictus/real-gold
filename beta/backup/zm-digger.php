<?php

function show_module() {
	global $db, $startdate, $sqldater, $currentyear, $currentmonth, $monthy, $contract, $team_id_def, $teamdefinition, $uid, $app_action;

	echo "<div>Select a verbatim type: ";
	showSelect('diggercolumns', 'digger');
	echo "</div>";

	echo "<div>Filter by: ";
	showUserSelect();
	echo "</div>";




	// Has a digger been selected?
	if (isset($_GET['digger'])) {
		echo "<table class='no-padding sortable'>";
		echo "<thead><th>Survey</th><th>NTID</th><th>{$_GET['digger']}</th></thead>";
		$query = "SELECT teammate_nt_id,external_survey_id,{$_GET['digger']} FROM raw_data $sqldater $teamdefinition ";
		if ($_GET['filter'] != '') {
			$query .= "AND teammate_nt_id = '{$_GET['filter']}' ";
		}
		$query .= "ORDER by teammate_nt_id";
		if(!$r = $db->query($query)) { }
		while ($ret = $r->fetch_assoc()) {
			if ($ret[$_GET['digger']] != '') {
				echo "<tr>";
				echo "<td><a href='?a=surveys&e={$ret['external_survey_id']}'>".$ret['external_survey_id'].'</a></td>';
				echo "<td class='userbadge'><a href='?a=user&b={$ret['teammate_nt_id']}'>";
				echo getUserBadge($ret['teammate_nt_id']);
				echo "</a></td>";
				echo "<td>{$ret[$_GET['digger']]}</td>";
				echo "</tr>";
			}
		}
		echo "</table>";
	}
}

function dug($column_name, $survey_id) {
	if (isset(db_get("SELECT column_name FROM diggeractivity WHERE column_name = '{$column_name}' AND survey_id = '{$survey_id}' AND uid = '{$_SESSION['user_id']}' LIMIT 1")['column_name'])) {
		return true;
	}

	return false;
}

?>
