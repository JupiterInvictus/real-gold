<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
		// big input field
		if (isset($_GET[search])) { $search = $_GET[search]; }
		/*echo "<form>";
		echo "<input size=30 id='searchfield' name='search' value='$search' autofocus><button type=submit>Search</button>
		<input type=hidden name='a' value=search>
		</form>";*/
		if (isset($_GET['search'])){

			// Search should be case insensitive.
			$_GET['search'] = strtolower($_GET['search']);


			// First check whether an ntid matches.
			//
			$ntid = db_get("SELECT teammate_nt_id FROM raw_data WHERE teammate_nt_id = '{$_GET['search']}' LIMIT 1")['teammate_nt_id'];

			if ($ntid) {
				echo "<script>location.href='https://saettem.com/gold/beta/?a=user&b={$ntid}&search={$_GET['search']}';</script>";
			}




			echo "<table id=searchresults class=bt>";
			echo "<thead>
			<tr>
			<th>
				Source
			</th>
			<th>
				Column name
			</th>
			<th>
				Data
			</th>
			</tr>
			</thead>";

			// medallia data.
			$table = 'raw_data';
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='$table' AND table_schema='concentrix'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){
				$columnName = $row['column_name'];

				$sqlx = "SELECT external_survey_id FROM $table WHERE $columnName LIKE '%{$search}%' LIMIT 25";
				if(!$resultx = $db->query($sqlx)){cl($sqlx);cl($db->error);}
				while($rowx = $resultx->fetch_assoc()){
					echo "<tr>";
					echo "<td>Medallia</td>";
					echo "<td>$columnName</td>";
					echo "<td><a href='?a=surveys&e={$rowx[external_survey_id]}'>{$rowx[external_survey_id]}</a></td></tr>";
				}
			}

			$table = 'prt058data';
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='$table' AND table_schema='concentrix'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){
				$columnName = $row['column_name'];
				$sqlx = "SELECT $columnName FROM $table WHERE $columnName LIKE '%{$search}%' LIMIT 25";
				if(!$resultx = $db->query($sqlx)){cl($sqlx);cl($db->error);}
				while($rowx = $resultx->fetch_assoc()){
					echo "<tr>";
					echo "<td>prt058</td>";
					echo "<td>$columnName</td>";
					echo "<td><a href='?a=surveys&e={$rowx[external_survey_id]}'>{$rowx[$columnName]}</a></td></tr>";
				}
			}

			$table = 'prt060data';
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='$table' AND table_schema='concentrix'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){
				$columnName = $row['column_name'];
				$sqlx = "SELECT $columnName FROM $table WHERE $columnName LIKE '%{$search}%' LIMIT 25";
				if(!$resultx = $db->query($sqlx)){cl($sqlx);cl($db->error);}
				while($rowx = $resultx->fetch_assoc()){
					echo "<tr>";
					echo "<td>prt058</td>";
					echo "<td>$columnName</td>";
					echo "<td><a href='?a=surveys&e={$rowx[external_survey_id]}'>{$rowx[$columnName]}</a></td></tr>";
				}
			}

			$table = 'prt073data';
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='$table' AND table_schema='concentrix'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){
				$columnName = $row['column_name'];
				$sqlx = "SELECT $columnName FROM $table WHERE $columnName LIKE '%{$search}%' LIMIT 25";
				if(!$resultx = $db->query($sqlx)){cl($sqlx);cl($db->error);}
				while($rowx = $resultx->fetch_assoc()){
					echo "<tr>";
					echo "<td>prt058</td>";
					echo "<td>$columnName</td>";
					echo "<td><a href='?a=surveys&e={$rowx[external_survey_id]}'>{$rowx[$columnName]}</a></td></tr>";
				}
			}

			$table = 'prt085data';
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='$table' AND table_schema='concentrix'";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){
				$columnName = $row['column_name'];
				$sqlx = "SELECT $columnName FROM $table WHERE $columnName LIKE '%{$search}%' LIMIT 25";
				if(!$resultx = $db->query($sqlx)){cl($sqlx);cl($db->error);}
				while($rowx = $resultx->fetch_assoc()){
					echo "<tr>";
					echo "<td>prt058</td>";
					echo "<td>$columnName</td>";
					echo "<td><a href='?a=surveys&e={$rowx[external_survey_id]}'>{$rowx[$columnName]}</a></td></tr>";
				}
			}
			echo "</table>";
		}
	}
