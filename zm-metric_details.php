<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	echo "<h1>Team " . getteamname($_GET['c']) . " / " . gg('metrics','metric_name','metric_id',$_GET['b']) . "</h1>";
	echo "<h2>History</h2>";
		echo "<div class='metric_history'>";
			echo "metric history, able to choose which intervals, years quarters and months etc.";
			echo "</div>";

	echo "<h2>Top Drivers</h2>";

	echo "<h2>Performance Distribution</h2>";
		echo "<div class='performance-distribution'>";
			echo "% of top, mid and bottom performers. historically";
		echo "</div>";

	echo "<h2>Bottom 5% Performers</h2>";
		echo "<div class='bottom-performer-history'>";
			echo "History of bottom performers";
		echo "</div>";
}
