<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;

	echo "<h2>PRT060p</h2>";
	$sql = "SELECT DISTINCT month FROM prt060data";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while ($row=$result->fetch_assoc()){
		$mdyear = substr($row[month],-4,4);
		$mdmonth = substr($row[month],0,3);
		$monthdata[$mdyear][$mdmonth] = 1;
	}

	// Show each year since the start.
	for ($x = 2012; $x <= $currentyear; $x++) {
		echo "<table class='bt'><caption>$x</caption>";
		$monthis = 0;
		 for ($y = 1; $y < 5; $y++){
			 echo "<tr height=10>";
			 for ($z = 1; $z < 4; $z++){
				 echo "<td width=90";
				 $monthis++;
				 $mtext = $monthy[$monthis];
				 if ($monthdata[$x][$mtext]) {
					 //echo " style='background-color:#006000;color:#fff;'";
				 }
				 else {
					 echo " style='background-color:#600000;color:#fff;'";
				 }
				 echo ">$mtext-$x</td>";
			 }
			 echo "</tr>";
		 }
		echo "</table>";
	}

}
