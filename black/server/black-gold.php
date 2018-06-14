<?php

/*

	Black Gold Backend
	jupiter@isolate.world
	2018

*/

session_start();
header('Content-Type: text/plain');

$blackGoldUid = $_SESSION['user_id'];

// Are we logged in
if ($blackGoldUid) {
	echo "true";
}
else {
	echo "blackGold: not logged in";
}













function exceldate_to_unixedate($exceldate){ return ($exceldate - 25569) * 86400; }
function unixdate_to_exceldate($unixdate){ return 25569 + ($unixdate / 86400); }

 ?>
