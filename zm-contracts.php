<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
	if (isadmin()) {
		echo "<a href='?a=contracts&b=add_contract'>Add contract</a>.<br><br>";
		if ($b=='add_contract'){
			if ($_GET[contract_name]){
				$sql = "INSERT INTO contracts (contract_name, contract_admin_user_id, contract_region) VALUES('{$_GET[contract_name]}','$uid','{$_GET[contract_region]}')";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			else {
				echo "<form>
				Contract name: <input name='contract_name'><br>
				Region: <select name='contract_region'>";
				$sql = "SELECT region_id,region_name FROM regions";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){
						echo "<option value={$row[region_id]}>{$row[region_name]}</option>";
				}
				echo "</select><br>
				<input name=a value=contracts type=hidden><input name=b value=add_contract type=hidden><input type=submit></form><br>";
			}
		}
	}
	$sql = "SELECT * FROM contracts ORDER by contract_name ASC";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){
		echo "<div class='tbl_contract'><a href='?a=contracts&b=show_contract&contract={$row[contract_id]}'>".$row[contract_name]."</a></div>";
	}
}
