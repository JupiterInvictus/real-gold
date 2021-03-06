<?php
$showNewHires = db_get("SELECT user_excludenewhires FROM users WHERE user_id = '{$uid}' LIMIT 1")['user_excludenewhires'];

function show_module() {

	global $db, $startdate, $sqldater, $currentyear, $currentmonth, $monthy, $contract, $team_id_def, $uid, $simplecolors, $app_action, $showNewHires;

	// simple color switch
	if (($_GET['x'] == 'simplecolors') && ($uid)) {
		if ($simplecolors) {
			$simplecolors = 0;
		}
		else {
			$simplecolors = 1;
		}
		sq("UPDATE users SET user_simplecolors = $simplecolors WHERE user_id = $uid LIMIT 1");
		setaddress("?a=$app_action");
	}
	else if (($_GET['x'] == 'newhires') && ($uid)) {
		if ($showNewHires) {
			$showNewHires = 0;
		}
		else {
			$showNewHires = 1;
		}
		sq("UPDATE users SET user_excludenewhires = $showNewHires WHERE user_id = $uid LIMIT 1");
		setaddress("?a=$app_action");
	}

	echo "<a class='button-newhires' href='?a=dashboard&x=newhires'>";
	if ($showNewHires) { echo "Exclude"; }
	else { echo "Include"; }
	echo " New Hires in KDI and NPS</a>";
	echo "<a class='button-simple' href='?a=dashboard&x=simplecolors'>";
	if ($simplecolors) { echo "Complex"; }
	else { echo "Simple"; }
	echo " Colours</a>";
	echo "<div class='tabs'>";
	if (!isset($_GET['b'])) {
		$_GET['b'] = 'core';
	}
	if ($_GET['b'] == '') { $_GET['b'] = 'core'; }
	$tabselected='';if ($_GET['b'] == 'core') { $tabselected=' tab-selected'; }
	echo "<a class='tab$tabselected' href='?a=dashboard&b=core'>Core Metrics</a>";
	$tabselected='';if ($_GET['b'] == 'volumes') { $tabselected=' tab-selected'; }
	echo "<a class='tab$tabselected' href='?a=dashboard&b=volumes'>Volumes</a>";
	$tabselected='';if ($_GET['b'] == 'slatr') { $tabselected=' tab-selected'; }
	echo "<a class='tab$tabselected' href='?a=dashboard&b=slatr'>SLA & TR</a>";
	echo "</div>";

	echo "<table class='dashboard'>";

	$dksurveys = surveycount("",$team_id_def[5]);
	$nlsurveys = surveycount("",$team_id_def[7]);
	$nosurveys = surveycount("",$team_id_def[6]);
	$sesurveys = surveycount("",$team_id_def[4]);

	$pvalue[dk]=vm("pvol",5,$startdate);
	$pvalue[nl]=vm("pvol",7,$startdate);
	$pvalue[no]=vm("pvol",6,$startdate);
	$pvalue[se]=vm("pvol",4,$startdate);

	$evalue[dk]=vm("evol",5,$startdate);
	$evalue[nl]=vm("evol",7,$startdate);
	$evalue[no]=vm("evol",6,$startdate);
	$evalue[se]=vm("evol",4,$startdate);

	$ptr[dk]=vm("ptr",5,$startdate);
	$ptr[nl]=vm("ptr",7,$startdate);
	$ptr[no]=vm("ptr",6,$startdate);
	$ptr[se]=vm("ptr",4,$startdate);

	$etr[dk]=vm("etr",5,$startdate);
	$etr[nl]=vm("etr",7,$startdate);
	$etr[no]=vm("etr",6,$startdate);
	$etr[se]=vm("etr",4,$startdate);

	echo "
	<thead id='teams'>
		<th></th>
		<th class='dashboard-team'>
			<a href='?a=team&b=5'>
				<div class='dashboard-team-flag'><i class='em em-flag-dk'></i></div>
				<div class='dashboard-team-name'>Denmark</div>
			</a>
			<span class='dashboard-team-surveys' title='Surveys received'><i class='far fa-".geticon('surveys')."'></i> $dksurveys</span>
			<span class='dashboard-team-latestsurvey'><i class='far fa-clock'></i> ".getlatest($team_id_def[5])."</span>
		</th>

		<th class='dashboard-team'>
			<a href='?a=team&b=7'>
				<div class='dashboard-team-flag'><i class='em em-flag-nl'></i></div>
				<div class='dashboard-team-name'>Netherlands</div>
			</a>
			<span class='dashboard-team-surveys' title='Surveys received'><i class='far fa-".geticon('surveys')."'></i> $nlsurveys</span>
			<span class='dashboard-team-latestsurvey'><i class='far fa-clock'></i> ".getlatest($team_id_def[7])."</span>
		</th>

		<th class='dashboard-team'>
			<a href='?a=team&b=6'>
				<div class='dashboard-team-flag'><i class='em em-flag-no'></i></div>
				<div class='dashboard-team-name'>Norway</div>
			</a>
			<span class='dashboard-team-surveys' title='Surveys received'><i class='far fa-".geticon('surveys')."'></i> $nosurveys</span>
			<span class='dashboard-team-latestsurvey'><i class='far fa-clock'></i> ".getlatest($team_id_def[6])."</span>
		</th>

		<th class='dashboard-team'>
			<a href='?a=team&b=4'>
				<div class='dashboard-team-flag'><i class='em em-flag-se'></i></div>
				<div class='dashboard-team-name'>Sweden</div>
			</a>
			<span class='dashboard-team-surveys' title='Surveys received'><i class='far fa-".geticon('surveys')."'></i> $sesurveys</span>
			<span class='dashboard-team-latestsurvey'><i class='far fa-clock'></i> ".getlatest($team_id_def[4])."</span>
		</th>
	</thead>";

	if ($_GET['b'] == 'core') {
		echo "<tr><td></td><td colspan=11 class='big-title'>Core Metrics</td></tr>";
		echo "<tr>";
			echo "<td class='dashboard-metric-name'>AHT</td>";
			echo displayvmbox(5,2);
			echo displayvmbox(7,2);
			echo displayvmbox(6,2);
			echo displayvmbox(4,2);
		echo "</tr>";

		echo "<tr>";
			echo "<td class='dashboard-metric-name'>KDI</td>";
			echo displaydashboardbox(5,5);
			echo displaydashboardbox(7,5);
			echo displaydashboardbox(6,5);
			echo displaydashboardbox(4,5);
		echo "</tr>";

		echo "<tr>";
		echo "<td class='dashboard-metric-name'>TR</td>";

		$value=((($etr[dk]*$evalue[dk])+($ptr[dk]*$pvalue[dk]))/($evalue[dk]+$pvalue[dk])*100);
		echo displayvmbox(5,6,$value);
		$value=((($etr[nl]*$evalue[nl])+($ptr[nl]*$pvalue[nl]))/($evalue[nl]+$pvalue[nl])*100);
		echo displayvmbox(7,6,$value);
		$value=((($etr[no]*$evalue[no])+($ptr[no]*$pvalue[no]))/($evalue[no]+$pvalue[no])*100);
		echo displayvmbox(6,6,$value);
		$value=((($etr[se]*$evalue[se])+($ptr[se]*$pvalue[se]))/($evalue[se]+$pvalue[se])*100);
		echo displayvmbox(4,6,$value);

		echo "</tr>";

		echo "<tr>";
			echo "<td class='dashboard-metric-name'>";
			echo "RCR";
			echo "</td>";
			echo displayvmbox(5,17);
			echo displayvmbox(7,17);
			echo displayvmbox(6,17);
			echo displayvmbox(4,17);
		echo "</tr>";

		echo "<tr><td></td><td colspan=10 class='big-title'>Kickers</td></tr>";
		echo "<tr>";
			echo "<td class='dashboard-metric-name'>NPS</td>";
			echo displaydashboardbox(5,4);
			echo displaydashboardbox(7,4);
			echo displaydashboardbox(6,4);
			echo displaydashboardbox(4,4);
		echo "</tr>";
	}
	else if ($_GET['b'] == 'volumes') {
		// VOLUMES
		echo "<tr><td></td><td colspan=10 class='big-title'>Volumes</td></tr>";
		echo "<tr>";
		echo "<td class='dashboard-metric-name'>Phone</td>";
		echo displayvmbox(5,12,$pvalue[dk]);
		echo displayvmbox(7,12,$pvalue[nl]);
		echo displayvmbox(6,12,$pvalue[no]);
		echo displayvmbox(4,12,$pvalue[se]);
		echo "</tr>";

		echo "<tr>";
		echo "<td class='dashboard-metric-name'>Email</td>";
		echo displayvmbox(5,14,$evalue[dk]);
		echo displayvmbox(7,14,$evalue[nl]);
		echo displayvmbox(6,14,$evalue[no]);
		echo displayvmbox(4,14,$evalue[se]);
		echo "</tr>";

		echo "<tr>";
		echo "<td class='dashboard-metric-name'>Combined</td>";
		echo displayvmbox(5,14,$evalue[dk]+$pvalue[dk]);
		echo displayvmbox(7,14,$evalue[nl]+$pvalue[nl]);
		echo displayvmbox(6,14,$evalue[no]+$pvalue[no]);
		echo displayvmbox(4,14,$evalue[se]+$pvalue[se]);
		echo "</tr>";

		echo "<tr>";
		echo "<td class='dashboard-metric-name'>Email / Phone Ratio</td>";
		echo displayvmbox(5,19,round($evalue[dk]/$pvalue[dk]*100));
		echo displayvmbox(7,19,round($evalue[nl]/$pvalue[nl]*100));
		echo displayvmbox(6,19,round($evalue[no]/$pvalue[no]*100));
		echo displayvmbox(4,19,round($evalue[se]/$pvalue[se]*100));
		echo "</tr>";

		echo "<tr>";
		echo "<td class='dashboard-metric-name'>Surveys / Volume Ratio</td>";
		echo displayvmbox(5,20,round($dksurveys/($evalue[dk]+$pvalue[dk])*100));
		echo displayvmbox(7,20,round($nlsurveys/($evalue[nl]+$pvalue[nl])*100));
		echo displayvmbox(6,20,round($nosurveys/($evalue[no]+$pvalue[no])*100));
		echo displayvmbox(4,20,round($sesurveys/($evalue[se]+$pvalue[se])*100));
		echo "</tr>\n";

	}
	else {
		// MISCELLANEOUS
		echo "<tr><td></td><td colspan=10 class='big-title'>Service level</td></tr>";
		echo "<tr><td class='dashboard-metric-name'>Email</td>\n";
		echo displayvmbox(5,13);
		echo displayvmbox(7,13);
		echo displayvmbox(6,13);
		echo displayvmbox(4,13);
		echo "</tr>\n";

		// MISCELLANEOUS
		echo "<tr><td></td><td colspan=10 class='big-title'>Transfer rate</td></tr>";
		echo "<tr><td class='dashboard-metric-name'>Phone</td>\n";
		echo displayvmbox(5,6,$ptr[dk]*100);
		echo displayvmbox(7,6,$ptr[nl]*100);
		echo displayvmbox(6,6,$ptr[no]*100);
		echo displayvmbox(4,6,$ptr[se]*100);
		echo "</tr>\n";

		echo "<tr><td class='dashboard-metric-name'>Email</td>\n";
		echo displayvmbox(5,6,$etr[dk]*100);
		echo displayvmbox(7,6,$etr[nl]*100);
		echo displayvmbox(6,6,$etr[no]*100);
		echo displayvmbox(4,6,$etr[se]*100);
		echo "</tr>\n";
	}

	echo "</table>\n";
	echo "</div>\n";
}



/*function displayDashboardBox($displayTeam, $displayMetric, $displayValue) {
	global $startdate, $enddate, $team_id_def, $today, $contract, $simplecolors;

}*/
