<?php

define('CHARSET', 'ISO-8859-1');
define('REPLACE_FLAGS', ENT_COMPAT | ENT_XHTML);
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

global $app_action;

$vmselector = [
	2 => "Total_AHT_secs",
	8 => "Phone_SLperc",
	13 => "Email_SLperc",
	12 => "Phone_Answered",
	14 => "Email_Worked",
	21 => "Phone_Transfer_Rate",
	22 => "Email_Transfer_Rate",
	17 => "phone_rcr",
	23 => "email_rcr",
	24 => "rcr",
];
$prtselector = [
	2 => "Total_AHT_secs",
	8 => "Phone_SLperc",
	13 => "Email_SLperc",
	12 => "Phone_Answered",
	14 => "Email_Worked",
	21 => "Phone_Transfer_Rate",
	22 => "Email_Transfer_Rate",
	17 => "phone_rcr",
	23 => "email_rcr",
	24 => "rcr",
];


$timeout_duration = 432000;
set_time_limit(600);
session_start();
if (isset($_SESSION[‘LAST_ACTIVITY’]) && ($time - $_SESSION[‘LAST_ACTIVITY’]) > $timeout_duration) {
	session_unset();
	session_destroy();
	session_start();
}

// If a main action has been specified.
if (isset($_GET['a'])) {
	$app_action = $_GET['a'];
}

// If a main post action has been specified, override the main action.
if (isset($_POST['a'])) {
	$app_action = $_POST['a'];
}

$survey_threshold = 1;
$today = date("Y-m-d");

$_SESSION[‘LAST_ACTIVITY’] = $time;

$month = [
	 '1' => 'January',
	'01' => 'January',
	 '2' => 'February',
	'02' => 'February',
	 '3' => 'March',
	'03' => 'March',
	 '4' => 'April',
	'04' => 'April',
	 '5' => 'May',
	'05' => 'May',
	 '6' => 'June',
	'06' => 'June',
	 '7' => 'July',
	'07' => 'July',
	 '8' => 'August',
	'08' => 'August',
	 '9' => 'September',
	'09' => 'September',
	'10' => 'October',
	'11' => 'November',
	'12' => 'December'
];

$monthy = [
	 '1' => 'JAN',
	'01' => 'JAN',
	 '2' => 'FEB',
	'02' => 'FEB',
	 '3' => 'MAR',
	'03' => 'MAR',
	 '4' => 'APR',
	'04' => 'APR',
	 '5' => 'MAY',
	'05' => 'MAY',
	 '6' => 'JUN',
	'06' => 'JUN',
	 '7' => 'JUL',
	'07' => 'JUL',
	 '8' => 'AUG',
	'08' => 'AUG',
	 '9' => 'SEP',
	'09' => 'SEP',
	'10' => 'OCT',
	'11' => 'NOV',
	'12' => 'DEC'
];

$days = [
	'1' => 'Monday',
	'2' => 'Tuesday',
	'3' => 'Wednesday',
	'4' => 'Thursday',
	'5' => 'Friday',
	'6' => 'Saturday',
	'0' => 'Sunday'
];

error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

date_default_timezone_set('Europe/London');

$uid = $_SESSION[user_id];

include "$path../../zm-core-db.php"; $ddb = 'concentrix'; db_connect();
include "$path../../zm-core-login.php";
include "$path../../zm-core-functions.php";


// Load team definitions.
global $team_id_def;

$sql = "SELECT team_name,id FROM teams";
if (!$result = $db->query($sql)) { cl($sql); cl($db->error); }
$teams = 0;
while ($row = $result->fetch_assoc()) {
	$teams++;
	$team_id = $row[id];
	$sqla = "SELECT * FROM team_data_definitions WHERE team_id = $team_id";
	if (!$resulta = $db->query($sqla)) {
		cl($sqla);
		cl($db->error);
	}

	$definitions = 0;
	$team_id_def[$team_id] = '';

	while ($rowa = $resulta->fetch_assoc()) {
		if ($definitions) {
			$team_id_def[$team_id].=' OR ';
		}
		$team_id_def[$team_id] .= $rowa[raw_data_column] . "='{$rowa[raw_data_data]}'";
		$definitions++;
	}
	if ($team_id_def[$team_id]) {
		$team_id_def[$team_id]=" AND (".$team_id_def[$team_id] . ")";
	}
}

function html($string) {
	return htmlspecialchars($string, REPLACE_FLAGS, CHARSET);
}

function showMainPage() {
	echo "<script>location.href='?';</script>";
}

function getTeamDefinitions($team_id) {
	global $db;
	$sql = "SELECT * FROM team_data_definitions WHERE team_id = $team_id";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$definitions = 0;
	while($row=$result->fetch_assoc()){
		if ($definitions){$teamdefinition.=' OR ';}
		$teamdefinition .= $row['raw_data_column'] . "='".$row['raw_data_data']."'";
		$definitions++;
	}
	if ($teamdefinition){$teamdefinition=" AND (".$teamdefinition . ")";}
	return $teamdefinition;
}
function getTeamAhtDefinitions($team_id) {
	global $db;
	$sql = "SELECT * FROM team_aht_definitions WHERE team_id = $team_id";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$ahtdefinitions = 0;
	while($row=$result->fetch_assoc()){
		if ($ahtdefinitions){$ahtteamdefinition.=' OR ';}
		$ahtteamdefinition .= $row[ahtreport_data_column] . "='{$row[ahtreport_data_data]}'";
		$ahtdefinitions++;
	}
	if ($ahtteamdefinition){$ahtteamdefinition=" AND (".$ahtteamdefinition . ")";}
	return $ahtteamdefinition;
}

function setaddress($address) {
	echo "<script>window.history.pushState('moo', 'gold', '$address');</script>";
}

function sq($q, $silent = false){
	global $db;
	if(!$s = $db->query($q)) {
		if (!$silent) {
			echo "<b>Query:</b> $q<br><b>Error:</b>" . $db->error;
		}
	}
}
function sqr($q){global $db;if(!$s=$db->query($q)){cl($q);cl($db->error);}return $s->fetch_assoc();}

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

// Get metric symbol
function gms($metric_id){
	global $db;
	$sql = "SELECT metric_symbol FROM metrics WHERE metric_id = '$metric_id' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[metric_symbol];
}

// Get metric rounding
function gmr($metric_id){
	global $db;
	$sql = "SELECT metric_rounding FROM metrics WHERE metric_id = '$metric_id' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[metric_rounding];
}

function getlastday($year,$month) {
	$ldm=cal_days_in_month(CAL_GREGORIAN,$month,$year);
	return $ldm;
}

function getlatest($teamdeff){
	global $startdate,$enddate, $db;
	$valuestartdate_year = substr($startdate,0,4);
	$valuestartdate_month = substr($startdate,5,2);
	$valuestartdate_day = substr($startdate,8,2);
	$valueenddate_year = substr($enddate,0,4);
	$valueenddate_month = substr($enddate,5,2);
	$valueenddate_day = substr($enddate,8,2);
	$valuestartdate_excel = unixdate_to_exceldate(mktime(0,0,0,$valuestartdate_month,$valuestartdate_day,$valuestartdate_year));
	$valueenddate_excel = unixdate_to_exceldate(mktime(23,59,59,$valueenddate_month,$valueenddate_day,$valueenddate_year));
	$valuesqldater = " WHERE raw_data.Teammate_Contact_Date > $valuestartdate_excel";
	$valuesqldater .= " AND raw_data.Teammate_Contact_Date < $valueenddate_excel";
	$sql = "SELECT Response_Date FROM raw_data $valuesqldater $teamdeff ORDER by Response_Date DESC LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[Response_Date]==''){return 'n/a';}
	$lasthour = date("G:i T", exceldate_to_unixedate($row[Response_Date]));
	$dateString = date("Y-m-d", exceldate_to_unixedate($row[Response_Date]));
	if (date("Y - m - d",exceldate_to_unixedate($row[Response_Date])) == $today) {
		return 'today';
	}
	else if (date("Y - m - d",exceldate_to_unixedate($row[Response_Date])) == yesterday()) {
		//return 'yesterday at ' . $lasthour;
		return 'yesterday';
	}
	else {
		$now = time();
		$dateDiff = $now - strtotime($dateString);
		$days = round($dateDiff  / (60 * 60 * 24));
		if ($days > 1) { return $days . " days ago"; }
		if ($days == 1) {
			return 'yesterday';
			//return "yesterday at " . $lasthour;
		}
		else {
			$hours = round($dateDiff / (60 * 60));
			return $hours . " hours ago";
			//return "today";
		}
	}
	return;
	//return date("Y - m - d",exceldate_to_unixedate($row[Response_Date]));
}

function yesterday() {
	return date("Y - m - d", time() - 60 * 60 * 24);
}


function exceldate_to_unixedate ($exceldate) {
	return ($exceldate - 25569) * 86400;
}
function unixdate_to_exceldate ($unixdate) {
	return 25569 + ($unixdate / 86400);
}

function countsurveys($username){
	global $db, $teamdefinition,$sqldater;
	$surveys=0;
	$sql = "SELECT COUNT(*) as id FROM raw_data  $sqldater $teamdefinition AND teammate_nt_id='$username' ";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row = $result->fetch_assoc();
	$surveys = $row[id];
	return $surveys;
}
function processreport($filename) {
	global $db;
	echo " Processing Medallia report... this can take a few minutes...";
	require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$rownumber = 0;
	foreach ($objWorksheet->getRowIterator() as $row) {
		$columnnumber = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);
		foreach ($cellIterator as $cell) {
			$columnnumber++;
			if ($rownumber==2) {
				$column[$columnnumber] = str_replace(" ","_",$cell->getValue());
				$column[$columnnumber] = substr($column[$columnnumber],0,30);
				$column[$columnnumber] = str_replace("(","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace(")","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace("/","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace("?","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace("'","",$column[$columnnumber]);
				$column[$columnnumber] = str_replace(",","_",$column[$columnnumber]);
				$column[$columnnumber] = str_replace("-","_",$column[$columnnumber]);
			}
			elseif($rownumber>2) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
		}
		$rownumber++;
	}

	// Add the columns to the database table.
	for($a = 1; $a<=$columnnumber; $a++){
		$sql = "alter table raw_data add {$column[$a]} text";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	}

	// Add the data.
	for ($a = 1; $a<$rownumber; $a++){

		// Try to find out if there data already exists.
		$sql = 'INSERT INTO raw_data ';
		$sql .= ' (';
		for ($b = 1; $b <= $columnnumber; $b++) {
			$sql .=	"{$column[$b]},";
		}
		$sql = substr($sql,0, -1);
		$sql .= ") VALUES(";
		for ($b = 1; $b <= $columnnumber; $b++) {
			$data[$a][$b] = $db->real_escape_string($data[$a][$b]);
			$sql .=	"'{$data[$a][$b]}',";
		}
		$sql = substr($sql, 0, -1);
		$sql .= ')';
		if(!$result=$db->query($sql)){
			if (substr($db->error,0,15)!='Duplicate entry') {
				cl($sql);cl($db->error);
			}
		}
	}
	setsetting('medalliadata',date("Y-m-d H:i:s"));
}

function processahtreport($filename) {
	global $db, $path;
	echo " Processing prt060 report... ";
	require_once  $path . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	for($x=0;$x<$objPHPExcel->getSheetCount();$x++){
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($x);
		unset($column);
		unset($columnnumber);
		unset($rownumber);
		unset($data);
		$rownumber = 0;
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnnumber = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);
			foreach ($cellIterator as $cell) {
				$columnnumber++;
				if ($rownumber==6) {
					$column[$columnnumber] = str_replace(" ","_",$cell->getValue());
					$column[$columnnumber] = substr($column[$columnnumber],0,30);
					$column[$columnnumber] = str_replace("(","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(")","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("/","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("?","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("'","",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(",","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(".","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("-","_",$column[$columnnumber]);
					if ($column[$columnnumber]=='Month'){$monthcolumn = $columnnumber;}
					if ($column[$columnnumber]=='NTID'){$ntidcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Name'){$queuenamecolumn = $columnnumber;}
					if ($column[$columnnumber]=='Routing Caller Type Group'){$rctgcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Routing Caller Type'){$rct = $columnnumber;}
				}
				elseif($rownumber>6) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
			}
			$rownumber++;
		}


		// Add the columns to the database table.
/*		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table prt060data add {$column[$a]} text";	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}	}
*/

		// Add the data.
		for ($a = 7; $a<$rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id
			FROM
				prt060data
			WHERE
				month = '{$data[$a][$monthcolumn]}'
			AND
				ntid = '{$data[$a][$ntidcolumn]}'
			AND
				queue_name = '{$data[$a][$queuenamecolumn]}'
			AND
				routing_caller_type_group = '{$data[$a][$rctgcolumn]}'
			AND
				routing_caller_type = '{$data[$a][$rctcolumn]}'
			LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM prt060data WHERE id = '{$row[id]}' LIMIT 1";
				echo $sql . "<br>";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			$sql = "INSERT INTO prt060data ";
			$sql .= "(";
			for ($b=1;$b<=$columnnumber;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
			for ($b=1;$b<=$columnnumber;$b++){$data[$a][$b]=$db->real_escape_string($data[$a][$b]);$sql.="'{$data[$a][$b]}',";}$sql=substr($sql,0,-1);$sql.=')';
 			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	echo "Done.";
	setsetting('prt060pdata',date("Y-m-d H:i:s"));
}

function processGeneralPrtReport($reportFile) {
	global $db, $path;
	$reportType = substr($reportFile, 8, 6);
	$reportType = strtolower($reportType);
	$reportFrom = substr($reportFile, -13, 8);
	// check if last char is ), then split the string and do the -13 8 substr on the left.

	if (substr($reportFrom, -1) == ')') {
		list($reportFrom, $bla) = explode(" ", $reportFile);
		$reportFrom = substr($reportFrom, -8, 8);
	}

	echo "<hr></hgr>Processing <b>$reportType</b> report '$reportFile' from $reportFrom... ";

	// Load the required Excel stuff.
	require_once dirname(__FILE__) . "/{$path}Classes/PHPExcel/IOFactory.php";
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($reportFile);

	echo "//";

	// Loop through all sheets in the file.
	for($sheetNumber = 0; $sheetNumber < $objPHPExcel->getSheetCount(); $sheetNumber++){

		# Set the current sheet.
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($sheetNumber);

		# Delete the values in the variables used.
		unset($column); $rowNumber[$sheetNumber] = 0;

		// Loop through all the rows of the sheet.
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnNumber[$sheetNumber] = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);

			// Loop through all the columns in the sheet.
			foreach ($cellIterator as $cell) {
				$columnNumber[$sheetNumber]++;

				// In a PRT report, row 6 is the table header row.
				if ($rowNumber[$sheetNumber] == 6) {
					$column[$columnNumber[$sheetNumber]] = $cell->getValue();
					$column[$columnNumber[$sheetNumber]] = fixColumnName($column[$columnNumber[$sheetNumber]]);
				}
				elseif ($rowNumber > 6) {
					$data[$sheetNumber][$rowNumber[$sheetNumber]][$columnNumber[$sheetNumber]] = $cell->getValue();
				}
			}
			$rowNumber[$sheetNumber]++;
		}

		$settingone[$sheetNumber] = $data[$sheetNumber][3][1];
		$settingtwo[$sheetNumber] = $data[$sheetNumber][4][1];
	}


	for($a = 1; $a <= $columnNumber[0]; $a++){
		if ($column[$a] == 'Month') {
			$monthreport = $a;
		}
		else if ($column[$a] == 'Queue_Skillset') {
			$skillset = $a;
		}
		else if ($column[$a] == 'Routing_Caller_Type_Group') {
			$rctg = $a;
		}
		else if ($column[$a] == 'Routing_Caller_Type') {
			$rct = $a;
		}
	}

	$table_deleted = false;
	// Add the data.

	for ($sheetLoop = 0; $sheetLoop <= $sheetNumber; $sheetLoop++) {
		for ($a = 7; $a<$rowNumber[$sheetLoop]; $a++) {
			$dataFilter = "";
			for ($b = 1; $b <= $columnNumber[$sheetLoop]; $b++) { $dataFilter .= "{$column[$b]} = '" . $data[$sheetLoop][$a][$b] . "' AND "; }
			$dataFilter = substr($dataFilter, 0, -5);
			$query = "SELECT id FROM {$reportType}data WHERE ";
			$query .= "queue_skillset = '{$data[$sheetLoop][$a][$skillset]}'";
			$query .= " AND month='{$data[$sheetLoop][$a][$monthreport]}'";
			$query .= " AND routing_caller_type_group='{$data[$sheetLoop][$a][$rctg]}'";
			$query .= " AND routing_caller_type='{$data[$sheetLoop][$a][$rct]}'";
			$query .= " LIMIT 1";
			$tmpid = db_get($query)['id'];
			if ($tmpid != '') {
				db_set("DELETE FROM {$reportType}data WHERE id='{$tmpid}' LIMIT 1");
			}
			$sql = "INSERT INTO ".$reportType."data (";
			for ($b=1;$b<=$columnNumber[$sheetLoop];$b++){
				$sql.="{$column[$b]},";
			}
			$sql = substr($sql,0,-1);
			$sql .= ") VALUES(";
			for ($b=1; $b <= $columnNumber[$sheetLoop]; $b++) {
				$data[$sheetLoop][$a][$b] = $db->real_escape_string($data[$sheetLoop][$a][$b]);
				$sql .= "'{$data[$sheetLoop][$a][$b]}',";
			}
			$sql = substr($sql, 0, -1);

			$sql .= ')';

			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	echo "Done.";
	setsetting($reportType, date("Y-m-d H:i:s"));
	setsetting($reportType . "from", $reportFrom);
}

function fixColumnName($columnName) {
	// Limit to 64 characters due to mysql limitation.
	$columnName = substr($columnName, 0, 64);

	// Replace unsupported characters.
	$columnName = str_replace(" ", "_", $columnName);
	$columnName = str_replace("(", "_", $columnName);
	$columnName = str_replace(")", "_", $columnName);
	$columnName = str_replace("/", "_", $columnName);
	$columnName = str_replace("?", "_", $columnName);
	$columnName = str_replace("%", "_", $columnName);
	$columnName = str_replace(":", "_", $columnName);
	$columnName = str_replace("'", "",  $columnName);
	$columnName = str_replace(",", "_", $columnName);
	$columnName = str_replace(".", "_", $columnName);
	$columnName = str_replace("-", "_", $columnName);

	return $columnName;
}
/*function processprt073report($filename) {
	global $db;
	echo " Processing prt073 report...";
	require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	for($x=0;$x<$objPHPExcel->getSheetCount();$x++){
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($x);
		unset($column);
		unset($columnnumber);
		unset($rownumber);
		unset($data);
		$rownumber = 0;
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnnumber = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);
			foreach ($cellIterator as $cell) {
				$columnnumber++;
				if ($rownumber==6) {
					$column[$columnnumber] = str_replace(" ","_",$cell->getValue());
					$column[$columnnumber] = substr($column[$columnnumber],0,30);
					$column[$columnnumber] = str_replace("(","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(")","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("/","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("?","_",$column[$columnnumber]);
          $column[$columnnumber] = str_replace("%","_",$column[$columnnumber]);
          $column[$columnnumber] = str_replace(":","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("'","",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(",","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(".","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("-","_",$column[$columnnumber]);
					if ($column[$columnnumber]=='Month'){$monthcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Skillset'){$ntidcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Name'){$queuenamecolumn = $columnnumber;}
				}
				elseif($rownumber>6) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
			}
			$rownumber++;
		}

		// Add the columns to the database table.
		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table prt073data add {$column[$a]} text";	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}	}

		// Add the data.
		for ($a = 7; $a<$rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id FROM prt073data WHERE month = '{$data[$a][$monthcolumn]}' AND queue_skillset = '{$data[$a][$ntidcolumn]}' AND queue_name = 'Total' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM prt073data WHERE id = '{$row[id]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			if ($data[$a][$queuenamecolumn] == 'Total') {
				$sql="INSERT INTO prt073data (";
				for ($b=1;$b<=$columnnumber;$b++){
					$sql.="{$column[$b]},";
				}
				$sql = substr($sql,0,-1);
				$sql .= ") VALUES(";
				for ($b=1; $b <= $columnnumber; $b++) {
          $data[$a][$b] = $db->real_escape_string($data[$a][$b]);
          $sql .= "'{$data[$a][$b]}',";
        }
        $sql = substr($sql,0,-1);
        $sql .= ')';
   			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
      }
		}
	}
	echo "Done.";
}
*/

function processprtreport($filename) {
	global $db;
	echo " Processing PRT report...";
	require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filename);
	for($x=0;$x<$objPHPExcel->getSheetCount();$x++){
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($x);
		unset($column);
		unset($columnnumber);
		unset($rownumber);
		unset($data);
		$rownumber = 0;
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnnumber = 0; $cellIterator = $row->getCellIterator(); $cellIterator->setIterateOnlyExistingCells(FALSE);
			foreach ($cellIterator as $cell) {
				$columnnumber++;
				if ($rownumber==6) {
					$column[$columnnumber] = str_replace(" ", "_", $cell->getValue());
					$column[$columnnumber] = substr($column[$columnnumber], 0, 30);
					$column[$columnnumber] = str_replace("(", "_", $column[$columnnumber]);
					$column[$columnnumber] = str_replace(")", "_", $column[$columnnumber]);
					$column[$columnnumber] = str_replace("/", "_", $column[$columnnumber]);
					$column[$columnnumber] = str_replace("?", "_", $column[$columnnumber]);
					$column[$columnnumber] = str_replace("'", "",  $column[$columnnumber]);
					$column[$columnnumber] = str_replace(",", "_", $column[$columnnumber]);
					$column[$columnnumber] = str_replace(".", "_", $column[$columnnumber]);
					$column[$columnnumber] = str_replace("-", "_", $column[$columnnumber]);
					if ($column[$columnnumber] == 'Date') {
						$monthcolumn = $columnnumber;
					}
					if ($column[$columnnumber] == 'NTID') {
						$ntidcolumn = $columnnumber;
					}
					if ($column[$columnnumber] == 'Queue_Name') {
						$queuenamecolumn = $columnnumber;
					}
				}
				elseif ($rownumber > 6) {
					$data[$rownumber][$columnnumber] = $cell->getValue();
				}
			}
			$rownumber++;
		}

		// Add the columns to the database table.
		for($a = 1; $a <= $columnnumber; $a++){
			$sql = "alter table prt058data add {$column[$a]} text";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}

		// Add the data.
		for ($a = 7; $a < $rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id FROM prt058data WHERE date = '{$data[$a][$monthcolumn]}' AND ntid = '{$data[$a][$ntidcolumn]}' AND queue_name = '{$data[$a][$queuenamecolumn]}' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM prt058data WHERE id = '{$row[id]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			$sql = 'INSERT INTO prt058data ';
			$sql .= '(';
			for ($b = 1; $b <= $columnnumber; $b++){
				$sql .= "{$column[$b]},";
			}
			$sql = substr($sql, 0, -1);
			$sql .= ") VALUES(";
			for ($b = 1; $b <= $columnnumber; $b++) {
				$data[$a][$b] = $db->real_escape_string($data[$a][$b]);
				$sql .= "'{$data[$a][$b]}',";
			}
			$sql = substr($sql, 0, -1);
			$sql .= ')';
 			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	echo "Done.";
}
function processvmreport($filename) {
	global $db; echo " Processing VM055p report..."; require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	$objReader = new PHPExcel_Reader_Excel2007(); $objReader->setReadDataOnly(true); $objPHPExcel = $objReader->load($filename);
	for($x=0;$x<$objPHPExcel->getSheetCount();$x++){
		$objWorksheet = $objPHPExcel->setActiveSheetIndex($x);
		$teamo = $objWorksheet->getTitle();
		unset($column); unset($columnnumber); unset($rownumber); unset($data);
		$months = 0; $monthdata = []; $rownumber = 0;	 $inverted_col = 0; $inverted_row = 0;
		$column[0] = "team";
		$column[1] = "month";

		$nom = 0; // Number of months.
		$rownumber = 0;
		foreach ($objWorksheet->getRowIterator() as $row) {
			$columnnumber = 0; $cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(FALSE);
			foreach ($cellIterator as $cell) {
				$columnnumber++;
				$rawdata[$columnnumber][$rownumber] = $cell->getValue();
			}
			$rownumber++;
		}
		$savedskillgroup = "";
		$colos = 1;
		$startcol = 0;
		$endcol = 0;
		for ($a = 9; $a < $rownumber; $a++){
			if ($rawdata[1][$a]!=''){$savedskillgroup = $rawdata[1][$a];}
			if ($rawdata[1][$a]==''){$rawdata[1][$a]=$savedskillgroup;}
			if ($rawdata[1][$a] == 'Total'){
				if ($startcol == 0){$startcol = $a;}
				$endcol = $a;
				$colos++;
				$column[$colos]=$rawdata[3][$a];
				$column[$colos] = str_replace(" ","_",$column[$colos]);
				$column[$colos] = substr($column[$colos],0,30);
				$column[$colos] = str_replace("(","_",$column[$colos]);
				$column[$colos] = str_replace(")","_",$column[$colos]);
				$column[$colos] = str_replace("/","_",$column[$colos]);
				$column[$colos] = str_replace("?","_",$column[$colos]);
				$column[$colos] = str_replace("'","",$column[$colos]);
				$column[$colos] = str_replace(",","_",$column[$colos]);
				$column[$colos] = str_replace("-","_",$column[$colos]);
				$column[$colos] = str_replace(":","_",$column[$colos]);
				$column[$colos] = str_replace("%","perc",$column[$colos]);
			}
		}

		// Add the columns to the database table.
		for($a = 0; $a<=$colos; $a++){
			$sql = "alter table vm055_data add {$column[$a]} text";
			if(!$result=$db->query($sql)){
				cl($sql);cl($db->error);
			}
		}
		for ($a = 5;$a <=$columnnumber; $a++){
			if (($rawdata[$a][5]!='') && ($rawdata[$a][5]!='Total')){
				// find the data column for this month
				for($b=$a;$b<$a+7;$b++){if ($datacolumn[$teamo][$rawdata[$a][5]]==''){if($rawdata[$b][6]=='Total'){$datacolumn[$teamo][$rawdata[$a][5]]=$b;}}}

				// Try to find out if the data already exists.
				$sql = "SELECT id FROM vm055_data WHERE month = '{$rawdata[$a][5]}' AND team = '$teamo' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				$row=$result->fetch_assoc();
				if ($row[id]>0){
					$sql = "DELETE FROM vm055_data WHERE id = '{$row[id]}' LIMIT 1"; if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}
				$sql="INSERT INTO vm055_data ";
				$sql .= "(";
				for ($b=0;$b<=$colos;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
				$sql .= "'$teamo','{$rawdata[$a][5]}',";
				for ($c = $startcol;$c <= $endcol; $c++) { $ddb = $db->real_escape_string($rawdata[$datacolumn[$teamo][$rawdata[$a][5]]][$c]);$sql.="'$ddb',";}
				$sql=substr($sql,0,-1);
				$sql.=")";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
		}
	}
	setsetting('vm055data',date("Y-m-d H:i:s"));
	showMainPage();
}

function addleftchoice($name) {
 	global $app_action;
 	echo "<div ";
	echo "onclick=\"location.href='?a={$name}';\" ";
	echo "class='bar-actions-action";
	if ($name == $app_action) { echo '-selected'; }
	echo "' title='" . ucfirst($name) . "'>";
	$icon = geticon($name);
	echo "<span><i class='fa fa-$icon'></i></span>";
	echo "<div class='bar-actions-action-name'>";
	echo ucfirst(str_replace("_", " ", $name));
	echo "</div>";
 	echo "</div>";
}

function dd($datestring){
	global $today;
	$datepart = substr($datestring,0,10);
	$timepart = substr($datestring,10,6);
	if ($datepart == $today) { $datepart = "<b>today</b>";}
	else { $datepart = "<i>$datepart</i>"; }
	return $datepart . " @ ". $timepart;
}
function getsetting($setting) {
	global $db;
	$sql = "SELECT settingvalue FROM systemsettings WHERE settingname = '$setting' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[settingvalue];
}
function setsetting($setting,$value) {
	global $db;
	$sql = "UPDATE systemsettings SET settingvalue = '$value' WHERE settingname = '$setting' LIMIT 1";
	if(!$result=$db->query($sql)){
		cl($sql);cl($db->error);
		$sql = "INSERT INTO systemsettings (settingname, settingvalue) VALUES('$setting', '$value')";
		if(!$result=$db->query($sql)){
			cl($sql);cl($db->error);
		}
	}
}
function isadmin() {
  global $db, $uid;
  $sql = "SELECT user_admin FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row[user_admin];
}
function gettheme(){
	global $db, $uid;
	if (!$_SESSION['logged_in']){return;}
	$sql = "SELECT user_theme FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row[user_theme];
}
function ismgr() {
  global $db, $uid;
  $sql = "SELECT user_manager FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row[user_manager];
}
function getmanager($username){
	global $db;
	$sql = "SELECT team_leader_name FROM raw_data WHERE teammate_nt_id = '{$username}' ORDER by response_date DESC LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  $row=$result->fetch_assoc();
	return $row[team_leader_name];
}


// Let's try to guess the team of a user. Argument: NT ID. Returns the team ID if successful, -1 if not.
function guessteam($username){
	global $db;

	//echo $username . "<br>";
	$sql = "SELECT team_id FROM users_teams WHERE teammate_nt_id = '$username' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[team_id]=="")	{
		$sql = "SELECT queue_source_name, count(queue_source_name) as qc FROM raw_data WHERE teammate_nt_id = '$username' GROUP by queue_source_name ORDER by qc DESC LIMIT 1";

		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		$sql = "SELECT team_id FROM team_data_definitions WHERE raw_data_column = 'Queue_Source_Name' AND raw_data_data = '{$row['queue_source_name']}' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();

		$sql = "INSERT INTO users_teams (team_id, teammate_nt_id) VALUES('{$row[team_id]}','$username')";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	}
	return $row['team_id'];
}


function getteamname($team_id){
	global $db;
	$sql = "SELECT team_name FROM teams WHERE id = '$team_id'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[team_name];
}

function targetcolor($value, $contract, $metric, $team, $date, $submetric) {
	if (($value == '--') || ($metric == '')) {
		return '';
	}
	if ($team){
		global $db, $bad_color;

		// There should only be one match.
		$sub=" AND submetric = '$submetric'";

		$sql = "SELECT
				target_color,
				target_textcolor,
				target_value_low,
				target_value_high
			FROM
				targets
			WHERE
					(active = 1)
				AND
					(target_value_low <= '{$value}')
				AND
					(target_value_high >= '{$value}-0.00001')
				AND
					target_metric_id = '{$metric}'
				AND
					target_team_id = '{$team}'
				AND
					target_start_date <= '{$date}'
				AND
					target_stop_date >= '{$date}' {$sub}
			LIMIT 1";

		if(!$result = $db->query($sql)) {
			cl($sql);
			cl($db->error);
		}
		$row = $result->fetch_assoc();
		if ($row[target_color] == '') {
			$date = "0000-00-00 00:00:00";
			$sql = "SELECT
					target_color,
					target_textcolor,
					target_value_low,
					target_value_high
				FROM
					targets
				WHERE
						active = 1
					AND  target_value_low <= $value
					AND 	target_value_high >= $value-0.0000001
					AND target_metric_id='$metric'
					AND target_team_id='$team'
					AND target_start_date <= '$date'
					AND target_stop_date >= '$date' $sub
				LIMIT 1";
			if(!$result = $db->query($sql)) {
				cl($sql);
				cl($db->error);
			}
			$row = $result->fetch_assoc();
		}
		if (($metric == 6) or ($metric == 17)) {
			$targetdiff = $row[target_value_low] - $value;
		}
		else if ($metric == 2) {
			$targetdiff = ($value/$row[target_value_low] - 1.0) * 100;
		}
		else {
			$targetdiff = $row[target_value_high] - $value;
		}
		$targetdiff = $targetdiff / 100;

		if (($metric == 6) or ($metric == 17)) {
			if (($row[target_color] == $bad_color) && ($targetdiff > -0.05)) {
				return array("fdae61","dd8e41");
			}
		}
		else if ($metric == 2) {
			if (($row[target_color] == $bad_color) && ($targetdiff < 0.05)) {
				return array("fdae61","dd8e41");
			}
		}
		else {
			if (($row[target_color] == $bad_color) && ($targetdiff < 0.05)) {
				return array("fdae61","dd8e41");
			}
		}
		return array($row[target_color],$row[target_textcolor]);
	}
	else {return '';}
}

function gettarget($contract,$metric,$team,$date,$highorlow,$submetric){
	if($team){
		global $db, $bad_color, $good_color;

		if ($submetric){
			$submetric = "AND submetric='$submetric'";
		}
		else {
			$submetric = "AND submetric=''";
		}
		$tvhol = 'target_value_' . $highorlow;

		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '$bad_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric LIMIT 1";

		if(!$result=$db->query($sql)) {
			cl($sql);
			cl($db->error);
		}

		$row = $result->fetch_assoc();

		if ($row[$tvhol] == ''){
			$sql = "SELECT $tvhol FROM targets WHERE target_color = '$bad_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_start_date = '0000-00-00 00:00:00' AND target_stop_date = '0000-00-00 00:00:00' AND target_team_id='$team' $submetric LIMIT 1";
			if(!$result = $db->query($sql)) {
				cl($sql);
				cl($db->error);
			}
			$row = $result->fetch_assoc();
			if ($row[$tvhol] == ''){
				return 0;
			}
		}
		return $row[$tvhol];
	}
	return 0;
}
function getgoodtarget($contract,$metric,$team,$date,$highorlow){
	if($team){
		global $db, $bad_color, $good_color;
		$submetric = "AND submetric=''";
		$tvhol = 'target_value_' . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '$good_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric ORDER by $tvhol DESC LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){
			$sql = "SELECT $tvhol FROM targets WHERE target_color = '$good_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_start_date = '0000-00-00 00:00:00' AND target_stop_date = '0000-00-00 00:00:00' AND target_team_id='$team' $submetric LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[$tvhol]==''){return 0;}
		}
		return $row[$tvhol];
	}
	return 0;
}
function getgreattarget($contract,$metric,$team,$date,$highorlow){
	if($team){
		global $db, $great_color;
		$submetric = "AND submetric=''";
		$tvhol = "target_value_$highorlow";// . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '$great_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric ORDER by $tvhol DESC LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		return $row[$tvhol];
	}
	return 0;
}
function getsubtarget($contract,$metric,$team,$date,$submetric){
	global $db, $good_color, $bad_color;
	$hol='low';
	if ($metric==2){$hol='high';}
	$tvhol = "target_value_$hol";
	$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '$good_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND submetric='$submetric' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
 	$row=$result->fetch_assoc();
	if ($row[$tvhol]==''){
		$sql = "SELECT $tvhol FROM targets WHERE target_color = '$good_color' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND submetric='$submetric' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	 	$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){ return 0; }
	}
	return $row[$tvhol];
}
function surveycount($optionalmonth, $teamdefinition){
	global $db,$sqldater, $currentyear, $currentmonth;
	$surveys=0;
  if ($optionalmonth == "previous month") {
    $tmpyear = $currentyear;
    $tmpmonth = $currentmonth-1;
    if ($tmpmonth < 1){$tmpmonth = 12; $tmpyear--; }
    $tmpdate = unixdate_to_exceldate(mktime(0,0,0,$tmpmonth,1,$tmpyear));
    $lastday = getlastday($tmpyear,$tmpmonth);
    $tmpdater = unixdate_to_exceldate(mktime(23,59,59,$tmpmonth,$lastday,$tmpyear));
    $tmpsqldater = "WHERE raw_data.Teammate_Contact_Date > $tmpdate AND raw_data.Teammate_Contact_Date < $tmpdater";
  }
  elseif ($optionalmonth == "two months ago") {
    $tmpyear = $currentyear;
    $tmpmonth = $currentmonth-1;
    if ($tmpmonth < 1){$tmpmonth = 12; $tmpyear--; }
    $tmpmonth = $tmpmonth-1;
    if ($tmpmonth < 1){$tmpmonth = 12; $tmpyear--; }
    $tmpdate = unixdate_to_exceldate(mktime(0,0,0,$tmpmonth,1,$tmpyear));
    $lastday = getlastday($tmpyear,$tmpmonth);
    $tmpdater = unixdate_to_exceldate(mktime(23,59,59,$tmpmonth,$lastday,$tmpyear));
    $tmpsqldater = "WHERE raw_data.Teammate_Contact_Date > $tmpdate raw_data.AND Teammate_Contact_Date < $tmpdater";
  }
  if ($tmpsqldater=='') {$tmpsqldater = $sqldater; }
	$sql = "SELECT COUNT(*) as id FROM raw_data $tmpsqldater $teamdefinition";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	$surveys=$row[id];
	return $surveys;
}

function backupvm ($vm_metric, $vm_team, $vm_startdate) {
	global $db, $monthy, $prtselector;

	if ($vm_startdate == '') { echo "No startdate picked."; return "n/a"; }
	if ($vm_metric == '') { echo "No metric picked."; return "n/a"; }
	if ($vm_team == '') { echo "No team picked ({$vm_metric} / {$vm_startdate} / {$vm_team})."; return "n/a"; }

	$vm_startdate_year = substr($vm_startdate,0,4);
	$vm_startdate_month = substr($vm_startdate,5,2);
	$vm_startdate_month = $monthy[$vm_startdate_month];
	$vm_date = $vm_startdate_month.'-'.$vm_startdate_year;
	$mul = 1;

	    if ($vm_metric == "2"){ $vm_metric='aht'; }
	elseif ($vm_metric == "8"){ $vm_metric='psl'; }
	elseif ($vm_metric == "13"){ $vm_metric='esl'; }
	elseif ($vm_metric == "12"){ $vm_metric='pvol'; }
	elseif ($vm_metric == "14"){ $vm_metric='evol'; }

	    if ($vm_metric == "aht"){ $selector = 'email_aht_secs, phone_aht_secs, phone_answered, email_worked'; }
	elseif ($vm_metric == "psl"){ return "n/a"; } // Not available in prt073
	elseif ($vm_metric == "esl"){ $selector = 'email_sl_'; }
	elseif ($vm_metric == "pvol"){ $selector = 'Phone_Answered'; }
	elseif ($vm_metric == "evol"){ $selector = 'Email_Worked'; }
	elseif ($vm_metric == "ptr"){ $selector = 'Phone_Transfer_Rate'; }
	elseif ($vm_metric == "etr"){ $selector = 'Email_Transfer_Rate'; }

	if ($vm_metric == 17) { $selector = "phone_rcr"; }

	if ($selector == '') { return "n/a"; }
		if($vm_metric == 'esl') { $mul = 100; }

		$sql = "SELECT team_prt073 FROM teams WHERE vmdata_team = '$vm_team' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		$vm_team = $row['team_prt073'];

		$sql = "SELECT $selector FROM prt073data WHERE queue_skillset = '{$vm_team}' AND month = '{$vm_date}' AND Queue_Name = 'Total' LIMIT 1";
		if(!$result=$db->query($sql)){
			cl($sql);
			cl($db->error);
		}
		$row=$result->fetch_assoc();

		if ($vm_metric == 'aht') {
			$total_aht = ($row['email_aht_secs'] * $row['email_worked'] + $row['phone_aht_secs'] * $row['phone_answered']) / ($row['email_worked'] + $row['phone_answered']);
			return $total_aht;
		}
		elseif ($vm_metric == 'ptr') {
		}
		if ($row[$selector]==''){ return "n/a"; }
		return $row[$selector]*$mul;
}

/*
	VM (metric, team, start date)
*/

function vm($vm_metric, $vm_team, $vm_startdate, $channel = "combined") {
	global $db, $monthy, $selector;
	$vm_startdate_year = substr($vm_startdate,0,4);
	$vm_startdate_month = substr($vm_startdate,5,2);
	$vm_startdate_month = $monthy[$vm_startdate_month];
	$vm_date = $vm_startdate_month.'-'.$vm_startdate_year;

	$mul = 1;

	if (
		($vm_metric == '8') ||
		($vm_metric == '13') ||
		($vm_metric == '17') ||
		($vm_metric == '23') ||
		($vm_metric == '24')
	) { $mul = 100; }

		$sql = "SELECT vmdata_team FROM teams WHERE id = '$vm_team' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		$vm_team = $row[vmdata_team];
		$sql = "SELECT " . $selector[$vm_metric] . " FROM vm055_data WHERE team = '{$vm_team}' AND month = '{$vm_date}' LIMIT 1";
	if(!$result=$db->query($sql)){
		cl($sql);
		cl($db->error);
		$returno = "n/a";
	}
	else {
		$row = $result->fetch_assoc();
		$returno = '';
		if ($row[$selector]==''){ $returno = "n/a"; }
		$returno = $row[$selector]*$mul;
	}

	// If no value is returned by the vm file, try prt073.
	if ($returno == 'n/a') {
		$returno = backupvm($vm_metric, $vm_team, $vm_startdate);
	}
	return $returno;
}

// Get combined values for a specific date interval.
function getvalue($metric,$valuestartdate,$valueenddate, $teamdefinition){
	global $db,$contract, $_SESSION, $showNewHires;

	$valuestartdate_year = substr($valuestartdate,0,4);
	$valuestartdate_month = substr($valuestartdate,5,2);
	$valuestartdate_day = substr($valuestartdate,8,2);
	$valueenddate_year = substr($valueenddate,0,4);
	$valueenddate_month = substr($valueenddate,5,2);
	$valueenddate_day = substr($valueenddate,8,2);
	$valuestartdate_excel = unixdate_to_exceldate(mktime(0,0,0,$valuestartdate_month,$valuestartdate_day,$valuestartdate_year));
	$valueenddate_excel = unixdate_to_exceldate(mktime(23,59,59,$valueenddate_month,$valueenddate_day,$valueenddate_year));

	$valuesqldater = " WHERE raw_data.Teammate_Contact_Date >= $valuestartdate_excel";
	$valuesqldater .= " AND raw_data.Teammate_Contact_Date <= $valueenddate_excel";


	if ($showNewHires != '1') {
		$teamdefinition .= " AND raw_data.Teammate_Tenure <> '1-30 days'";
	}

	if($metric == '4'){
		$sql = "SELECT external_survey_id FROM raw_data".$valuesqldater . $teamdefinition;
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$surveys = 0;
		while($row=$result->fetch_assoc()){
			$surveys++;
		}
	}
	$topperformer=0;
	$bottomperformer=0;
	$crrr_yes=0;$crrr_inc=0;
	$kdi_sum=0;$kdi_phone=0;$kdi_phone_sum=0;
	$kdi_email=0;$kdi_email_sum=0;

	if($metric=='4'){$co='likely_to_recommend_paypal';}
	elseif($metric=='3'){$co='issue_resolved';}
	elseif($metric=='5'){$co='kdi___email,kdi___phone,Handled_professionally,Showed_genuine_interest,Took_ownership,Knowledge_to_handle_request,Valued_customer,Was_professional,Easy_to_understand,Provided_accurate_info,Helpful_response,Answered_concisely,Sent_in_timely_manner';}
	elseif($metric=='15'){$co='workitem_phone_talk_time';}
	elseif($metric=='16'){$co='customer_contact_count,issue_resolved';}
	else{return 0;} // Unsupported metric


	$sql = "SELECT $co FROM raw_data $valuesqldater ". $teamdefinition;
	$contra = 0;
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){
		$contra++;
		if($metric=='4'){
			if ($row[likely_to_recommend_paypal]>8){$topperformer++;}
			if ($row[likely_to_recommend_paypal]<7){$bottomperformer++;}
		}
		elseif($metric=='3'){
			if ($row[issue_resolved]=='Yes'){$crrr_yes++;}
			if ($row[issue_resolved]!='') {$crrr_inc++;}
		}
		elseif($metric=='5'){
			if (($row[kdi___phone] != '') || ($row[kdi___email] != '')) {
				$k='Handled_professionally';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Showed_genuine_interest';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Took_ownership';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Knowledge_to_handle_request';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Valued_customer';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Was_professional';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Easy_to_understand';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Provided_accurate_info';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Helpful_response';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Answered_concisely';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				//$k='Sent_in_timely_manner';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
			}
		}
		elseif($metric=='15'){
			if ($row[workitem_phone_talk_time]!=''){$att++;$att_sum+=$row[workitem_phone_talk_time];}
		}
		elseif($metric=='16'){
			if ($row[customer_contact_count]!=''){
				$ccc++;
				if (($row[customer_contact_count]==1) && ($row[issue_resolved]=='Yes')){
		  			$ccc_sum++;
				}
			}
		}
	}
	if($metric=='4'){
		$value = round((100*$topperformer/$surveys)-(100*$bottomperformer/$surveys),2);
	}
	//elseif($metric=='3'){ $value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metric=='5'){ $value = round(($kdi_top/$kdi*100),2);}
	elseif($metric=='15'){$value = round(($att_sum/$att),0);}
	elseif($metric=='16'){$value = round(($ccc_sum/$ccc*100),2);}
	else { $value = 0; }
	if ($contra>0){return $value;}
	else{return 'n/a';}
}

// Get any values with a free filter.
/*
  Metric:
    - NPS

*/
function sv($metric,$filter,$surveys,$sqldaterextra){
	global $db, $teamdefinition, $sqldater;
	if ($sqldaterextra == ''){
		$sqldaterextra = $sqldater;
	}
	$topperformer = 0;
	$bottomperformer = 0;
	$crrr_yes = 0;
	$crrr_inc = 0;
	$kdi_sum = 0;
	$kdi_phone = 0;
	$kdi_phone_sum = 0;
	$kdi_email = 0;
	$kdi_email_sum = 0;

	if ($metric =='4') {
		$co = 'likely_to_recommend_paypal';
	}
	elseif ($metric == '3') {
		$co = 'issue_resolved';
	}
	elseif ($metric == '5') {
		$co = 'kdi___email,kdi___phone,Handled_professionally,Showed_genuine_interest,Took_ownership,Knowledge_to_handle_request,Valued_customer,Was_professional,Easy_to_understand,Provided_accurate_info,Helpful_response,Answered_concisely,Sent_in_timely_manner';
	}
	elseif ($metric == '15') {
		$co = 'workitem_phone_talk_time';
	}
	elseif ($metric == '16') {
		$co = 'customer_contact_count,issue_resolved';
	}
	else {
		return "error with metric '$metric'";
	}
	$sql = "SELECT $co FROM raw_data $sqldaterextra $teamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$contra = 0;
	$npssurveys = 0;
	while ($row = $result->fetch_assoc()){
		$contra++;
		if($metric == '4') {
			if ($row[likely_to_recommend_paypal] > 8) {
				$topperformer++;
			}
			if ($row[likely_to_recommend_paypal] < 7) {
				$bottomperformer++;
			}
			if ($row[likely_to_recommend_paypal] != ''){
		 		$npssurveys++;
	 		}
		}
		elseif($metric == '3') {
			if ($row[issue_resolved] == 'Yes'){
				$crrr_yes++;
			}
			if ($row[issue_resolved] != '') {
				$crrr_inc++;
			}
		}
		elseif($metric == '5') {
			if (($row[kdi___phone] != '') || ($row[kdi___email] != '')) {
				$k = 'Handled_professionally';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Showed_genuine_interest';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Took_ownership';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Knowledge_to_handle_request';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Valued_customer';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Was_professional';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Easy_to_understand';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Provided_accurate_info';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Helpful_response';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k = 'Answered_concisely';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				//$k='Sent_in_timely_manner';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
			}
		}
		elseif ($metric == '15') {
			if ($row[workitem_phone_talk_time] != '') {
				$att++;
				$att_sum += $row[workitem_phone_talk_time];
			}
		}
		elseif ($metric == '16') {
			if ($row[customer_contact_count] != '') {
				$ccc++;
				if (($row[customer_contact_count] == 1) && ($row[issue_resolved] == 'Yes')) {
					$ccc_sum++;
				}
			}
		}
	}
	if ($metric == '4') {
		$value = round((100 * $topperformer / $npssurveys) - (100 * $bottomperformer / $npssurveys), 2);
	}
	elseif ($metric == '3') {
		$value = round(100 * ($crrr_yes / $crrr_inc), 2);
	}
	elseif ($metric == '5') {
		$value = round(($kdi_top / $kdi * 100), 2);
	}
	elseif ($metric == '15') {
		$value = round(($att_sum / $att), 0);
	}
	elseif ($metric == '16') {
		$value = round(($ccc_sum / $ccc * 100), 2);
	}
	else {
		$value = 0;
	}
	if ($contra > 0) {
		return $value;
	}
	else {
		return '--';
	}
}

// Get any values with a free filter and surveys.
/*
  Metric:
    - NPS

*/
function svs($metric,$filter,$surveys,$sqldaterextra){
	global $db,$teamdefinition,$sqldater;
  if ($sqldaterextra==''){$sqldaterextra = $sqldater;}
	$topperformer=0;
	$bottomperformer=0;
	$crrr_yes=0;$crrr_inc=0;
	$kdi_sum=0;$kdi_phone=0;$kdi_phone_sum=0;
	$kdi_email=0;$kdi_email_sum=0;
	if($metric=='4'){$co='likely_to_recommend_paypal';}
	elseif($metric=='3'){$co='issue_resolved';}
	elseif($metric=='5'){$co='kdi___email,kdi___phone,Handled_professionally,Showed_genuine_interest,Took_ownership,Knowledge_to_handle_request,Valued_customer,Was_professional,Easy_to_understand,Provided_accurate_info,Helpful_response,Answered_concisely,Sent_in_timely_manner';}
	elseif($metric=='15'){$co='workitem_phone_talk_time';}
	elseif($metric=='16'){$co='customer_contact_count,issue_resolved';}
	else { return "error with metric '$metric'"; }
	$sql = "SELECT $co FROM raw_data $sqldaterextra $teamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$contra = 0;
	$surveys = 0;
	$npssurveys=0;
	while($row=$result->fetch_assoc()){
		$surveys++;
		$contra++;
		if($metric=='4'){
			if ($row[likely_to_recommend_paypal]>8){$topperformer++;}
			if ($row[likely_to_recommend_paypal]<7){$bottomperformer++;}
      if ($row[likely_to_recommend_paypal]!=''){$npssurveys++;}
		}
		elseif($metric=='3'){
			if ($row[issue_resolved]=='Yes'){$crrr_yes++;}
			if ($row[issue_resolved]!='') {$crrr_inc++;}
		}
		elseif($metric=='5'){
			if (($row[kdi___phone] != '') || ($row[kdi___email] != '')) {
				$k='Handled_professionally';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Showed_genuine_interest';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Took_ownership';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Knowledge_to_handle_request';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Valued_customer';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Was_professional';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Easy_to_understand';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Provided_accurate_info';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Helpful_response';if ($row[$ok]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				$k='Answered_concisely';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
				//$k='Sent_in_timely_manner';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
			}
		}
		elseif($metric=='15'){
			if ($row[workitem_phone_talk_time]!=''){$att++;$att_sum+=$row[workitem_phone_talk_time];}
		}
		elseif($metric=='16'){
			if ($row[customer_contact_count]!=''){
				$ccc++;
				if (($row[customer_contact_count]==1) && ($row[issue_resolved]=='Yes')){
					$ccc_sum++;
				}
			}
		}
	}
	if($metric == '4') {
		$value = round((100*$topperformer/$npssurveys)-(100*$bottomperformer/$npssurveys),2);
	}
	elseif($metric == '3') {
		$value = round(100*($crrr_yes/$crrr_inc),2);
	}
	elseif($metric == '5') {
		$value = round(($kdi_top/$kdi*100),2);
	}
	elseif($metric == '15') {
		$value = round(($att_sum/$att),0);
	}
	elseif($metric == '16') {
		$value = round(($ccc_sum/$ccc*100),2);
	}
	else {
		$value = 0;
	}

	if ($contra > 0){
		return array($value,$contra);
	}
	else {
		return '0';
	}
}

// Get any PRT60p value based on month and filter.
// Options:
//    answered = phone calls answered
//    worked = emails worked
//    eaht = email aht
//    paht = phone aht
//
function av($what,$monthdate,$filter){
	global $db,$sqldater,$monthy,$ahtteamdefinition;
	$answered = 0;
	$worked = 0;
	$eaht = 0;
	$paht = 0;
	$montha = substr($monthdate,5,2);
	$montha = $monthy[$montha];
	$yeara = substr($monthdate,0,4);
	$aht = 0;

	if ($what == 2) {
		$what='aht';
	}
	if ($what == 17) {
		$what='rcr';
	}
	if ($what == 6) {
		$what='tr';
	}
	if ($what == 14) {
		$what='worked';
	}
	if ($what == 12) {
		$what='answered';
	}
	$sql = "SELECT
		phone_rcr,
		ntid,
		queue_name,
		contacts_handled,
		phone_answered,
		email_worked,
		email_transferred,
		phone_contact_transfers,
		phone_aht_secs,
		email_aht_secs,
		total_aht_secs,
		phone_avg_acw_time_secs
	FROM
		prt060data
	WHERE
		month='$montha-$yeara' $ahtteamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$contra = 0;
	$counto = 0;
	$counta = 0;
	while($row=$result->fetch_assoc()){
		$contra++;
		if ($what=='answered'){
			$counto+=$row[phone_answered];
			$counta+=$row[phone_answered];
		}
		elseif ($what=='worked'){
			$counto+=$row[email_worked];
			$counta+=$row[email_worked];
		}
		elseif ($what=='eaht'){
			$counto+=$row[email_aht_secs]*$row[email_worked];
			$counta+=$row[email_worked];
		}
		elseif ($what=='paht'){
			$counto+=$row[phone_aht_secs]*$row[phone_answered];
			$counta+=$row[phone_answered];
		}
		elseif ($what=='aht'){
			$counto+=$row[total_aht_secs]*$row[contacts_handled];
			$counta+=$row[contacts_handled];
		}
		elseif ($what=='tr'){
			$counto+=$row['email_transferred'];
			$counto+=$row['phone_contact_transfers'];
			$counta+=$row[contacts_handled];
		}
		elseif ($what == 'rcr') {
			$counto+=$row[phone_rcr]*$row[phone_answered];
			$counta+=$row[phone_answered];
		}
		elseif ($what == 'acw') {
			$counto+=$row['phone_avg_acw_time_secs'] * $row['phone_answered'];
			$counta+=$row['phone_answered'];
		}
	}
	$counto = intval($counto);
	$counta = intval($counta);

	if (($what=='paht') or ($what=='eaht') or ($what=='aht') or ($what=='tr') or ($what=='rcr') or ($what=='acw')) {
		$counto=$counto / $counta;
		//if ($what == 'tr') { $counto = floor($counto * 10000)/10000; }
	}
	if ($counta > 0) {
		return $counto;
	}
	return '--';
}
function gethours($tm,$date){
	global $db;
	$month = substr($date,0,7);
	$sql = "SELECT worked_hours FROM hours WHERE teammate_nt_id = '$tm' AND date = '$month' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[worked_hours];
}
function getinstances($tm,$date){
	global $db;
	$month = substr($date,0,7);
	$sql = "SELECT sick_instances FROM hours WHERE teammate_nt_id = '$tm' AND date = '$month' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[sick_instances];
}

function geticon($name) {
	if ($name == 'dashboard') { $name = 'chart-bar'; }
	elseif ($name == 'surveys') { $name = 'comment-alt'; }
	elseif ($name == 'settings') { $name = 'cog'; }
	elseif ($name == 'bonus') { $name = 'money-bill-alt'; }
	elseif ($name == 'targets') { $name = 'bullseye'; }
	elseif ($name == 'certification') { $name = 'certificate'; }
	elseif ($name == 'trends') { $name = 'chart-line'; }
	elseif ($name == 'seating') { $name = 'th-large'; }
	elseif ($name == 'elite') { $name = 'bolt'; }
	elseif ($name == 'admin') { $name = 'ban'; }
	elseif ($name == 'digger') { $name = 'angle-double-down'; }
	elseif ($name == 'team') { $name = 'globe'; }
	elseif ($name == 'surfer') { $name = 'anchor'; }
	return $name;
}
function showphotos() {
  global $db, $uid;
  $sql = "SELECT user_showphotos FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row['user_showphotos'];
}
function getphoto($tm, $size) {
	global $path;
	// Don't get photo if user does not have access to photos.
	if (!$size) { $size = 70; }
	if (showphotos()) {
		// TODO: check if tm has a photo
		// TODO: return the filename of photo
		if (file_exists("$path/photos/$tm.jpg")) {
			return "<img class='photo' src='/gold/photos/$tm.jpg' width=$size height=$size alt='Photo of $tm'>";
		}
		else { return ''; }
	}
}

function getUserBadge($ntid, $options = "") {
	global $db, $sqldater, $teamdefinition;
	$statement = "SELECT ntid, employee FROM prt060data WHERE ntid = '$ntid'";
	$result = $db->query($statement);
	$row = $result->fetch_assoc();
	$teammate['guild'][$row['ntid']] = guessteam($row['ntid']);
	$teammate['name'][$row['ntid']] = $row['employee'];
	$teammate['guildname'][$row['ntid']] = g("teams","team_name",$teammate['guild'][$row['ntid']]);
	$rt = "";
	if (($team < 1) or (guessteam($ntid) == $team)) {
		$fgcolor = g("teams","team_fgcolor",$teammate['guild'][$ntid]);
		$bgcolor = g("teams","team_bgcolor",$teammate['guild'][$ntid]);
		$brcolor = g("teams","team_border",$teammate['guild'][$ntid]);
		$iso = '';
		if ($teammate['guildname'][$ntid] == 'Denmark') { $iso = 'flag-dk'; }
		if ($teammate['guildname'][$ntid] == 'Netherlands') { $iso = 'flag-nl'; }
		if ($teammate['guildname'][$ntid] == 'Norway') { $iso = 'flag-no'; }
		if ($teammate['guildname'][$ntid] == 'Sweden') { $iso = 'flag-se'; }
		if ($teammate['guild'][$ntid] == '15') { $iso = 'gb'; }

		$rt .= "<div class='player' style='background: #$bgcolor; color: #$fgcolor;' onClick='location=\"?a=user&b=$ntid\";'>";

		$rt .= "<div class='player-guild'>";
			$rt .= "<i class='em em-$iso'></i>";
		$rt .= "</div>";

		$rt .= "<div class='player-level'>";
			$rt .= round(agent_getlevel($ntid), 1);
		$rt .= "</div>";

		$rt .= "<div class='player-photo'>";
		$rt .= getphoto($ntid, 45);
		$rt .= "</div>";

		list($teammate['firstname'][$ntid], $teammate['surname'][$ntid]) = explode(' ', $teammate['name'][$ntid]);

		if ($options != "hide_name") {
			$rt .= "<div class='player-firstname'>";
			$rt .= $teammate['firstname'][$ntid];
			$rt .= "</div>";

			$rt .= "<div class='player-surname'>";
			$rt .= $teammate['surname'][$ntid];
			$rt .= "</div>";
		}


		$rt .= "</div>";
	}
	return $rt;
}

function agent_getlevel($ntid) {
	global $db;
	$xp_per_level = 1000;
	$level = floor(db_get("SELECT SUM(contacts_handled) as ch FROM prt060data WHERE ntid = '$ntid' AND queue_name <> 'Total'")['ch'] / $xp_per_level);
	return (str_pad($level, 2, "0", STR_PAD_LEFT));
}

function agent_getteamname($team_id) {
	global $db;
	return sqr("SELECT team_name FROM teams WHERE id = '$team_id' LIMIT 1")['team_name'];
}

function agent_getrank($ntid) {
	global $db, $sqldater, $teamdefinition, $survey_threshold;
	$statement = "SELECT COUNT(external_survey_id) AS goodsurveys FROM raw_data $sqldater $teamdefinition AND likely_to_recommend_paypal > 7 AND (kdi___email > 75 OR kdi___phone > 75) AND issue_resolved = 'Yes' AND teammate_nt_id = '$ntid' GROUP BY teammate_name";
	if (!$result = $db->query($statement)) { cl($statement); cl($db->error); }
	$row = $result->fetch_assoc();
	$goodsurveys = $row['goodsurveys'];
	$statement = "SELECT COUNT(external_survey_id) AS surveys FROM raw_data $sqldater $teamdefinition AND (kdi___email <> '' OR kdi___phone <> '') AND teammate_nt_id = '$ntid' GROUP BY teammate_name";
	if (!$result = $db->query($statement)) {
		cl($sqla); cl($db->error); }
	$row = $result->fetch_assoc();
	if ($row['surveys'] < $survey_threshold) { return ''; }
	return round($goodsurveys / $row['surveys'] * 10, 0);
}

function agent_getrankicon($rank) {
	switch ($rank) {
		case 3:
			return "<i class='em em-slightly_smiling_face'></i>";
			break;
		case 4:
			return "<i class='em em-star'></i>";
			break;
		case 5:
			return "<i class='em em-star'></i><i class='em em-star'></i>";
			break;
		case 6:
			return "<i class='em em-star'></i><i class='em em-star'></i><i class='em em-star'></i>";
			break;
		case 7:
			return "<i class='em em-star2'></i>";
			break;
		case 8:
			return "<i class='em em-star2'></i><i class='em em-star2'></i>";
			break;
		case 9:
				return "<i class='em em-star2'></i><i class='em em-star2'></i><i class='em em-star2'></i>";
				break;
		case 10:
			return '<i class="em em-grinning_face_with_star_eyes"></i>';
			break;
	}
	return '';
}

function db_get ($query) {
	global $db;

	// TODO: make query safe.
	if(!$r = $db->query($query)) {
		echo mysqli_error($db);
		return false;
	}
	return $r->fetch_assoc();
}

function db_set ($query) {
	global $db;

	// TODO: make query safe.
	if(!$r = $db->query($query)) {
		echo mysqli_error($db);
		return false;
	}
	return true;
}

function showSelect($table, $getvar) {
	global $db, $app_action;
	$query = "SELECT column_name FROM {$table}";
	echo "<select id='select{$table}' onChange='location.href=\"?a={$app_action}&{$getvar}=\" + this.value;'>";
	if(!$r = $db->query($query)) { }
	echo "<option>--</option>";
	while ($ret = $r->fetch_assoc()) {
		echo "<option";
		if ($_GET[$getvar] == $ret['column_name']) { echo ' selected'; }
		echo ">{$ret['column_name']}</option>";
	}
	echo "</select>";
}

function showUserSelect() {
	global $db, $app_action, $sqldater, $teamdefinition;
	$query = "SELECT teammate_nt_id,teammate_name FROM raw_data $sqldater $teamdefinition GROUP BY teammate_nt_id ORDER BY teammate_nt_id";
	echo "<select id='selectuser' onChange='location.href=\"?a={$app_action}&digger={$_GET['digger']}&filter=\" + this.value;'>";
	if(!$r = $db->query($query)) { }
	echo "<option value=''>--</option>";
	while ($ret = $r->fetch_assoc()) {
		echo "<option";
		if ($_GET['filter'] == $ret['teammate_nt_id']) { echo ' selected'; }
		echo " value='{$ret['teammate_nt_id']}'>{$ret['teammate_name']} ({$ret['teammate_nt_id']})</option>";
	}
	echo "</select>";

}

function isLeaver($ntid) {
	global $db;

}

function displayvmbox ($dvm_team, $dvm_metric, $dvm_value) {
	global $startdate, $simplecolors;

	if (isset($dvm_value)){ $value = $dvm_value; }
	else { $value = vm($dvm_metric, $dvm_team, $startdate); }

	$tmptarget = gettarget(5,$dvm_metric,$dvm_team,$startdate,"low");
	list($bg,$fg) = targetcolor($value, 5, $dvm_metric, $dvm_team, $startdate);

	echo "<td class='dashboardtd";
	if ($dvm_team == 5) {
		if ($dvm_metric == 2 || $dvm_metric == 4) { echo " topleftradius"; }
		if ($dvm_metric == 17 || $dvm_metric == 4) { echo " bottomleftradius"; }
	}
	if ($dvm_team == 16) {
		if ($dvm_metric == 2 || $dvm_metric == 4) { echo " toprightradius"; }
		if ($dvm_metric == 17 || $dvm_metric == 4) { echo " bottomrightradius"; }
	}
	// Display the simple colors and we have a target?
	if ($simplecolors && $tmptarget) {
		if ($tmptarget <= $value) {
			echo " simple-bad'";
			$fg = ' simple-bad';
		}
		else {
			echo " simple-good'";
			$fg = ' simple-good';
		}
	}
	else {
		echo "' style='background-color:#$bg;";
		echo " color:#$fg;'";
		$fg = "' style='color:#$fg;";
	}
	echo ">";

	if ($value == "n/a") {
		echo "<span class='valuearea'>n/a</span>";
		$fg = "";
		$bg = "";
	}
	else {
		echo "<span class='valuearea'>";
		echo  round($value, gmr($dvm_metric)). gms($dvm_metric);
		echo "</span>";
	}

	// Display target area if there is a target.
	if (isset($tmptarget)) {

		// Target title
			echo "<span class='targettitle$fg'><i class='fa fa-bullseye'></i></span>";

		// Target area
			echo "<span class='targetarea$fg'>";
			echo $tmptarget;
			echo gms($dvm_metric);
			echo "</span>";

		// Delta area
			if ($value != "n/a"){
				echo "<span class='targetdelta$fg'>";
				//echo "<span class='targetdeltatitle$fg'>&Delta;</span>";

				// If the metric is AHT, calculate delta in one way.
				if ($dvm_metric == '2') { $tmpdelta = ($value / $tmptarget) - 1; }
				else { $tmpdelta = $value - $tmptarget; $tmpdelta = $tmpdelta / 100; }
				echo round($tmpdelta*100,1);
				echo "%</span>";
			}
	}
	echo "</td>\n\n";
}
function displaydashboardbox($ddb_team, $ddb_metric, $submetric){
	global $startdate, $enddate, $team_id_def, $today, $contract, $simplecolors;
	$teamdefinition = $team_id_def[$ddb_team];
	$value = getvalue($ddb_metric,$startdate,$enddate, $teamdefinition);
	$highorlow='high';if ($ddb_metric=='2'){$highorlow='low';}
	$tmptarget = gettarget(5,$ddb_metric,$ddb_team,$startdate,$highorlow,$submetric);

	// Value, contract, metric, team, date, submetric)
	list($bg,$fg) = targetcolor($value, 5, $ddb_metric, $ddb_team, $startdate, $submetric);
	$rounding = gmr($ddb_metric);
	$symbol = gms($ddb_metric);

	echo "<td class='dashboardtd";
	if ($ddb_team == 5) {
		if ($ddb_metric == 4) { echo " topleftradius bottomleftradius"; }
	}
	if ($ddb_team == 16) {
		if ($ddb_metric == 4) { echo " toprightradius bottomrightradius"; }
	}
	if ($simplecolors && $tmptarget) {
		if ($tmptarget < $value) {
			if ($highorlow=='low') {
				echo " simple-bad'";
				$fg = ' simple-bad';
			}
			else {
				echo " simple-good'";
				$fg = ' simple-good';
			}
		}
		else {
			if ($highorlow=='low') {
				echo " simple-good'";
				$fg = ' simple-good';
			}
			else {
				echo " simple-bad'";
				$fg = ' simple-bad';
			}
		}
	}
	else {
		echo "' style='background-color:#$bg;";
		echo " color:#$fg;' ";
		$fg = "' style='color:#$fg;";
	}

	echo "'>";

	if ($value == "n/a") {
		echo "<span class='valuearea'>n/a</span>";
		$fg = "";
	}
	else {
		// Value area
		echo "<span class='valuearea'>";
		echo round($value,$rounding) . $symbol;
		echo "</span>";
	}


	// Display target areas if there is a target.
	if ($tmptarget) {
		// Target title
		echo "<span class='targettitle$fg'><i class='fa fa-bullseye'></i></span>";

		// Target area
		echo "<span class='targetarea$fg'>";
		echo $tmptarget;
		echo "</span>";
		echo "<span class='targetdelta$fg'>";
		//echo "<span class='targetdeltatitle$fg'>&Delta;</span>";
		$tmpdelta = $value - $tmptarget;

		// NPS has a range from -100 to 100. The rest has a range of 100 (AHT is not displayed in this function).
		if ($ddb_metric==4) { $tmpdelta=$tmpdelta/200; }
		else { $tmpdelta=$tmpdelta/100; }
		echo round($tmpdelta*100,1);
		echo "%</span>";
	}

	$tmpstartdate = date("Y-m-d",strtotime("-1 week +1 day"));
	$tmpenddate = $today;
	$sevendays = getvalue($ddb_metric,$tmpstartdate,$tmpenddate, $teamdefinition);

	list($tbg,$tfg) = targetcolor($sevendays, 5, $ddb_metric, $ddb_team, $startdate, $submetric);

	if ($value != "n/a") {

		// Last week delta area
		echo "<div class='sevendaysdelta'>";
		$tmpdelta = $sevendays - $value;
		if ($tmpdelta != 0) {
			echo "<i class='fa fa-";
			if ($tmpdelta < 0) {
				echo "long-arrow-alt-down deltabad";
			}
			else {
				echo "long-arrow-alt-up deltagood";
			}
			echo "'></i>";
		}
		echo "</div>";
	}
	echo "</td>";
}

/*function teamster() {
	echo "<a href='?a=teams&b=add'>Add team</a>.<br><br>";
	if ($b == 'add'){
		if ($_GET[name]){
  			$sql = "INSERT INTO teams (team_name, team_admin_user_id, contract_id) VALUES('{$_GET[name]}','$uid','{$_GET[contract]}')";
  			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
		else {
  			echo "<form>
  			Team name: <input name='name'><br>
  			Contract: <select name='contract'>";

  			$sql = "SELECT contract_id,contract_name FROM contracts";
  			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  			while($row=$result->fetch_assoc()){
	  			echo "<option value={$row[contract_id]}>{$row[contract_name]}</option>";
  			}
  			echo "</select><br>
  			<input name=a value=teams type=hidden><input name=b value=add type=hidden><input type=submit></form><br>";
		}
	}
	$sql = "SELECT * FROM teams ORDER by team_name ASC";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	echo "<ul>";
	while($row=$result->fetch_assoc()){
  		echo "<li><a href='?a=teams&b=show_team&team={$row[id]}'>".$row[team_name]."</a>";
	}
	echo "</ul>";
	if ($b=='show_team'){
		$team=$_GET[team];
		if (isadmin()) {
			if ($c == 'delsurvey'){
				$sql = "DELETE FROM team_data_definitions WHERE id = '$team' AND raw_data_column = '{$_GET[column]}' AND raw_data_data = '{$_GET[data]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			if ($c == 'delaht'){
				$sql = "DELETE FROM team_aht_definitions WHERE id = '$team' AND ahtreport_data_column = '{$_GET[column]}' AND ahtreport_data_data = '{$_GET[data]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
		}
  		if ($_GET[adata]){
			$sql = "INSERT INTO team_aht_definitions (team_id,ahtreport_data_column,ahtreport_data_data) VALUES('$team','$column_name','{$_GET[adata]}')";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			echo "Definition added.";
  		}
  		if ($_GET[vmdef]){
			$sql = "UPDATE teams SET vmdata_team = '{$_GET[vmdef]}' WHERE id = '{$team}' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			echo "Definition added.";
  		}
		$sql = "SELECT * FROM teams WHERE id='$team' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		echo "<h1>{$row[team_name]}</h1>";
		$sql = "SELECT * FROM team_data_definitions WHERE team_id = '$team' ORDER by raw_data_column, raw_data_data";
		if (!$result=$db->query($sql)) {
			cl($sql);
			cl($db->error);
		}
		echo "<h3>Survey definitions: </h3><ul>";
		while($row=$result->fetch_assoc()){
			echo "<li>";
			if (isadmin()) {
				echo "<a href='?a=$a&b=$b&team=$team&c=delsurvey&column={$row[raw_data_column]}&data={$row[raw_data_data]}'>( x )</a> &nbsp; ";
			}
			echo $row[raw_data_column] . " = " . $row[raw_data_data] . "</li>";
		}
		echo "</ul>";
		echo "Add column and survey filters for this team.";
		$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='raw_data' AND table_schema='concentrix'";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "<form><br>Data column: <select name=column_name onChange='location.href=\"?a=teams&b=show_team&team=$team&column_name=\" + this.value;'>";
		echo "<option></option>";
		while($row=$result->fetch_assoc()){
			echo "<option";
			if ($_GET[column_name]==$row[column_name]){echo" selected";}
			echo ">".$row[column_name]."</option>";
		}
		echo "</select>";
		if ($_GET[column_name]){
			$column_name=$_GET[column_name];
			$sql = "SELECT distinct $column_name FROM raw_data ORDER by $column_name ASC";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			echo "<br>Data: <select name=data onChange='location.href=\"?a=teams&b=show_team&team=$team&column_name=$column_name&data=\" + this.value;'>";
			echo "<option></option>";
			while($row=$result->fetch_assoc()){
				echo "<option";
				if ($_GET[data]==$row[$column_name]){echo" selected";}
				echo ">".$row[$column_name]."</option>";
			}
			echo "</select>";
			echo "<input name=a type=hidden value=teams>
			<input name=b type=hidden value=show_team>
			<input name=team type=hidden value=$team>
			</form>";
			if ($_GET[data]){
				$sql = "INSERT INTO team_data_definitions (team_id,raw_data_column,raw_data_data) VALUES('$team','$column_name','{$_GET[data]}')";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "Definition added.";
			}
		}
		$sql = "SELECT * FROM team_aht_definitions WHERE team_id = '$team' ORDER by ahtreport_data_column, ahtreport_data_data";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "<h3>AHT definitions:</h3>";
		while($row=$result->fetch_assoc()){
			echo "<li>";
			if (isadmin()) {
				echo "<a href='?a=$a&b=$b&team=$team&c=delaht&column={$row[ahtreport_data_column]}&data={$row[ahtreport_data_data]}'>( x )</a> &nbsp; ";
			}
			echo $row[ahtreport_data_column] . " = " . $row[ahtreport_data_data] . "</li>";
		}
		echo "</ul>";
		echo "<br>Add column and AHT filters for this team.";
		$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='prt060data' AND table_schema='concentrix'";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "<form><br>Data column: <select name=acolumn_name onChange='location.href=\"?a=teams&b=show_team&team=$team&acolumn_name=\" + this.value;'>";
		echo "<option></option>";
		while($row=$result->fetch_assoc()){
			echo "<option";
			if ($_GET[acolumn_name]==$row[column_name]){echo" selected";}
				echo ">".$row[column_name]."</option>";
			}
			echo "</select>";
			if ($_GET[acolumn_name]){
				$column_name=$_GET[acolumn_name];
				$sql = "SELECT distinct $column_name FROM prt060data ORDER by $column_name ASC";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "<br>Data: <select name=data onChange='location.href=\"?a=teams&b=show_team&team=$team&acolumn_name=$column_name&adata=\" + this.value;'>";
				echo "<option></option>";
				while($row=$result->fetch_assoc()){
					echo "<option";
					echo ">".$row[$column_name]."</option>";
				}
				echo "</select>";
				echo "<input name=a type=hidden value=teams>
				<input name=b type=hidden value=show_team>
				<input name=team type=hidden value=$team>
				</form>";
				echo "<br><br><br>";
			}
  			echo "<h3>VM055p definition</h3>";
  			echo "Select a team from VM055p: ";
  			echo "<select id=vmdef onChange='location.href=\"?a=$a&b=$b&team=$team&vmdef=\" + this.value;'>";
  			$sql = "SELECT vmdata_team FROM teams WHERE id = '$team' LIMIT 1";
  			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  			$row=$result->fetch_assoc();
  			$vmdef = $row[vmdata_team];
  			$sql = "SELECT DISTINCT team FROM vm055_data ORDER BY team asc";
  			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  			echo "<option></option>";
  			while($row=$result->fetch_assoc()){
				echo "<option";
				if ($row[team]==$vmdef){echo " selected";}
				echo ">".$row[team]."</option>";
  			}
  			echo "</select>";
		}
	}
}
*/
function dashboardHeader($team_id) {
	global $startdate, $team_id_def;
	$team_short = strtolower(g('teams', 'team_shortname', $team_id));
	$team_long = g('teams', 'team_name', $team_id);
	$pvalue = vm(12,$team_id,$startdate);
	$evalue = vm(14,$team_id,$startdate);

	return ("
	<th class='dashboard-team'>
		<div>
			<a href='?a=team&b={$team_id}'>
				<span class='dashboard-team-flag text-right'><i class='em em-flag-{$team_short}' alt='Country'></i></span>
				<span class='dashboard-team-name'>{$team_long}</span>
			</a>
		</div>
		<div class='dashboard-team-surveys dashboard-team-info'>
			<span class='text-right' title='Surveys received'><i class='far fa-".geticon('surveys')."'></i></span>
			<span>" . surveycount("",$team_id_def[$team_id]) . "</span>
		</div>
		<div class='dashboard-team-latestsurvey dashboard-team-info'>
			<span class='text-right'><i class='far fa-clock'></i></span>
			<span>".getlatest($team_id_def[$team_id])."</span>
		</div>
		<div class='dashboard-team-phone-volume dashboard-team-info'>
			<span class='text-right'><i class='fa fa-phone'></i></span>
			<span>{$pvalue}</span>
		</div>
		<div class='dashboard-team-email-volume dashboard-team-info'>
			<span class='text-right'><i class='fa fa-envelope'></i></span>
			<span>{$evalue}</span>
		</div>
	</th>
	");
}

function getUserMonths($ntid) {
	$sql = "SELECT COUNT(DISTINCT Month) as distinct_months
		FROM prt060data
		WHERE ntid = '$ntid'
		GROUP BY ntid
		LIMIT 1
		";
	return sqr($sql)['distinct_months'];
}

function getContacts($ntid, $channel = "combined") {
	$sql = "SELECT sum(email_worked) as e, sum(phone_answered) as p FROM prt060data WHERE ntid = '{$ntid}' AND queue_name <> 'Total'";
	$r = sqr($sql);
	if ($channel == "phone") {
		return $r['p'];
	}
	else if ($channel == "email") {
		return $r['e'];
	}
	return $r['e'] + $r['p'];
}

function cpm($ntid) {
	return round(getContacts($ntid)/getUserMonths($ntid));
}
