<?php

/*

	Black Gold Backend
	jupiter@isolate.world
	2018

*/

session_start();
include "../../config.php";
include "$path../../../../zm-core-db.php";
$ddb = 'concentrix'; db_connect();
include "$path../../../../zm-core-login.php";
include "$path../../../../zm-core-functions.php";

header('Content-Type: text/plain');

$mainAction = $_GET['action'];
$info = $_GET['b'];
$returnText = '';

$rcvdUsername = $_GET['username'];
$rcvdPassword = $_GET['password'];

$blackGoldUid = $_SESSION['uid'];

if ($mainAction == 'logIn') {
	$blackGoldUid = validatepassword($rcvdUsername, $rcvdPassword);
	if ($blackGoldUid) {
		$returnText .= "{";
		addReturn("loggedIn", true);
		addReturn("uid", $blackGoldUid);
		addReturn("userName", getusername($blackGoldUid));
		$returnText = rtrim($returnText, ", ");
		$returnText .= "\n}\n";
		$_SESSION['userName'] = getusername($blackGoldUid);
		$_SESSION['uid'] = $blackGoldUid;
		$_SESSION['logged_in'] = true;
	}
	else {
		$returnText .= "{";
		addReturn("loggedIn", false);
		$returnText = rtrim($returnText, ", ");
		$returnText .= "\n}\n";
		$_SESSION['userName'] = '';
		$_SESSION['uid'] = '';
		$_SESSION['logged_in'] = false;
	}
}
else if ($mainAction == 'loggedIn') {
	if ($_SESSION['logged_in']) {
		$returnText .= "{";
		addReturn("loggedIn", true);
		addReturn("uid", $blackGoldUid);
		addReturn("userName", getusername($blackGoldUid));
		$returnText = rtrim($returnText, ", ");
		$returnText .= "\n}\n";
	}
	else {
		$returnText .= "{";
		addReturn("loggedIn", false);
		$returnText = rtrim($returnText, ", ");
		$returnText .= "\n}\n";

	}
}
else if ($mainAction == 'logOut') {
	$blackGoldUid = '';
	$returnText .= "{";
	addReturn("loggedIn", false);
	$returnText .= "\n}\n";
	$_SESSION['userName'] = '';
	$_SESSION['uid'] = '';
	$_SESSION['logged_in'] = false;
}

// Are we logged in
// not actually checking yet.........

if ($mainAction == 'getUsers') {
	$sql = "SELECT users.user_id, users.user_name, users.user_new, users.user_admin, users.user_showphotos, users.user_manager, users.user_ntid,
	teams.team_name, teams.team_shortname, users.user_active FROM users INNER JOIN teams ON users.user_preferred_team = teams.id ORDER BY users.user_name ";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$returnText = "[\n";
	while($row=$result->fetch_assoc()) {
		$returnText .= "\t{";
		foreach ($row as $key => $value) {
			addReturn($key, $value);
		}
		$returnText = rtrim($returnText, ", ");
		$returnText .= "\n\t}\n";
		$returnText .= ",";

	}
	$returnText = rtrim($returnText, ",");
	$returnText .= "]";
}
else if ($mainAction == 'getTeamName') {
	echo g("teams", "team_name", $info);
}
else if ($mainAction == 'isAdmin') {
	if ($blackGoldUid) {
		echo gg("users", "user_admin", "user_id", $blackGoldUid);
	}
	else {
		echo "0";
	}
}
else if ($mainAction == 'toggleUser') {
	if ($blackGoldUid) {
		echo $_GET;
		if ($info) {
			$current = sqr("SELECT user_active FROM users where user_name = '$info' LIMIT 1");
			if ($current == 0) {
				sq("UPDATE users SET user_active = 1 WHERE user_name = '$info' LIMIT 1");
				echo '1';
			}
			else {
				sq("UPDATE users SET user_active = 0 WHERE user_name = '$info' LIMIT 1");
				echo '0';
			}
		}
	}
}
else if ($mainAction == 'addUser') {
	if ($blackGoldUid) {
		if ($info) {
			sq("INSERT into users (user_name, user_new) VALUES('$info', 1)");
		}
	}
}


echo $returnText;

function addReturn($key, $value) {
	global $returnText;
	$addComma = false;
	if (!$value) { $value = false; }
	$returnText .= "\n\t\t\"$key\": ";
	if (is_bool($value)) { if ($value) { $returnText .= "true"; } else { $returnText .= "false"; } }
	else { $returnText .= "\"$value\""; }
	$returnText .= ', ';
}

// Get database data
function g ($tablename, $columnname, $tmpid) {
	global $db;
	$sql = "SELECT $columnname FROM $tablename WHERE id = '$tmpid' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[$columnname];
}

// Get database data
function gg ($tablename, $columnname, $idcolumnname, $tmpid) {
	global $db;
	$sql = "SELECT $columnname FROM $tablename WHERE $idcolumnname = '$tmpid' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[$columnname];
}

function sq($q){global $db;if(!$s=$db->query($q)){echo $q;echo $db->error;}}
function sqr($q){global $db;if(!$s=$db->query($q)){cl($q);cl($db->error);}return $s->fetch_assoc();}


 ?>
