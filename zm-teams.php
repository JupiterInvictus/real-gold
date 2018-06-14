<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	$teamname = agent_getteamname($team);
	echo "<h1>";
	if (isset($teamname)) { echo "Team " . $teamname; }
	else { echo "All teams"; }
	echo "</h1>";

	// Display a list of all metrics. Combined and per channel.
	echo "<h2>Metrics</h2>";

	// Display a list of all agents.
	echo "<h2>Team Mates</h2>";

	// Display a list of top contact reasons. Combined and per channel.
	echo "<h2>Top 5 Contract Reasons</h2>";

}
