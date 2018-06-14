<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
		$employee=$_GET[c];
		if ($employee){
			$surveys = countsurveys($employee);
			$manager = getmanager($employee);
			echo "Employee $employee<br>";
			echo "Surveys: $surveys<br>";
			echo "Line manager: $manager<br>";
			echo "Team: " . getteamname(guessteam($employee));
		}
		else {
			$sql = "SELECT distinct Teammate_NT_ID,Teammate_Name FROM raw_data $sqldater ORDER by Teammate_Name ASC";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$empnumber = 1;
			while($row=$result->fetch_assoc()){
				$empteam=guessteam($row[Teammate_NT_ID]);
				if ($team>-1){
					if ($empteam == $team) {
						echo "<div class='tbl_empnumber'>$empnumber</div>";
						echo "<div class='tbl_employees'><a href='?a=$a&b=show_employee&c={$row[Teammate_NT_ID]}'>".$row[Teammate_Name]."</a></div>";
						echo "<div class='tbl_empuserid'>{$row[Teammate_NT_ID]}</div>";
						echo "<div class='tbl_empteam'>".getteamname($empteam)."</div>";
						$empnumber++;
					}
				}
				else {
					echo "<div class='tbl_empnumber'>$empnumber</div>";
					echo "<div class='tbl_employees'><a href='?a=employees&b=show_employee&c={$row[Teammate_NT_ID]}'>".$row[Teammate_Name]."</a></div>";
					echo "<div class='tbl_empuserid'>{$row[Teammate_NT_ID]}</div>";
					echo "<div class='tbl_empteam'>".getteamname($empteam)."</div>";
					$empnumber++;
				}
			}
		}
	}
