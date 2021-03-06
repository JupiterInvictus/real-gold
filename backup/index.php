<?php

$forked = true;

$time = time();

define('CHARSET', 'ISO-8859-1');
define('REPLACE_FLAGS', ENT_COMPAT | ENT_XHTML);
$application_name = "Concentrix Global Online Leader Dashboard";
$application_copyright = "Copyright Concentrix Europe Ltd 2017";
$application_contact = "joakim.saettem@concentrix.com";
set_time_limit(600);

$time = $_SERVER[‘REQUEST_TIME’];
$timeout_duration = 432000;
session_start();
if (isset($_SESSION[‘LAST_ACTIVITY’]) && ($time - $_SESSION[‘LAST_ACTIVITY’]) > $timeout_duration) {
  session_unset();
  session_destroy();
  session_start();
}
$_SESSION[‘LAST_ACTIVITY’] = $time;

$application_version_major = "2017-08-21";
$application_version_minor = "08:53";
$month['01']="January";$month['02']="February";$month['03']="March";$month['04']="April";$month['05']="May";$month['06']="June";$month['07']="July";$month['08']="August";
$month['09']="September";$month['10']="October";$month['11']="November";$month['12']="December";
$month['1']="January";$month['2']="February";$month['3']="March";$month['4']="April";$month['5']="May";$month['6']="June";$month['7']="July";$month['8']="August";
$month['9']="September";
$monthy['01']='JAN';$monthy['02']='FEB';$monthy['03']='MAR';$monthy['04']='APR';$monthy['05']='MAY';$monthy['06']='JUN';
$monthy['07']='JUL';$monthy['08']='AUG';$monthy['09']='SEP';$monthy['10']='OCT';
$monthy['11']='NOV';$monthy['12']='DEC';
$monthy['1']='JAN';$monthy['2']='FEB';$monthy['3']='MAR';
$monthy['4']='APR';$monthy['5']='MAY';$monthy['6']='JUN';
$monthy['7']='JUL';$monthy['8']='AUG';$monthy['9']='SEP';
$days['1']='Monday';$days['2']='Tuesday';$days['3']='Wednesday';$days['4']='Thursday';$days['5']='Friday';$days['6']='Saturday';$days['0']='Sunday';
error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

include "config.php";

$uid = $_SESSION[user_id];
include "$path../../zm-core-db.php"; $ddb = 'concentrix'; db_connect();
include "$path../../zm-core-login.php";
include "$path../../zm-core-functions.php";
function html($string) {
    return htmlspecialchars($string, REPLACE_FLAGS, CHARSET);
}


function sq($q){global $db;if(!$s=$db->query($q)){echo $q;echo $db->error;}}
function sqr($q){global $db;if(!$s=$db->query($q)){cl($q);cl($db->error);}return $s->fetch_assoc();}

// Get database data
function g ($tablename, $columnname, $tmpid) {
	global $db;
	$sql = "SELECT $columnname FROM $tablename WHERE id = '$tmpid' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[$columnname];
}


// Make gradient
function mg($color){
  $r = substr($color,0,2);
  $g = substr($color,2,2);
  $b = substr($color,4,2);
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
  $valuesqldater = " WHERE Teammate_Contact_Date > $valuestartdate_excel";
  $valuesqldater .= " AND Teammate_Contact_Date < $valueenddate_excel";
  $sql = "SELECT Response_Date FROM raw_data $valuesqldater $teamdeff ORDER by Response_Date DESC LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  $row=$result->fetch_assoc();
  if ($row[Response_Date]==''){return 'n/a';}
  return date("Y-m-d",exceldate_to_unixedate($row[Response_Date]));
}

function displayvmbox($dvm_team, $dvm_metric, $dvm_value) {
  global $startdate;
  if ($dvm_value<>""){$value = $dvm_value;}
  else { $value = vm($dvm_metric,$dvm_team,$startdate); }
  if ($value==''){return;}
  list($bg,$fg) = targetcolor($value, 5, $dvm_metric, $dvm_team, $startdate);
  echo "<td class='dashboardtd'>";

  // Value area
  echo "<span class='valuearea' style='color:#$fg;background-color:#$bg'>". round($value,gmr($dvm_metric)). gms($dvm_metric)."</span>";

  // Target title
  echo "<span class='targettitle xtoggle' style='color:#$fg'>&ofcir;</span>";
  // Target area
  echo "<span class='targetarea xtoggle' style='color:#$fg;background-color:#$bg'>";
  $highorlow='low';
  $tmptarget = gettarget(5,$dvm_metric,$dvm_team,$startdate,$highorlow);
  echo $tmptarget;
  echo "</span>";
  // Delta area
  if ($value!=''){
		echo "<span class='targetdeltatitle xtoggle' style='color:#$fg'>&Delta;</span>";
    echo "<div class='targetdelta xtoggle";
    echo "' style='color:#$fg;background-color:#$bg";
    if ($dvm_metric == '2'){$tmpdelta = ($value / $tmptarget) - 1;}
    else {$tmpdelta = $value - $tmptarget;$tmpdelta=$tmpdelta/100;}
    echo "'>";
    echo round($tmpdelta*100,1);
    echo "%</div>";
  }
  echo "</td>\n\n";
}
function displaydashboardbox($ddb_team, $ddb_metric, $submetric){

  global $startdate, $enddate, $tdef, $teamdefinition, $today;
  $teamdefinition = $tdef[$ddb_team];
  $value = getvalue($ddb_metric,$startdate,$enddate);
  echo "<td class='dashboardtd'>";

  // Value, contract, metric, team, date, submetric)
    list($bg,$fg) = targetcolor($value, 5, $ddb_metric, $ddb_team, $startdate, $submetric);
	  $rounding = gmr($ddb_metric);
    $symbol = gms($ddb_metric);

  // Value area
    echo "<span class='valuearea' style='color:#$fg;background-color:#$bg'>";
    echo round($value,$rounding) . $symbol;
    echo "</span>";

  // Target title
    echo "<span class='targettitle xtoggle' style='color:#$fg'>&ofcir;</span>";

  // Target area
    echo "<span class='targetarea xtoggle' style='color:#$fg;background-color:#$bg'>";
    $highorlow='high';if ($ddb_metric=='2'){$highorlow='low';}
    $tmptarget = gettarget(5,$ddb_metric,$ddb_team,$startdate,$highorlow,$submetric);
    echo $tmptarget;
    echo "</span>";

  // Delta area
  if ($value!=''){
		echo "<span class='targetdeltatitle xtoggle' style='color:#$fg'>&Delta;</span>";
    echo "<div class='targetdelta xtoggle' style='color:#$fg;background-color:#$bg";
    $tmpdelta = $value - $tmptarget;
    $tmpdelta=$tmpdelta/100;
    echo "'>";
    echo round($tmpdelta*100,1);
    echo "%</div>";
}

  $tmpstartdate = date("Y-m-d",strtotime("-1 week +1 day"));
  $tmpenddate = $today;
  $sevendays = getvalue($ddb_metric,$tmpstartdate,$tmpenddate);
  list($tbg,$tfg) = targetcolor($sevendays, 5, $ddb_metric, $ddb_team, $startdate, $submetric);


  // 7 days rolling title
  echo "<span class='sevendaystitle xtoggle'";
  echo " style='color:#{$fg};'>";
  echo "7d";
  echo "</span>";

  // Last week value area
  echo "<span class='sevendaysarea xtoggle'";
  echo " style='color:#{$tfg};'";
  echo ">";
  echo round($sevendays,$rounding) . $symbol;
  echo "</span>";

  // Last week delta area
  echo "<div class='sevendaysdelta xtoggle delta";
  $tmpdelta = $sevendays - $value;
  if ($tmpdelta<0){$goodbad = 'bad';}  else{$goodbad = 'good';}
  echo "$goodbad'";
  echo "><div class='{$goodbad}arrow'>

  </div>";
  echo round($tmpdelta,0);
  echo "%</div>";
  echo "</td>";
}

function exceldate_to_unixedate($exceldate){ return ($exceldate - 25569) * 86400; }
function unixdate_to_exceldate($unixdate){ return 25569 + ($unixdate / 86400); }
function countsurveys($username){
	global $db, $teamdefinition,$sqldater;
	$surveys=0;
	$sql = "SELECT COUNT(*) as id FROM raw_data  $sqldater $teamdefinition AND teammate_nt_id='$username' ";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	$surveys=$row[id];
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
		$sql = "INSERT INTO raw_data (";
		for ($b=1;$b<=$columnnumber;$b++){
			$sql .=	"{$column[$b]},";
		}
		$sql = substr($sql,0,-1);
		$sql .= ") VALUES(";
		for ($b=1;$b<=$columnnumber;$b++){
			$data[$a][$b] = $db->real_escape_string($data[$a][$b]);
			$sql .=	"'{$data[$a][$b]}',";
		}
		$sql = substr($sql,0,-1);
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
	global $db;
	echo " Processing AHT report...";
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
					$column[$columnnumber] = str_replace("'","",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(",","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace(".","_",$column[$columnnumber]);
					$column[$columnnumber] = str_replace("-","_",$column[$columnnumber]);
					if ($column[$columnnumber]=='Month'){$monthcolumn = $columnnumber;}
					if ($column[$columnnumber]=='NTID'){$ntidcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Name'){$queuenamecolumn = $columnnumber;}
				}
				elseif($rownumber>6) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
			}
			$rownumber++;
		}

		// Add the columns to the database table.
		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table prt060data add {$column[$a]} text";	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}	}

		// Add the data.
		for ($a = 7; $a<$rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id FROM prt060data WHERE month = '{$data[$a][$monthcolumn]}' AND ntid = '{$data[$a][$ntidcolumn]}' AND queue_name = '{$data[$a][$queuenamecolumn]}' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM prt060data WHERE id = '{$row[id]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			$sql="INSERT INTO prt060data (";for ($b=1;$b<=$columnnumber;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
			for ($b=1;$b<=$columnnumber;$b++){$data[$a][$b]=$db->real_escape_string($data[$a][$b]);$sql.="'{$data[$a][$b]}',";}$sql=substr($sql,0,-1);$sql.=')';
 			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	echo "Done.";
	setsetting('prt060pdata',date("Y-m-d H:i:s"));
}
function processprt073report($filename) {
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
					if ($column[$columnnumber]=='Queue_Silo'){$silocolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Skillset'){$ntidcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Name'){$queuenamecolumn = $columnnumber;}
				}
				elseif($rownumber>6) { $data[$rownumber][$columnnumber] = trim($cell->getValue()); }
			}
			$rownumber++;
		}

		// Add the columns to the database table.
		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table prt073data add {$column[$a]} text";	if(!$result=$db->query($sql)){
			//cl($sql);cl($db->error);
		}	
		}

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
			if (($data[$a][$queuenamecolumn] == 'Total') && (trim($data[$a][$silocolumn]) == 'Customer Solutions')) {
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
					if ($column[$columnnumber]=='Date'){$monthcolumn = $columnnumber;}
					if ($column[$columnnumber]=='NTID'){$ntidcolumn = $columnnumber;}
					if ($column[$columnnumber]=='Queue_Name'){$queuenamecolumn = $columnnumber;}
				}
				elseif($rownumber>6) { $data[$rownumber][$columnnumber] = $cell->getValue(); }
			}
			$rownumber++;
		}

		// Add the columns to the database table.
		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table prt058data add {$column[$a]} text";	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}	}

		// Add the data.
		for ($a = 7; $a<$rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id FROM prt058data WHERE date = '{$data[$a][$monthcolumn]}' AND ntid = '{$data[$a][$ntidcolumn]}' AND queue_name = '{$data[$a][$queuenamecolumn]}' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM prt058data WHERE id = '{$row[id]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			$sql="INSERT INTO prt058data (";for ($b=1;$b<=$columnnumber;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
			for ($b=1;$b<=$columnnumber;$b++){$data[$a][$b]=$db->real_escape_string($data[$a][$b]);$sql.="'{$data[$a][$b]}',";}$sql=substr($sql,0,-1);$sql.=')';
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
				$sql="INSERT INTO vm055_data (";for ($b=0;$b<=$colos;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
				$sql .= "'$teamo','{$rawdata[$a][5]}',";
				for ($c = $startcol;$c <= $endcol; $c++) { $ddb = $db->real_escape_string($rawdata[$datacolumn[$teamo][$rawdata[$a][5]]][$c]);$sql.="'$ddb',";}
				$sql=substr($sql,0,-1);
				$sql.=")";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
		}
	}
	setsetting('vm055data',date("Y-m-d H:i:s"));
	echo "<script>location.href='?';</script>";
}

function addleftchoice($name) {
  global $_GET, $showleftchoicetext, $path;
  echo "<div id='{$name}' onmousedown=\"location.href='?a={$name}';\" ";
  if ($showleftchoicetext) {
    echo "class='leftchoice";
    if ($_GET[a]==$name) {echo " leftchoiceselected";}
    echo "'><img src='{$path}images/{$name}.png'>";
    echo ucfirst($name);
  }
  else {
    echo "class='leftchoicesmall";
    if ($_GET[a]==$name) {echo " leftchoiceselectedsmall";}
    echo "' title='" . ucfirst($name) . "'>";
    if ($name == 'dashboard') { $name = 'bar-chart'; }
    if ($name == 'surveys') { $name = 'commenting-o'; }
    if ($name == 'settings') { $name = 'cog'; }
    if ($name == 'bonus') { $name = 'money'; }
    if ($name == 'targets') { $name = 'dot-circle-o'; }
    if ($name == 'certification') { $name = 'certificate'; }
    if ($name == 'trends') { $name = 'line-chart'; }
    if ($name == 'seating') { $name = 'th-large'; }
    echo "<div class='fa fa-2x fa-$name'></div>";
  }
  echo "</div>";
}

function dd($datestring){
	$today = date("Y-m-d");
	$datepart = substr($datestring,0,10);
	$timepart = substr($datestring,10,6);
	if ($datepart == $today) { $datepart = "today";}
	return '<b>'.$datepart . "</b> @ ". $timepart;
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
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
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
function guessteam($username){
	global $db;

	$sql = "SELECT team_id FROM users_teams WHERE teammate_nt_id = '$username' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[team_id]=="")	{
		$manager = getmanager($username);
		$sql = "SELECT raw_data_column FROM team_data_definitions LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		$column=$row[raw_data_column];
		$sql = "SELECT $column FROM raw_data WHERE team_leader_name = '$manager' ORDER by teammate_contact_date ASC LIMIT 10";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		while($row=$result->fetch_assoc()){
  		$queue[$row[$column]]++;
  	}
  	arsort($queue);
		$quenumb = 0;
		foreach($queue as $que => $queuenum) { $topqueue[$quenumb] = $que; $quenumb++; }
		$sql = "SELECT team_id FROM team_data_definitions WHERE raw_data_column = '$column' AND raw_data_data = '{$topqueue[0]}' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();

		$sql = "INSERT INTO users_teams (team_id, teammate_nt_id) VALUES('{$row[team_id]}','$username')";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	}
	return $row[team_id];
}
function getteamname($team_id){
	global $db;
	$sql = "SELECT team_name FROM teams WHERE id = '$team_id'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[team_name];
}
function targetcolor($value, $contract, $metric, $team, $date, $submetric) {
	if ($value==='--'){return '';}
	if ($metric===''){return '';}
	if ($team){
		global $db;
		// There should only be one match.
		$sub=" AND submetric = '$submetric'";
		$sql = "SELECT target_color,target_textcolor,target_value_low,target_value_high FROM targets WHERE target_value_low <= '$value' AND target_value_high >= '$value-0.01' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND target_start_date <= '$date' AND target_stop_date >= '$date' $sub LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[target_color]===''){
			$date = "0000-00-00 00:00:00";
			$sql = "SELECT target_color,target_textcolor,target_value_low,target_value_high FROM targets WHERE target_value_low <= $value AND target_value_high >= $value-0.01 AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND target_start_date <= '$date' AND target_stop_date >= '$date' $sub LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
		}
		if (($metric==2) or ($metric==6) or ($metric==17)) {
			$targetdiff = $row[target_value_low] - $value;
		}
		else {
			$targetdiff = $row[target_value_high] - $value;
		}
		$targetdiff = $targetdiff / 100;
		if (($metric==2) or ($metric==6) or ($metric==17)) {
			if (($row[target_color]=='ff0000') && ($targetdiff > -0.05)) {
				return array("ffef0f","d35400");
			}
		}
		else {
			if (($row[target_color]=='ff0000') && ($targetdiff < 0.05)) {
				return array("ffef0f","d35400");
			}
		}
		return array($row[target_color],$row[target_textcolor]);
	}
	else {return '';}
}
function gettarget($contract,$metric,$team,$date,$highorlow,$submetric){
	if($team){
		global $db;
		if ($submetric){$submetric="AND submetric='$submetric'";}
		else {
			$submetric = "AND submetric=''";
		}
		$tvhol = 'target_value_' . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = 'ff0000' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){
			$sql = "SELECT $tvhol FROM targets WHERE target_color = 'ff0000' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_start_date = '0000-00-00 00:00:00' AND target_stop_date = '0000-00-00 00:00:00' AND target_team_id='$team' $submetric LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[$tvhol]==''){return 0;}
		}
		return $row[$tvhol];
	}
	return 0;
}
function getgoodtarget($contract,$metric,$team,$date,$highorlow){
	if($team){
		global $db;
		$submetric = "AND submetric=''";
		$tvhol = 'target_value_' . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '00b050' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric ORDER by $tvhol DESC LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){
			$sql = "SELECT $tvhol FROM targets WHERE target_color = '00B050' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_start_date = '0000-00-00 00:00:00' AND target_stop_date = '0000-00-00 00:00:00' AND target_team_id='$team' $submetric LIMIT 1";
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
		global $db;
		$submetric = "AND submetric=''";
		$tvhol = "target_value_$highorlow";// . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '009030' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric ORDER by $tvhol DESC LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		return $row[$tvhol];
	}
	return 0;
}
function getsubtarget($contract,$metric,$team,$date,$submetric){
	global $db;
	$hol='low';
	if ($metric==2){$hol='high';}
	$tvhol = "target_value_$hol";
	$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '00b050' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND submetric='$submetric' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
 	$row=$result->fetch_assoc();
	if ($row[$tvhol]==''){
		$sql = "SELECT $tvhol FROM targets WHERE target_color = '00b050' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND submetric='$submetric' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	 	$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){ return 0; }
	}
	return $row[$tvhol];
}
function surveycount($optionalmonth){
	global $db,$teamdefinition,$sqldater, $currentyear, $currentmonth;
	$surveys=0;
  if ($optionalmonth == "previous month") {
    $tmpyear = $currentyear;
    $tmpmonth = $currentmonth-1;
    if ($tmpmonth < 1){$tmpmonth = 12; $tmpyear--; }
    $tmpdate = unixdate_to_exceldate(mktime(0,0,0,$tmpmonth,1,$tmpyear));
    $lastday = getlastday($tmpyear,$tmpmonth);
    $tmpdater = unixdate_to_exceldate(mktime(23,59,59,$tmpmonth,$lastday,$tmpyear));
    $tmpsqldater = "WHERE Teammate_Contact_Date > $tmpdate AND Teammate_Contact_Date < $tmpdater";
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
    $tmpsqldater = "WHERE Teammate_Contact_Date > $tmpdate AND Teammate_Contact_Date < $tmpdater";
  }
  if ($tmpsqldater=='') {$tmpsqldater = $sqldater; }
	$sql = "SELECT COUNT(*) as id FROM raw_data $tmpsqldater $teamdefinition";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	$surveys=$row[id];
	return $surveys;
}
function vm($vm_metric, $vm_team, $vm_startdate) {
	global $db, $monthy;
	$vm_startdate_year = substr($vm_startdate,0,4);
	$vm_startdate_month = substr($vm_startdate,5,2);
	$vm_startdate_month = $monthy[$vm_startdate_month];
	$vm_date = $vm_startdate_month.'-'.$vm_startdate_year;
	$mul=1;

	    if ($vm_metric == "2"){$vm_metric='aht';}
	elseif ($vm_metric == "8"){$vm_metric='psl';}
	elseif ($vm_metric == "13"){$vm_metric='esl';}
	elseif ($vm_metric == "12"){$vm_metric='pvol';}
	elseif ($vm_metric == "14"){$vm_metric='evol';}

	    if ($vm_metric == "aht"){$selector = 'Total_AHT_secs';}
	elseif ($vm_metric == "psl"){$selector = 'Phone_SLperc';}
	elseif ($vm_metric == "esl"){$selector = 'Email_SLperc';}
	elseif ($vm_metric == "pvol"){$selector = 'Phone_Answered';}
	elseif ($vm_metric == "evol"){$selector = 'Email_Worked';}
	elseif ($vm_metric == "ptr"){$selector = 'Phone_Transfer_Rate';}
	elseif ($vm_metric == "etr"){$selector = 'Email_Transfer_Rate';}
  if ($vm_metric == 'psl') { $mul = 100; }
  else if($vm_metric == 'esl') { $mul = 100; }

  if ($vm_metric == "17") {
    $sql = "SELECT team_prt073 FROM teams WHERE id = '$vm_team' LIMIT 1";
    if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  	$row=$result->fetch_assoc();
    $vm_team = $row['team_prt073'];
    $selector = 'phone_rcr';
    $mul = 100;
    $sql = "SELECT $selector FROM prt073data WHERE queue_skillset = '{$vm_team}' AND month = '{$vm_date}' LIMIT 1";
  }
  else {
    $sql = "SELECT vmdata_team FROM teams WHERE id = '$vm_team' LIMIT 1";
    if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  	$row=$result->fetch_assoc();
    $vm_team = $row[vmdata_team];
    $sql = "SELECT $selector FROM vm055_data WHERE team = '{$vm_team}' AND month = '{$vm_date}' LIMIT 1";
  }
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  $row=$result->fetch_assoc();
  if ($row[$selector]==''){return "0";}
  return $row[$selector]*$mul;
}

// Get combined values for a specific date interval.
function getvalue($metric,$valuestartdate,$valueenddate){
	global $db,$teamdefinition,$contract;
	$valuestartdate_year = substr($valuestartdate,0,4);
	$valuestartdate_month = substr($valuestartdate,5,2);
	$valuestartdate_day = substr($valuestartdate,8,2);
	$valueenddate_year = substr($valueenddate,0,4);
	$valueenddate_month = substr($valueenddate,5,2);
	$valueenddate_day = substr($valueenddate,8,2);
	$valuestartdate_excel = unixdate_to_exceldate(mktime(0,0,0,$valuestartdate_month,$valuestartdate_day,$valuestartdate_year));
	$valueenddate_excel = unixdate_to_exceldate(mktime(23,59,59,$valueenddate_month,$valueenddate_day,$valueenddate_year));
	$valuesqldater = " WHERE Teammate_Contact_Date >= $valuestartdate_excel";
	$valuesqldater .= " AND Teammate_Contact_Date <= $valueenddate_excel";
	$sql = "SELECT external_survey_id FROM raw_data".$valuesqldater . $teamdefinition;
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$surveys = 0;
  while($row=$result->fetch_assoc()){ $surveys++;}
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
    //echo "100 * $topperformer / $surveys) - (100 * $bottomperformer / $surveys) = $value<br>";
  }
	elseif($metric=='3'){ $value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metric=='5'){ $value = round(($kdi_top/$kdi*100),2);}
	elseif($metric=='15'){$value = round(($att_sum/$att),0);}
  elseif($metric=='16'){$value = round(($ccc_sum/$ccc*100),2);}
	else { $value = 0; }
	if ($contra>0){return $value;}
	else{return '0';}
}

// Get any values with a free filter.
/*
  Metric:
    - NPS

*/
function sv($metric,$filter,$surveys,$sqldaterextra){
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
  $npssurveys=0;
	while($row=$result->fetch_assoc()){
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
	if($metric=='4'){$value = round((100*$topperformer/$npssurveys)-(100*$bottomperformer/$npssurveys),2);}
	elseif($metric=='3'){ $value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metric=='5'){ $value = round(($kdi_top/$kdi*100),2);}
	elseif($metric=='15'){$value = round(($att_sum/$att),0);}
  elseif($metric=='16'){$value = round(($ccc_sum/$ccc*100),2);}
	else { $value = 0; }
	if ($contra>0){return $value;}
	else{return '--';}
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
	if($metric=='4'){$value = round((100*$topperformer/$npssurveys)-(100*$bottomperformer/$npssurveys),2);}
	elseif($metric=='3'){ $value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metric=='5'){ $value = round(($kdi_top/$kdi*100),2);}
	elseif($metric=='15'){$value = round(($att_sum/$att),0);}
  elseif($metric=='16'){$value = round(($ccc_sum/$ccc*100),2);}
	else { $value = 0; }
	if ($contra>0){return array($value,$contra);}
	else{return '0';}
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
	$answered=0;$worked=0;$eaht=0;$paht=0;$montha = substr($monthdate,5,2);$montha=$monthy[$montha];$yeara = substr($monthdate,0,4);
	$aht=0;
	if ($what==2){$what='aht';}
	if ($what==17){$what='rcr';}
	if ($what==6){$what='tr';}
	if ($what==14){$what='worked';}
	if ($what==12){$what='answered';}
	$sql = "SELECT phone_rcr,transfer_rate,total_aht_secs,contacts_handled,ntid,queue_name,phone_answered,phone_aht_secs,email_worked,email_aht_secs FROM prt060data WHERE month='$montha-$yeara' $ahtteamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}

	$contra = 0;
	$counto=0;
	$counta=0;
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
			$counto+=$row[transfer_rate]*$row[contacts_handled];
			$counta+=$row[contacts_handled];
		}
		elseif ($what == 'rcr') {
			$counto+=$row[phone_rcr]*$row[phone_answered];
			$counta+=$row[phone_answered];
		}
	}
	$counto = intval($counto);
	$counta = intval($counta);

	if (($what=='paht') or ($what=='eaht') or ($what=='aht') or ($what=='tr') or ($what=='rcr')) { $counto=$counto / $counta; }
	if ($contra>0){
		if ($counta>0) {
			// TODO figure out why it becomes 00...
			if ($counto == '00') { $counto = 0; }
			return $counto;
		}
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
$theme = gettheme();
echo "
<!DOCTYPE html>
<html>
	<head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0, minimum-scale=1.0'>
		<script src='{$path}sorttable.js'></script>
		<script src='{$path}gold.js'></script>
    <script src='https://use.fontawesome.com/77fac2bb57.js'></script>
		<link rel=stylesheet href='{$theme}.css''>
		<link href='https://fonts.googleapis.com/css?family=Lato|Raleway' rel='stylesheet'>
    <title>{$application_name}</title>
	</head>
	<body>";
if ($_POST[a]=='upload'){
	if(isset($_FILES['filedata'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedata"]["name"]);
			if (move_uploaded_file($_FILES["filedata"]["tmp_name"], $target_file)) {
				echo "The file ". basename( $_FILES["filedata"]["name"]). " has been uploaded.";
				processreport($target_file);
				//echo "<script>location.href='./';</script>";
			}
			else { echo "Sorry, there was an error uploading your file."; }
	}
}
if ($_POST[a]=='uploadaht'){
	if(isset($_FILES['filedataaht'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedataaht"]["name"]);
			if (move_uploaded_file($_FILES["filedataaht"]["tmp_name"], $target_file)) {
				echo "The file ". basename( $_FILES["filedataaht"]["name"]). " has been uploaded.";
				processahtreport($target_file);
				//echo "<script>location.href='./';</script>";
			}
			else {
				echo "Sorry, there was an error uploading your PRT060P file.";
			}
	}
}
if ($_POST[a]=='uploadprt073'){
	if(isset($_FILES['filedataprt073'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedataprt073"]["name"]);
			if (move_uploaded_file($_FILES["filedataprt073"]["tmp_name"], $target_file)) {
				echo "The file prt073p ". basename( $_FILES["filedataprt073"]["name"]). " has been uploaded.";
				processprt073report($target_file);
				//echo "<script>location.href='./beta.php';</script>";
			}
			else {
				echo "Sorry, there was an error uploading your PRT0073 file.";
			}
	}
}

if ($_POST[a]=='uploadprt'){
	if(isset($_FILES['filedataprt'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedataprt"]["name"]);
			if (move_uploaded_file($_FILES["filedataprt"]["tmp_name"], $target_file)) {
				echo "The file prt058p ". basename( $_FILES["filedataprt"]["name"]). " has been uploaded.";
				processprtreport($target_file);
				//echo "<script>location.href='./beta.php';</script>";
			}
			else {
				echo "Sorry, there was an error uploading your PRT058P file.";
			}
	}
}
if ($_POST[a]=='uploadvm055p'){
	if(isset($_FILES['filedatavm'])){
		$target_dir = "reports/";
		$target_file = $target_dir . basename($_FILES["filedatavm"]["name"]);
			if (move_uploaded_file($_FILES["filedatavm"]["tmp_name"], $target_file)) {
				echo "The file ". basename( $_FILES["filedatavm"]["name"]). " has been uploaded.";
				processvmreport($target_file);
				//echo "<script>location.href='./beta.php';</script>";
			}
			else {
				echo "Sorry, there was an error uploading your VM055p file.";
			}
	}
}

// Are we logged in?
if ($_POST['newpassword']) {
	$newhash = generatehash($_POST['newpassword']);
	$sql = "UPDATE users SET user_hash = '".$newhash."' WHERE user_id = '" . $_POST['userid'] . "'";
	$result = $db->query($sql);
	$sql = "UPDATE users SET user_new = '0' WHERE user_id = '" . $_POST['userid'] . "'";
	$result = $db->query($sql);
	echo "<script>location.href='?';</script>";
}
if ($_POST['username']) {
	if (validatepassword($_POST['username'],$_POST['password'])) {
		//$_SESSION['username'] = $_POST['username'];
    $_SESSION['user_id'] = getuserid($_POST['username']);
		$_SESSION['logged_in'] = true;
		echo "<script>location.href='?';</script>";
	}
	else {
		$login_error = "Incorrect username and/or password.";
	}
}
// Allow new users to set a password
if ($_GET['n']) {
	$sql = "SELECT user_new FROM users WHERE user_id = '".$_GET['n']."'  LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row = $result->fetch_assoc();
	if($row['user_new'] == '1') { showsetnewpassword(); }
	else { showlogin(); }
}
else {
	if (!$_SESSION['logged_in']) { // Not logged in
		showlogin();
	}
	else { // Logged in
    	// Left area
		echo "<div id='topbar'>";
		$a=$_GET[a];
		$b=$_GET[b];
		$c=$_GET[c];
		$d=$_GET[d];
		$e=$_GET[e];
		$f=$_GET[f];
    $un=getusername($uid);
    $sql = "SELECT user_showleftchoicetext FROM users WHERE user_id={$uid} LIMIT 1";
    if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
    $showleftchoicetext = $row[user_showleftchoicetext];
    $small = ''; if (!$showleftchoicetext) {$small = 'small';}
		$sql = "SELECT user_preferred_team FROM users WHERE user_id=$uid LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
		$prefteam = $row[user_preferred_team];
		if ($_GET[prefteam]){
			$prefteam = $_GET[prefteam];
			$sql = "UPDATE users SET user_preferred_team = '{$_GET[prefteam]}' WHERE user_id='$uid' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
		$sql = "SELECT user_startdate,user_enddate FROM users WHERE user_id=$uid";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
		$startdate=$row[user_startdate]; $startdate=substr($startdate,0,10);
		$enddate=$row[user_enddate]; $enddate=substr($enddate,0,10);
		if ($startdate>$enddate){$startdate=$enddate;}
		if ($_GET[startdate]){
			$startdate=$_GET[startdate]; $enddate=$_GET[enddate];
		}
		$team=$_GET[team];
		if ($team==''){$team=$prefteam;}
		$currentmonth = date("m"); $currentyear = date("Y"); $currentday = date("d");
		$lastday=cal_days_in_month(CAL_GREGORIAN,$currentmonth,$currentyear);
		if ($startdate==''){
			if ($currentday<10){
				$startdate_month = str_pad($currentmonth-1,2,"0",STR_PAD_LEFT);
				if ($enddate==''){$enddate="$currentyear-$startdate_month-$lastday";}
			}
			$startdate="$currentyear-$startdate_month-01";
		}
		if ($enddate==''){ $enddate="$currentyear-$currentmonth-$lastday"; }
		$startdate_year = substr($startdate,0,4); $startdate_month = substr($startdate,5,2); $startdate_day = substr($startdate,8,2);
		$enddate_year = substr($enddate,0,4); $enddate_month = substr($enddate,5,2); $enddate_day = substr($enddate,8,2);
		$startdate_excel = unixdate_to_exceldate(mktime(0,0,0,$startdate_month,$startdate_day,$startdate_year));
		$enddate_excel = unixdate_to_exceldate(mktime(23,59,59,$enddate_month,$enddate_day,$enddate_year));
		$sqldater = " WHERE Teammate_Contact_Date > $startdate_excel";
		$sqldater .= " AND Teammate_Contact_Date < $enddate_excel";

		$sql = "UPDATE users SET user_startdate = '$startdate' WHERE user_id='$uid'"; if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$sql = "UPDATE users SET user_enddate = '$enddate' WHERE user_id='$uid'"; if(!$result=$db->query($sql)){cl($sql);cl($db->error);}

		$lastmonth = date("Y-m-d", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
        $lastmonthyear = date("Y", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
        $lastmonthmonth = date("m", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
        $lastmonthday = date("d", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
		$lastend = date("Y-m-d", mktime(0, 0, 0, $lastmonthmonth, cal_days_in_month(CAL_GREGORIAN,$lastmonthmonth,$lastmonthyear), $lastmonthyear));

		$nextmonth = date("Y-m-d", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
      	$nextmonthyear = date("Y", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
        $nextmonthmonth = date("m", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
        $nextmonthday = date("d", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
		$nextend = date("Y-m-d", mktime(0, 0, 0, $nextmonthmonth, cal_days_in_month(CAL_GREGORIAN,$nextmonthmonth,$nextmonthyear), $nextmonthyear));

        $previous_week = strtotime("-1 week + 1 day");
        $start_week = strtotime("last monday midnight",$previous_week);
        $end_week = strtotime("next sunday",$start_week);
        $lastweekstart = date("Y-m-d",$start_week);
        $lastweekend = date("Y-m-d",$end_week);

		echo "<div title='Previous month' id='prevmonth$small'><a href='?a=$a&b=$b&c=$c&d=$d&team=$team&startdate=$lastmonth&enddate=$lastend'><div class='fa fa-3x fa-arrow-left'></div></a></div>";
  	echo "<div title='Next month' id='nextmonth$small'><a href='?a=$a&b=$b&c=$c&d=$d&team=$team&startdate=$nextmonth&enddate=$nextend'><div class='fa fa-3x fa-arrow-right'></div></a></div>";

		// Pick preferred team
		echo "<div class='preferred-team-title'>Team:</div>";
		echo "<div id='preferred_team'><select onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d$extraurl&startdate=$startdate&enddate=$enddate&team=\" + this.value + \"&prefteam=\" + this.value;' name='prefteam'>";
        echo "<option value='-1'>Belfast</option>";
		$sql = "SELECT id,team_name FROM teams LIMIT 50";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        while($row=$result->fetch_assoc()){
			echo "<option ";
			if ($prefteam==$row[id]){
				echo "selected ";
			}
			echo "value={$row[id]}>{$row[team_name]}</option>";
		}
		echo "</select></div>";
    echo "<div id='lastsevendays'><a href='?a=$a&b=$b&c=$c&d=$d&team=$team&startdate=$lastweekstart&enddate=$lastweekend&sub=Last%20Week'>Last Week</a></div>";
		echo "<div id='currentmonth'><a href='?a=$a&b=$b&c=$c&d=$d&team=$team&startdate=$currentyear-$currentmonth-01&enddate=$currentyear-$currentmonth-$lastday&sub=Current%20Month'>Current Month</a></div>";
		echo "<div id='startdatepicker'>Start date: <input id=startdate name=startdate type=date value='$startdate' onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d&team=$team&enddate=$enddate&startdate=\"+this.value;'></div>";
		echo "<div id='enddatepicker'>End date: <input id=enddate name=enddate type=date value='$enddate' onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d&team=$team&startdate=$startdate&enddate=\"+this.value;'></div>";
		$teamdefinition='';
		if ($team){
			$sql = "SELECT * FROM team_data_definitions WHERE team_id = $team";
        	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$definitions = 0;
        	while($row=$result->fetch_assoc()){
				if ($definitions){$teamdefinition.=' OR ';}
				$teamdefinition .= $row[raw_data_column] . "='{$row[raw_data_data]}'";
				$definitions++;
			}
			if ($teamdefinition){$teamdefinition=" AND (".$teamdefinition . ")";}
			$sql = "SELECT * FROM team_aht_definitions WHERE team_id = $team";
        	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$ahtdefinitions = 0;
        	while($row=$result->fetch_assoc()){
				if ($ahtdefinitions){$ahtteamdefinition.=' OR ';}
				$ahtteamdefinition .= $row[ahtreport_data_column] . "='{$row[ahtreport_data_data]}'";
				$ahtdefinitions++;
			}
			if ($ahtteamdefinition){$ahtteamdefinition=" AND (".$ahtteamdefinition . ")";}
		}
		echo "</div>";
    echo "<div id='div_left_area$small'>";
		addleftchoice("dashboard");
		addleftchoice("surveys");
		addleftchoice("bonus");
		//addleftchoice("masters");
		//addleftchoice("qds");
		addleftchoice("targets");
		addleftchoice("trends");
		addleftchoice("certification");
		addleftchoice("seating");
  	addleftchoice("settings");
  	//addleftchoice("mystats");
  	//addleftchoice("metrics");
  	//addleftchoice("employees");
		//addleftchoice("teams");
		//addleftchoice("contracts");
  	//addleftchoice("calendar");
		echo "</div>";
		echo "<div class='topleftcorner'>";
		echo "</div>";
		echo "<div id='leftinfo'>Updated:<br> ";
		$today = date("Y-m-d");
		$medalliaupdated = getsetting('medalliadata');
		$vm055updated = getsetting('vm055data');
		$prt060pupdated = getsetting('prt060pdata');
		$medalliaupdated = getsetting('medalliadata');
  	$sub = $_GET[sub];
		echo "Platform: " . dd("$application_version_major $application_version_minor") . "<br>";
		echo "Medallia: " . dd($medalliaupdated) . "<br>";
		echo "VM055: " . dd($vm055updated) . "<br>";
		echo "PRT060p: " . dd($prt060pupdated);
		echo "</div>";
  	echo "<div id='div_right_area$small'>";
    if ($a=='') { $a = 'dashboard'; }
    if ($a) {
      echo "<div id='area_title$small'><a href='?a=$a'>".ucfirst($a)."</a></div>";
      echo "<div id='area_body'>";

			if($a=='seating'){
				$width = 16;
				$height = 20;

				$sql = "SELECT * FROM seating";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while ($row=$result->fetch_assoc()) {
					$xcoord = $row['xcoord'];
					$ycoord = $row['ycoord'];
					$seatingdata[$xcoord][$ycoord] = $row;
					if ($row['rotation'] < 4) {
						$seatcount[$row[teamid]]++;
					}
				}
				arsort($seatcount);
				echo "<div class='seating-legend'>";
				foreach ($seatcount as $team => $key) {
					$tmpname = sqr("SELECT team_name FROM teams WHERE id = '$team'")['team_name'];
					if ($tmpname == '') { $tmpname = 'Other'; }
					echo "$tmpname = $key<br>";
				}
				echo "</div>";
				echo "<div class='seating-tools'>";
				echo "Design <button id='desk'>Desk</button>";
				echo "<button id='wall'>Wall</button>";
				echo "<button id='door'>Door</button>";
				echo "<button id='issues'>Issues</button>";
				echo "Assign ";
				echo "team:<br> <select id='assignteam'>";
				echo "<option value='-1'>--</option>";
				$sql = "SELECT id,team_name FROM teams ORDER BY team_name ASC";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while ($row=$result->fetch_assoc()) {
					echo "<option value={$row[id]}>{$row[team_name]}</option>";
				}
				echo "</select>";
				echo "Assign person:<br> <select id='assignperson'>";
				echo "</select>";
				echo "<button id='manuallyAssign'>Manually assign</button>";
				echo "<button id='unassign'>Unassign</button>";
				echo "</div>";
				echo "<table class='seating'>";
				echo "<tr><td></td>";
				for ($w = 1; $w <= $width; $w++) {
					echo "<td>$w</td>";
				}
				echo "</tr>";
				$c_seatable = 'seatable';
				for ($h = 1; $h <= $height; $h++) {
					echo "<tr>";
					echo "<td>$h</td>";
					for ($w = 1; $w <= $width; $w++) {
						if ($seatingdata[$w][$h]['xcoord']>0) {
							if ($seatingdata[$w][$h]['rotation'] == 0) {
								$seatclass = 'seatedbottom';
							}
							else if ($seatingdata[$w][$h]['rotation'] == 1) {
								$seatclass = 'seatedleft';
							}
							else if ($seatingdata[$w][$h]['rotation'] == 2) {
								$seatclass = 'seatedtop';
							}
							else if ($seatingdata[$w][$h]['rotation'] == 3) {
								$seatclass = 'seatedright';
							}
							else if ($seatingdata[$w][$h]['rotation'] == 4) {
								$seatclass = 'wall';
							}
							else if ($seatingdata[$w][$h]['rotation'] == 5) {
								$seatclass = 'door';
							}
							else if ($seatingdata[$w][$h]['rotation'] == 6) {
								$seatclass = 'issues';
							}
							if ($seatingdata[$w][$h]['teamid']>0) {
								$seatclass .= ' team' . $seatingdata[$w][$h]['teamid'];
							}
						}
						else { $seatclass = $c_seatable; }
						echo "<td class='$seatclass' id='seat-$w-$h'>{$seatingdata[$w][$h]['ntid']}</td>\n";
					}
					echo "</tr>";
				}
			}
			if($a=='oldbonus'){
				if ($team>0){
					$view_startdate_month=$startdate_month;
					$view_startdate_year=$startdate_year;
					$view_enddate_year=$enddate_year;
					$view_enddate_month=$enddate_month;
					$view_startdate_day="01";
					$view_enddate_day = cal_days_in_month(CAL_GREGORIAN,$view_startdate_month,$view_enddate_year);
					$view_startdate = "$view_startdate_year-$view_startdate_month-01";
					$view_enddate = "$view_startdate_year-$view_startdate_month-$view_enddate_day";
					if ($enddate_day != $view_enddate_day){
						echo "<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
					}
					if ($view_startdate_month != $view_enddate_month){
						echo "<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
					}
					echo "<h2>Month: " . $month[$view_startdate_month]." $view_startdate_year</h2>";
					echo "<form method=get>";
    			if(1) {
						echo "<table  class='sortable' id='bonustable' cellpadding=0 cellspacing=0>";
						echo "<thead><tr>
						<th class=rotated>Position</th>
						<th>Name</th>
						<th>Surname</th>
						<th class=rotated>Phone<br>Answered</th>
						<th class=rotated>Phone<br>Surveys</th>
						<th class=rotated>Phone AHT</th>
						<th class=rotated>Email Worked</th>
						<th class=rotated>Email Surveys</th>
						<th class=rotated>Email AHT</th>
						<th class=rotated>Phone KDI</th>
						<th class=rotated>RCR</th>
						<th class=rotated>Phone TR</th>
						<th class=rotated>Email KDI</th>
						<th class=rotated>Email TR</th>
						<th class=rotated>NPS</th>";
						echo "</tr></thead>";
						echo "<tbody>";
						$sql = "SELECT distinct teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition ORDER by teammate_nt_id ASC";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						$contract = 5;
						$teammate_counter=0;
						while($row=$result->fetch_assoc()){
							$teammate_counter++; $teammate[$teammate_counter]=$row[teammate_nt_id];
							$teammatename[$row[teammate_nt_id]]=$row[teammate_name];

							// Count emails.
							$sqlb="SELECT distinct external_survey_id FROM raw_data $sqldater $teamdefinition AND teammate_nt_id = '{$row[teammate_nt_id]}' AND queue_source_name LIKE '%EMAIL'";
							if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
							$survey_counter=0;
							while($rowb=$resultb->fetch_assoc()){$survey_counter++;}
							$bd[emailsurveys][$row[teammate_nt_id]]=$survey_counter;

							// Count phone
							$sqlb="SELECT distinct external_survey_id FROM raw_data $sqldater $teamdefinition AND 	teammate_nt_id = '{$row[teammate_nt_id]}' AND queue_source_name LIKE '%VOICE'";
							if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
							$survey_counter=0;
							while($rowb=$resultb->fetch_assoc()){$survey_counter++;}
							$bd[phonesurveys][$row[teammate_nt_id]]=$survey_counter;
						}
						$im=ismgr();

						// Bonus weighting
						$sql = "SELECT * FROM bonus_weighting";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						while($row=$result->fetch_assoc()){$bonus_weight[$row[metric_id]]=$row[weight];}
						for($x=1;$x<=$teammate_counter;$x++){
							if (guessteam($teammate[$x])==$team){
								$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%VOICE'";
								$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Voice'";
								$bd[phonekdi][$teammate[$x]] = 	sv(5,$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
								$bd[phoneanswered][$teammate[$x]] = av("answered",$startdate,$ahtdef);
								$bd[phonetr][$teammate[$x]] = av("tr",$startdate,$ahtdef)*100;
								$bd[phoneaht][$teammate[$x]] = av("paht",$startdate,$ahtdef);
								$bd[rcr][$teammate[$x]] = av(17, $startdate, $ahtdef)*100;

								$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%EMAIL'";
								$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Email'";
								$bd[emailkdi][$teammate[$x]] = sv(5,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
								$bd[emailworked][$teammate[$x]] = av("worked",$startdate,$ahtdef);
								$bd[emailtr][$teammate[$x]] = av("tr",$startdate,$ahtdef)*100;
								$bd[emailaht][$teammate[$x]] = av("eaht",$startdate,$ahtdef);

								$combinedsurveydefinition = " AND teammate_nt_id = '{$teammate[$x]}'";
								$bd[combinedsurveys][$teammate[$x]] = $bd[phonesurveys][$teammate[$x]] + 	$bd[emailsurveys][$teammate[$x]];
								$bd[nps][$teammate[$x]] = sv(4,$combinedsurveydefinition,$bd[combinedsurveys][$teammate[$x]]);

								$weight_paht = min(1.3, getsubtarget(5,2,$team,$view_startdate,'phone')/$bd[phoneaht][$teammate[$x]]);
								$weight_eaht = min(1.3, getsubtarget(5,2,$team,$view_startdate,'email')/$bd[emailaht][$teammate[$x]]);

								$weight_pkdi=0;  if ($bd[phonekdi][$teammate[$x]]){$weight_pkdi = min(1.3, max(0.7,$bd[phonekdi][$teammate[$x]]/getsubtarget(5,5,$team,$view_startdate,'phone')));}

								$weight_pcrrr=0; if ($bd[phonecrrr][$teammate[$x]]){$weight_pcrrr = min(1.3, max(0.7,$bd[phonecrrr][$teammate[$x]]/getsubtarget(5,3,$team,$view_startdate,'phone')));}

								$weight_pnps = 0;
								if (($bd[phonenps][$teammate[$x]]<0) or ($bd[phonenps][$teammate[$x]]>0) or ($bd[phonenps][$teammate[$x]]==0)){
									$weight_pnps = min(1.3, max(0.7,$bd[phonenps][$teammate[$x]]/getsubtarget(5,4,$team,$view_startdate,'phone')));
								}


								$weight_ekdi=0;  if ($bd[emailkdi][$teammate[$x]]){$weight_ekdi = min(1.3, max(0.7,$bd[emailkdi][$teammate[$x]]/getsubtarget(5,5,$team,$view_startdate,'email')));}
								$weight_ecrrr=0; if ($bd[emailcrrr][$teammate[$x]]){$weight_ecrrr = min(1.3, max(0.7,$bd[emailcrrr][$teammate[$x]]/getsubtarget(5,3,$team,$view_startdate,'email')));}

								$weight_enps=0;  if ($bd[emailnps][$teammate[$x]]){$weight_enps = min(1.3, max(0.7,$bd[emailnps][$teammate[$x]]/getsubtarget(5,4,$team,$view_startdate,'email')));}

								$combinedaht = ($weight_paht*$bd[phoneanswered][$teammate[$x]] + $weight_eaht*$bd[emailworked][$teammate[$x]]) / ($bd[phoneanswered][$teammate[$x]]+$bd[emailworked][$teammate[$x]]);
								$combinedkdi = ($weight_pkdi*$bd[phonesurveys][$teammate[$x]] + $weight_ekdi*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
								$combinedcrrr = ($weight_pcrrr*$bd[phonesurveys][$teammate[$x]] + $weight_ecrrr*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
								$combinednps = ($weight_pnps*$bd[phonesurveys][$teammate[$x]] + $weight_enps*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
								$bd[performancepoints][$teammate[$x]] = $combinedaht*$bonus_weight[2] + $combinedkdi*$bonus_weight[5] + $combinedcrrr*$bonus_weight[3] + $combinednps*$bonus_weight[4];

							}
						}
						arsort($bd[performancepoints]);
						$counti = 0;
						foreach($bd[performancepoints] as $tm => $points){
							if (guessteam($tm)!=$team){
            				}
            				else {
								$counti++;
								echo "<tr>";
								// Position
								echo "<td>$counti</td>";
								// Name
								$spacer = strpos($teammatename[$tm]," ");
								$name = substr($teammatename[$tm],0,$spacer);
								$surname = substr($teammatename[$tm],$spacer,strlen($teammatename[$tm])-$spacer);
								echo "<td class=tmname>$name</td>";
								echo "<td class=tmname>$surname</td>";
								// Phone answered
								echo "<td>{$bd[phoneanswered][$tm]}</td>";
								// Phone surveys
								echo "<td>{$bd[phonesurveys][$tm]}</td>";
								// Phone AHT
								list($bg,$fg)=targetcolor($bd[phoneaht][$tm], $contract, 2, $team, $view_startdate,"phone");
								echo "<td	 style='color:#$fg;background-color:#$bg'>";
								echo round($bd[phoneaht][$tm],0);
								echo "</td>";
								// Email Worked
								echo "<td>{$bd[emailworked][$tm]}</td>";
								// Email Surveys
								echo "<td>{$bd[emailsurveys][$tm]}</td>";

								// Email AHT
								list($bg,$fg)=targetcolor($bd[emailaht][$tm], $contract, 2, $team, $view_startdate,"email");
								echo "<td	 style='color:#$fg;background-color:#$bg'>";
								echo round($bd[emailaht][$tm],0);
								echo "</td>";

								// Phone KDI
								list($bg,$fg)=targetcolor($bd[phonekdi][$tm], $contract, 5, $team, $view_startdate,"phone");
								echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[phonekdi][$tm]}</td>";

								// RCR
								list($bg,$fg)=targetcolor($bd[rcr][$tm], $contract, 17, $team, $view_startdate,"phone");
								echo "<td	 style='color:#$fg;background-color:#$bg'>".round($bd[rcr][$tm],2)."%</td>";

								// Phone TR
								list($bg,$fg)=targetcolor($bd[phonetr][$tm], $contract, 6, $team, $view_startdate,"phone");
								echo "<td	 style='color:#$fg;background-color:#$bg'>".round($bd[phonetr][$tm],2)."%</td>";

								// Email KDI
								list($bg,$fg)=targetcolor($bd[emailkdi][$tm], $contract, 5, $team, $view_startdate,"email");
								echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[emailkdi][$tm]}</td>";

								// Email TR
								list($bg,$fg)=targetcolor($bd[emailtr][$tm], $contract, 5, $team, $view_startdate,"email");
								echo "<td	 style='color:#$fg;background-color:#$bg'>". round($bd[emailtr][$tm],2)."%</td>";

								// NPS
								echo "<td	style='color:#$fg;background-color:#$bg'>{$bd[nps][$tm]}</td>";
								echo "</tr>\n";

							}
						}
						echo "</tbody></table>";
						if(ismgr()){
							if ($b=='e'){
								echo "<input type=submit><input type=hidden name=c value='saved'><input type=hidden name=a value=$a><input type=hidden name=b value=$b>
								<input type=hidden name=tms value=$counti><input type=hidden name=month value='$startdate_year-$startdate_month'></form>";
							}
						}
        			}
				}
				else{echo "This view only works with a team selected.";}
			}
			if($a=='bonus2'){
				if ($team>0){
					if (ismgr()){echo "<a href='?a=$a&startdate=$startdate&enddate=$enddate&team=$team'>View</a> / "; echo "<a href='?a=$a&b=e&startdate=$startdate&enddate=$enddate&team=$team'>Edit</a>";}
					$view_startdate_month=$startdate_month;
					$view_startdate_year=$startdate_year;
					$view_enddate_year=$enddate_year;
					$view_enddate_month=$enddate_month;
					$view_startdate_day="01";
					$view_enddate_day = cal_days_in_month(CAL_GREGORIAN,$view_startdate_month,$view_enddate_year);
					$view_startdate = "$view_startdate_year-$view_startdate_month-01";
					$view_enddate = "$view_startdate_year-$view_startdate_month-$view_enddate_day";
					if ($enddate_day != $view_enddate_day){
						echo "<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
					}
					if ($view_startdate_month != $view_enddate_month){
						echo "<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
					}
					echo "<h2>Month: " . $month[$view_startdate_month]." $view_startdate_year</h2>";
					if(ismgr()){
						if ($_GET[c]=='saved'){
							for($x=1;$x<=$_GET[tms];$x++){
								$t="tm$x";
								$h="hw$x";
								$s="si$x";
								$tmname[$x]= $_GET[$t];
								$tmhw[$x]=$_GET[$h];
								$tmsi[$x]=$_GET[$s];
								$sql = "SELECT teammate_nt_id FROM hours WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}' LIMIT 1";
			        			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
								else {
									$row=$result->fetch_assoc();
									if ($row[teammate_nt_id]==''){
										$sql = "INSERT INTO hours (teammate_nt_id,date,worked_hours,sick_instances) VALUES('{$tmname[$x]}','{$_GET[month]}','{$tmhw[$x]}','{$tmsi[$x]}')";
										if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
									}
									else {
										$sql = "UPDATE hours SET worked_hours = '{$tmhw[$x]}' WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}'";
										if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
										$sql = "UPDATE hours SET sick_instances = '{$tmsi[$x]}' WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}'";
										if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
									}
								}
							}
						}
						echo "<form method=get>";
					}
        			if(1) {
						echo "<table  class='sortable' id='bonustable' cellpadding=0 cellspacing=0>";
						echo "<thead><tr>
						<th class=rotated>Position</th>
						<th>Name</th>
						<th>Surname</th>
						<th class=rotated>Phone<br>Answered</th>
						<th class=rotated>Phone<br>Surveys</th>
						<th class=rotated>Email Worked</th>
						<th class=rotated width=10>Email Surveys</th>
						<th class=rotated width=10>Phone AHT</th>
						<th class=rotated width=10>Email AHT</th>
						<th class=rotated width=10>Phone KDI</th>
						<th class=rotated width=10>Phone CrRR</th>
						<th class=rotated width=10>Phone NPS</th>
						<th class=rotated width=10>Email KDI</th>
						<th class=rotated width=10>Email CrRR</th>
						<th class=rotated width=10>Email NPS</th>
      					<th class=rotated width=10>Kicker: NPS</th>
						<th class=rotated width=10 id='ppth'>Performance Points</th>
						<th class=rotated width=10>Hours Worked</th>
						<th class=rotated width=10>Bonus before sick</th>";
						if (ismgr()){
							echo "<th class=rotated width=10>Sick Instances</th>
										<th class=rotated width=10>Bonus Paid Out</th>";
						}
						echo "</tr></thead><tfoot>";
						echo "<tr>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td><b>TARGET</b></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td>".getsubtarget(5,2,$team,$view_startdate,'phone')."</td>"; // Phone AHT
						echo "<td>".getsubtarget(5,2,$team,$view_startdate,'email')."</td>"; // Email AHT
						echo "<td>".getsubtarget(5,5,$team,$view_startdate,'phone')."</td>"; // Phone KDI
						echo "<td>".getsubtarget(5,3,$team,$view_startdate,'phone')."</td>"; // Phone CrRR
						echo "<td>".getsubtarget(5,4,$team,$view_startdate,'phone')."</td>"; // Phone NPS
						echo "<td>".getsubtarget(5,5,$team,$view_startdate,'email')."</td>"; // Email KDI
						echo "<td>".getsubtarget(5,3,$team,$view_startdate,'email')."</td>"; // Email CrRR
						echo "<td>";
          				echo  getsubtarget(5,4,$team,$view_startdate,'email');
          				echo "</td>"; // Email NPS
          				$npstarget = gettarget(5,4,$team,$view_startdate, "high");
          				echo "<td>$npstarget</td>";
						echo "<td>98%</td>";
          				echo "<td></td>";
          				echo "<td></td>";
          				echo "<td></td>";
          				echo "<td></td>";
						echo "</tr></tfoot><tbody>";
						$sql = "SELECT distinct teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition ORDER by teammate_nt_id ASC";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						$contract = 5;
						$teammate_counter=0;
						while($row=$result->fetch_assoc()){
							$teammate_counter++; $teammate[$teammate_counter]=$row[teammate_nt_id];
							$teammatename[$row[teammate_nt_id]]=$row[teammate_name];

							// Count emails.
							$sqlb="SELECT distinct external_survey_id FROM raw_data $sqldater $teamdefinition AND teammate_nt_id = '{$row[teammate_nt_id]}' AND queue_source_name LIKE '%EMAIL'";
							if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
							$survey_counter=0;
							while($rowb=$resultb->fetch_assoc()){$survey_counter++;}
							$bd[emailsurveys][$row[teammate_nt_id]]=$survey_counter;

							// Count phone
							$sqlb="SELECT distinct external_survey_id FROM raw_data $sqldater $teamdefinition AND 	teammate_nt_id = '{$row[teammate_nt_id]}' AND queue_source_name LIKE '%VOICE'";
							if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
							$survey_counter=0;
							while($rowb=$resultb->fetch_assoc()){$survey_counter++;}
							$bd[phonesurveys][$row[teammate_nt_id]]=$survey_counter;
						}
						$im=ismgr();

						// Bonus weighting
						$sql = "SELECT * FROM bonus_weighting";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						while($row=$result->fetch_assoc()){$bonus_weight[$row[metric_id]]=$row[weight];}
						for($x=1;$x<=$teammate_counter;$x++){
							if (guessteam($teammate[$x])==$team){
								$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%VOICE'";
								$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Voice'";
								$bd[phonekdi][$teammate[$x]] = 	sv(5,$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
								$bd[phonecrrr][$teammate[$x]] = sv(3,$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
								$bd[phonenps][$teammate[$x]] = sv(4,$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
								$bd[phoneanswered][$teammate[$x]] = av("answered",$startdate,$ahtdef);
								$bd[phoneaht][$teammate[$x]] = av("paht",$startdate,$ahtdef);
								$bd[hours][$teammate[$x]] = gethours($teammate[$x],$startdate);
								$bd[instances][$teammate[$x]] = getinstances($teammate[$x],$startdate);
								$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%EMAIL'";
								$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Email'";
								$bd[emailkdi][$teammate[$x]] = sv(5,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
								$bd[emailcrrr][$teammate[$x]] = sv(3,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
								$bd[emailnps][$teammate[$x]] = sv(4,$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
								$bd[emailworked][$teammate[$x]] = av("worked",$startdate,$ahtdef);
								$bd[emailaht][$teammate[$x]] = av("eaht",$startdate,$ahtdef);

              					$combinedsurveydefinition = " AND teammate_nt_id = '{$teammate[$x]}'";
              					$bd[combinedsurveys][$teammate[$x]] = $bd[phonesurveys][$teammate[$x]] + 	$bd[emailsurveys][$teammate[$x]];
              					$bd[nps][$teammate[$x]] = sv(4,$combinedsurveydefinition,$bd[combinedsurveys][$teammate[$x]]);

								$weight_paht = min(1.3, getsubtarget(5,2,$team,$view_startdate,'phone')/$bd[phoneaht][$teammate[$x]]);
								$weight_eaht = min(1.3, getsubtarget(5,2,$team,$view_startdate,'email')/$bd[emailaht][$teammate[$x]]);
								$weight_pkdi=0;  if ($bd[phonekdi][$teammate[$x]]){$weight_pkdi = min(1.3, max(0.7,$bd[phonekdi][$teammate[$x]]/getsubtarget(5,5,$team,$view_startdate,'phone')));}
								$weight_pcrrr=0; if ($bd[phonecrrr][$teammate[$x]]){$weight_pcrrr = min(1.3, max(0.7,$bd[phonecrrr][$teammate[$x]]/getsubtarget(5,3,$team,$view_startdate,'phone')));}
								$weight_pnps=0;  if ($bd[phonenps][$teammate[$x]]){$weight_pnps = min(1.3, max(0.7,$bd[phonenps][$teammate[$x]]/getsubtarget(5,4,$team,$view_startdate,'phone')));}

								$weight_ekdi=0;  if ($bd[emailkdi][$teammate[$x]]){$weight_ekdi = min(1.3, max(0.7,$bd[emailkdi][$teammate[$x]]/getsubtarget(5,5,$team,$view_startdate,'email')));}
								$weight_ecrrr=0; if ($bd[emailcrrr][$teammate[$x]]){$weight_ecrrr = min(1.3, max(0.7,$bd[emailcrrr][$teammate[$x]]/getsubtarget(5,3,$team,$view_startdate,'email')));}
								$weight_enps=0;  if ($bd[emailnps][$teammate[$x]]){$weight_enps = min(1.3, max(0.7,$bd[emailnps][$teammate[$x]]/getsubtarget(5,4,$team,$view_startdate,'email')));}

								$combinedaht = ($weight_paht*$bd[phoneanswered][$teammate[$x]] + $weight_eaht*$bd[emailworked][$teammate[$x]]) / ($bd[phoneanswered][$teammate[$x]]+$bd[emailworked][$teammate[$x]]);
								$combinedkdi = ($weight_pkdi*$bd[phonesurveys][$teammate[$x]] + $weight_ekdi*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
								$combinedcrrr = ($weight_pcrrr*$bd[phonesurveys][$teammate[$x]] + $weight_ecrrr*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
								$combinednps = ($weight_pnps*$bd[phonesurveys][$teammate[$x]] + $weight_enps*$bd[emailsurveys][$teammate[$x]]) / ($bd[phonesurveys][$teammate[$x]]+$bd[emailsurveys][$teammate[$x]]);
								$bd[performancepoints][$teammate[$x]] = $combinedaht*$bonus_weight[2] + $combinedkdi*$bonus_weight[5] + $combinedcrrr*$bonus_weight[3] + $combinednps*$bonus_weight[4];
							}
						}
						arsort($bd[performancepoints]);
						$counti = 0;
						foreach($bd[performancepoints] as $tm => $points){
							if (guessteam($tm)!=$team){
            				}
            				else {
								$counti++;
								echo "<tr>";
								// Position
								echo "<td>$counti</td>";
								// Name
								$spacer = strpos($teammatename[$tm]," ");
								$name = substr($teammatename[$tm],0,$spacer);
								$surname = substr($teammatename[$tm],$spacer,strlen($teammatename[$tm])-$spacer);
								echo "<td class=tmname>$name</td>";
								echo "<td class=tmname>$surname</td>";
								// Phone answered
								echo "<td>{$bd[phoneanswered][$tm]}</td>";
								// Phone surveys
								echo "<td>{$bd[phonesurveys][$tm]}</td>";
								// Email Worked
								echo "<td>{$bd[emailworked][$tm]}</td>";
								// Email Surveys
								echo "<td>{$bd[emailsurveys][$tm]}</td>";
								// Phone AHT
              					list($bg,$fg)=targetcolor($bd[phoneaht][$tm], $contract, 2, $team, $view_startdate,"phone");
								echo "<td	 style='color:#$fg;background-color:#$bg'>";
              					echo round($bd[phoneaht][$tm],0);
              					echo "</td>";
								// Email AHT
              					list($bg,$fg)=targetcolor($bd[emailaht][$tm], $contract, 2, $team, $view_startdate,"email");
								echo "<td	 style='color:#$fg;background-color:#$bg'>";
								echo round($bd[emailaht][$tm],0);
								echo "</td>";
								// Phone KDI
              					list($bg,$fg)=targetcolor($bd[phonekdi][$tm], $contract, 5, $team, $view_startdate,"phone");
								echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[phonekdi][$tm]}</td>";
								// Phone CrRR
              					list($bg,$fg)=targetcolor($bd[phonecrrr][$tm], $contract, 3, $team, $view_startdate,"phone");
								echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[phonecrrr][$tm]}</td>";
								// Phone NPS
              					list($bg,$fg)=targetcolor($bd[phonenps][$tm], $contract, 4, $team, $view_startdate,"phone");
								echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[phonenps][$tm]}</td>";
								// Email KDI
              					list($bg,$fg)=targetcolor($bd[emailkdi][$tm], $contract, 5, $team, $view_startdate,"email");
								echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[emailkdi][$tm]}</td>";
								// Email CrRR
              					list($bg,$fg)=targetcolor($bd[emailcrrr][$tm], $contract, 3, $team, $view_startdate,"email");
								echo "<td	 style='color:#$fg;background-color:#$bg'>{$bd[emailcrrr][$tm]}</td>";
								// Email NPS
              					list($bg,$fg)=targetcolor($bd[emailnps][$tm], $contract, 4, $team, $view_startdate,"email");
								echo "<td	style='color:#$fg;background-color:#$bg'>{$bd[emailnps][$tm]}</td>";

								// Kicker
              					list($bg,$fg)=targetcolor($bd[nps][$tm], $contract, 4, $team, $view_startdate);
								echo "<td style='color:#$fg;background-color:#$bg'>{$bd[nps][$tm]}</td>";
								// Performance Points
          						list($bg,$fg)=targetcolor($bd[performancepoints][$tm], $contract, 11,5);
								echo "<td	class=pptd style='color:#$fg;background-color:#$bg'>";
								echo round($bd[performancepoints][$tm]*100,2);
								echo "%</td>\n";
								// Hours Worked
								echo "<td>";
								if ($im && $b=='e'){
									echo "<input class='editor' size=3 name='hw$counti' value='{$bd[hours][$tm]}'>";
									echo "<input type=hidden name='tm$counti' value='$tm'>";
								}
								else{	echo $bd[hours][$tm]; }
								echo "</td>";
								// Bonus before sick
								$bonusbeforesick[$tm] = $bd[hours][$tm] * 1.22;
								if ($bd[performancepoints][$tm]<=0.98){$bonusbeforesick[$tm]=0;}
								elseif ($bd[performancepoints][$tm]<=1.02){$bonusbeforesick[$tm]*=0.25;}
								elseif ($bd[performancepoints][$tm]<=1.06){$bonusbeforesick[$tm]*=0.5;}
								echo "<td";
              					if (($bd[nps][$tm]>=$npstarget) && ($bonusbeforesick[$tm]>0)){
                					$bonusbeforesick[$tm]*=1.2;
                					echo " style='color:#06f'>";
                					echo round($bonusbeforesick[$tm],0);
              					}
              					elseif ($bonusbeforesick[$tm]>0) {
                					echo " style='color:#0f0'>";
                					echo round($bonusbeforesick[$tm],0);
              					}
              					else {
                					echo ">--";
              					}
              					echo "</td>";
								// Sick instances
              					if (ismgr()){
                					echo "<td";
                					if ($bd[instances][$tm]==1){echo " style='color:#f80;'";}
                					if ($bd[instances][$tm]>1){echo " style='color:#f00;'";}
                					echo ">";
                					if ($im&&$b=='e'){
										echo "<input class='editor' size=3 name='si$counti' value='{$bd[instances][$tm]}'>";
									}
            						else{
										echo $bd[instances][$tm];
									}
                					echo "</td>";
                					$multo = 1;
                					if ($bd[instances][$tm]>0){$multo='0.5';}
                					if ($bd[instances][$tm]>1){$multo='0';}
                					$bonuspaidout[$tm] = round($bonusbeforesick[$tm]*$multo,0);
              					}
								// Bonus paid out
              					if (ismgr()){
                					echo "<td";
                					if ($bonuspaidout[$tm]>0){
										echo " style='color:#0f0;'>{$bonuspaidout[$tm]}";
									}
                					else {
                  						echo ">--";
									}
                					echo "</td>";
              					}
								echo "</tr>\n";
							}
						}
						echo "</tbody></table>";
						if(ismgr()){
							if ($b=='e'){
								echo "<input type=submit><input type=hidden name=c value='saved'><input type=hidden name=a value=$a><input type=hidden name=b value=$b>
								<input type=hidden name=tms value=$counti><input type=hidden name=month value='$startdate_year-$startdate_month'></form>";
							}
						}
        			}
				}
				else{echo "This view only works with a team selected.";}
			}
			if($a=='bonus'){
				if ($team>0){
					echo "<div class='unselectable'>";
					echo "<a href='?a=oldbonus&startdate=$startdate&enddate=$enddate&team=$team'>Old bonus</a> <div class='new'>NEW</div>";
					if (ismgr()){
						echo " / <a href='?a=$a&startdate=$startdate&enddate=$enddate&team=$team'>View</a> / ";
						echo "<a href='?a=$a&b=e&startdate=$startdate&enddate=$enddate&team=$team'>Edit</a>";
					}
					echo "</div>";
					$view_startdate_month=$startdate_month;
					$view_startdate_year=$startdate_year;
					$view_enddate_year=$enddate_year;
					$view_enddate_month=$enddate_month;
					$view_startdate_day="01";
					$view_enddate_day = cal_days_in_month(CAL_GREGORIAN,$view_startdate_month,$view_enddate_year);
					$view_startdate = "$view_startdate_year-$view_startdate_month-01";
					$view_enddate = "$view_startdate_year-$view_startdate_month-$view_enddate_day";
					if ($enddate_day != $view_enddate_day){
						echo "<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
					}
					if ($view_startdate_month != $view_enddate_month){
						echo "<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";
					}
					echo "<h2>Month: " . $month[$view_startdate_month]." $view_startdate_year</h2>";
					if(ismgr()){
						if ($_GET[c]=='saved'){
							for($x=1;$x<=$_GET[tms];$x++){
								$t="tm$x";
								$h="hw$x";
								$s="si$x";
								$tmname[$x]= $_GET[$t];
								$tmhw[$x]=$_GET[$h];
								$tmsi[$x]=$_GET[$s];
								$sql = "SELECT teammate_nt_id FROM hours WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}' LIMIT 1";
								if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
								else {
									$row=$result->fetch_assoc();
									if ($row[teammate_nt_id]==''){
										$sql = "INSERT INTO hours (teammate_nt_id,date,worked_hours,sick_instances) VALUES('{$tmname[$x]}','{$_GET[month]}','{$tmhw[$x]}','{$tmsi[$x]}')";
										if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
									}
									else {
										$sql = "UPDATE hours SET worked_hours = '{$tmhw[$x]}' WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}'";
										if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
										$sql = "UPDATE hours SET sick_instances = '{$tmsi[$x]}' WHERE teammate_nt_id = '{$tmname[$x]}' AND date = '{$_GET[month]}'";
										if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
									}
								}
							}
						}
						echo "<form method=get>";
  				}
					if(1) {
						echo "<table  class='sortable' id='bonustable' cellpadding=0 cellspacing=0>";
						echo "<thead><tr>
						<th class=rotated>Position</th>
						<th>Name</th>
						<th>Surname</th>
						<th class='xtoggle rotated'>Contacts</th>
            <th class='xtoggle rotated'>Ratio</th>
						<th class='xtoggle rotated'>Surveys</th>
						<th class=rotated>AHT</th>
						<th class=rotated>KDI</th>
						<th class=rotated>TR</th>
						<th class=rotated>RCR</th>
						<th class=rotated>Kicker: NPS</th>
						<th class=rotated width=10 id='ppth'>Bonus Points</th>
						<th class=rotated width=10>Hours Worked</th>
						<th class=rotated width=10>Bonus before sick</th>";
						if (ismgr()){
							echo "<th class=rotated width=10>Sick Instances</th>
							<th class=rotated width=10>Bonus Paid Out</th>";
						}
						echo "</tr></thead>";

						echo "<tbody>";
						$sql = "SELECT distinct teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition ORDER by teammate_nt_id ASC";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						$contract = 5;
						$teammate_counter=0;

            $tgt_aht = gettarget(5,2,$team,$view_startdate,'low');
            $tgt_goodaht = getgoodtarget(5,2,$team,$view_startdate,'low');
            $tgt_greataht = getgreattarget(5,2,$team,$view_startdate,'low');

            $tgt_kdi = gettarget(5,5,$team,$view_startdate,'high');
            $tgt_goodkdi = getgoodtarget(5,5,$team,$view_startdate,'high');
            $tgt_greatkdi = getgreattarget(5,5,$team,$view_startdate,'high');

            $tgt_tr = gettarget(5,6,$team,$view_startdate,'low');
            $tgt_goodtr = getgoodtarget(5,6,$team,$view_startdate,'low');
            $tgt_greattr = getgreattarget(5,6,$team,$view_startdate,'low');

            $tgt_rcr = gettarget(5,17,$team,$view_startdate,'low');
            $tgt_goodrcr = getgoodtarget(5,17,$team,$view_startdate,'low');
            $tgt_greatrcr = getgreattarget(5,17,$team,$view_startdate,'low');

            $npstarget = gettarget(5,4,$team,$view_startdate,'high');

            $fair_weight = 0.5;
            $good_weight = 1;
            $great_weight = 1.2;

						while($row=$result->fetch_assoc()){
							$teammate_counter++;
							$teammate[$teammate_counter]=$row[teammate_nt_id];
							$teammatename[$row[teammate_nt_id]]=$row[teammate_name];
							$sqlb = "SELECT DISTINCT external_survey_id FROM raw_data $sqldater $teamdefinition AND teammate_nt_id = '{$row[teammate_nt_id]}'";
							if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
							$survey_counter=0;
							while($rowb=$resultb->fetch_assoc()){$survey_counter++;}
								$bd[surveys][$row[teammate_nt_id]]=$survey_counter;
							}
							$im=ismgr();

  						// Bonus weighting
  						$sql = "SELECT newweight,metric_id,newkicker FROM bonus_weighting WHERE newweight > 0";
  						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  						while($row=$result->fetch_assoc()){
								$bonus_weight[$row[metric_id]]=$row[newweight];
							}
							// Get standard target.

							for($x=1;$x<=$teammate_counter;$x++){
								if (guessteam($teammate[$x])==$team){
									$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}'";
									$ahtdef = " AND ntid = '{$teammate[$x]}'";
									$bd[contacts][$teammate[$x]] = av("answered",$startdate,$ahtdef) + av("worked",$startdate,$ahtdef);
                  $bd[ratio][$teammate[$x]] = av("worked",$startdate,$ahtdef) / av("answered",$startdate,$ahtdef);
									$bd[aht][$teammate[$x]] = av("aht",$startdate,$ahtdef);
									$bd[kdi][$teammate[$x]] = sv(5,$surveydefinition,$bd[surveys][$teammate[$x]],'');
									$bd[tr][$teammate[$x]] = av("tr",$startdate,$ahtdef);
									$bd[rcr][$teammate[$x]] = av("rcr",$startdate,$ahtdef);
  								$bd[nps][$teammate[$x]] = sv(4,$surveydefinition,$bd[surveys][$teammate[$x]],'');
  								$bd[hours][$teammate[$x]] = gethours($teammate[$x],$startdate);
  								$bd[instances][$teammate[$x]] = getinstances($teammate[$x],$startdate);

									$weight_aht = 0;
									if ($bd[contacts][$teammate[$x]]> 0 ) {
										if ($bd[aht][$teammate[$x]] <= $tgt_greataht){ $weight_aht = $great_weight; }
										else if ($bd[aht][$teammate[$x]] <= $tgt_goodaht){ $weight_aht = $good_weight; }
										else if ($bd[aht][$teammate[$x]] <= $tgt_aht){ $weight_aht = $fair_weight; }
									}
									$weight_kdi = 0;
									if ($bd[surveys][$teammate[$x]]>0) {
										if ($bd[kdi][$teammate[$x]] >= $tgt_greatkdi) { $weight_kdi = $great_weight; }
										else if ($bd[kdi][$teammate[$x]] >= $tgt_goodkdi) { $weight_kdi = $good_weight; }
										else if ($bd[kdi][$teammate[$x]] >= $tgt_kdi) { $weight_kdi = $fair_weight; }
									}

									$weight_tr = 0;
									if ($bd[contacts][$teammate[$x]]> 0 ) {
										if ($bd[tr][$teammate[$x]]*100 <= $tgt_greattr) { $weight_tr = $great_weight; }
										else if ($bd[tr][$teammate[$x]]*100 <= $tgt_goodtr) { $weight_tr = $good_weight; }
										else if ($bd[tr][$teammate[$x]]*100 <= $tgt_tr) { $weight_tr = $fair_weight; }
									}
									$weight_rcr = 0;
									if ($bd[contacts][$teammate[$x]]> 0 ) {
										if ($bd[rcr][$teammate[$x]]*100 <= $tgt_greatrcr) { $weight_rcr = $great_weight; }
										else if ($bd[rcr][$teammate[$x]]*100 <= $tgt_goodrcr) { $weight_rcr = $good_weight; }
										else if ($bd[rcr][$teammate[$x]]*100 <= $tgt_rcr) { $weight_rcr = $fair_weight; }
									}
									$bd[performancepoints][$teammate[$x]] = ($weight_aht * $bonus_weight[2]) + ($weight_kdi * $bonus_weight[5]) + ($weight_tr * $bonus_weight[6]) + ($weight_rcr * $bonus_weight[17]);
								}
  						}
  						arsort($bd[performancepoints]);
  						$counti = 0;
  						foreach($bd[performancepoints] as $tm => $points){
  							if (guessteam($tm)!=$team){
								}
								else {
									$counti++;
									echo "<tr>";
									// Position
									echo "<td>$counti</td>";
									// Name
									$spacer = strpos($teammatename[$tm]," ");
									$name = substr($teammatename[$tm],0,$spacer);
									$surname = substr($teammatename[$tm],$spacer,strlen($teammatename[$tm])-$spacer);
									echo "<td class=tmname>$name</td>";
									echo "<td class=tmname>$surname</td>";
									// contacts
									echo "<td class=xtoggle>{$bd[contacts][$tm]}</td>";
									echo "<td class=xtoggle>" . round($bd[ratio][$tm]*100,0) . "%</td>";
									// Surveys
									echo "<td class=xtoggle>{$bd[surveys][$tm]}</td>";
									// AHT
									list($bg,$fg)=targetcolor($bd[aht][$tm], $contract, 2, $team, $view_startdate);
									echo "<td class='mainmetric' style='color:#$fg;background-color:#$bg'>";
									echo round($bd[aht][$tm],0);
									echo "</td>";
									// KDI
									list($bg,$fg)=targetcolor($bd[kdi][$tm], $contract, 5, $team, $view_startdate);
									echo "<td class='mainmetric' style='color:#$fg;background-color:#$bg'>";
									echo round($bd[kdi][$tm],1);
									echo "</td>";

								// TR
								list($bg,$fg)=targetcolor($bd[tr][$tm]*100, $contract, 6, $team, $view_startdate);
  								echo "<td class='mainmetric' style='color:#$fg;background-color:#$bg'>";
										if (is_numeric($bd[tr][$tm])) {
                			echo round($bd[tr][$tm]*100,1);
                			echo "%";
										}
										else { echo $bd[tr][$tm]; }
										echo "</td>";
								// RCR
								list($bg,$fg)=targetcolor($bd[rcr][$tm]*100, $contract, 17, $team, $view_startdate);
  								echo "<td class='mainmetric' style='color:#$fg;background-color:#$bg'>";
                				echo round($bd[rcr][$tm]*100,1);
                				echo "%</td>";

								// NPS
								$gob = 'bad';
								if ($bd[nps][$tm] >= $npstarget) {
									$gob = 'good';
								}
  								echo "<td class='bonuskicker kicker$gob'>";
                	echo round($bd[nps][$tm],1);
                	echo "</td>";

  								// Performance Points
            					list($bg,$fg)=targetcolor($bd[performancepoints][$tm], $contract, 11,5);
  								echo "<td class=pptd style='color:#$fg;background-color:#$bg'>";
  								echo round($bd[performancepoints][$tm]*100,2);
  								echo "%</td>\n";
  								// Hours Worked
  								echo "<td>";
  								if ($im && $b=='e'){
  									echo "<input class='editor' size=3 name='hw$counti' value='{$bd[hours][$tm]}'>";
  									echo "<input type=hidden name='tm$counti' value='$tm'>";
  								}
  								else{	echo $bd[hours][$tm]; }
  								echo "</td>";
  								// Bonus before sick
  								$bonusbeforesick[$tm] = $bd[hours][$tm] * $bd[performancepoints][$tm] * $bd[performancepoints][$tm]*$bd[performancepoints][$tm] * 0.8;
  								echo "<td";
                					if (($bd[nps][$tm]>=$npstarget) && ($bonusbeforesick[$tm]>0)){
                  					$bonusbeforesick[$tm]*=1.2;
                  					echo " style='color:#06f'>";
                  					echo round($bonusbeforesick[$tm],2);
                					}
                					elseif ($bonusbeforesick[$tm]>0) {
                  					echo " style='color:#0f0'>";
                  					echo round($bonusbeforesick[$tm],2);
                					}
                					else {
                  					echo ">--";
                					}
                					echo "</td>";
  								// Sick instances
                					if (ismgr()){
                  					echo "<td";
                  					if ($bd[instances][$tm]==1){echo " style='color:#f80;'";}
                  					if ($bd[instances][$tm]>1){echo " style='color:#f00;'";}
                  					echo ">";
                  					if ($im&&$b=='e'){
  										echo "<input class='editor' size=3 name='si$counti' value='{$bd[instances][$tm]}'>";
  									}
              						else{
  										echo $bd[instances][$tm];
  									}
                  					echo "</td>";
                  					$multo = 1;
                  					if ($bd[instances][$tm]>0){$multo='0.5';}
                  					if ($bd[instances][$tm]>1){$multo='0';}
                  					$bonuspaidout[$tm] = round($bonusbeforesick[$tm]*$multo,2);
														$totalaftersick += $bonuspaidout[$tm];
														$totalbeforesick += $bonusbeforesick[$tm];
                					}
  								// Bonus paid out
                					if (ismgr()){
                  					echo "<td";
                  					if ($bonuspaidout[$tm]>0){
  										echo " style='color:#0f0;'>{$bonuspaidout[$tm]}";
  									}
                  					else {
                    						echo ">--";
  									}
                  					echo "</td>";
                					}
  								echo "</tr>\n";
  							}
  						}
  						echo "</tbody>";
							echo "<tfoot>";
							echo "<tr>";
							echo "<td colspan=5><b>FAIR</b></td>";
	            echo "<td>41%</td>";
	            echo "<td></td>";
							echo "<td>".gettarget(5,2,$team,$view_startdate,'low')."</td>"; // aht
							echo "<td>".gettarget(5,5,$team,$view_startdate,'high')."</td>"; // kdi
							echo "<td>".gettarget(5,6,$team,$view_startdate,'low')."</td>"; // tr
							echo "<td>".gettarget(5,17,$team,$view_startdate,'low')."</td>"; // rcr
							echo "<td>".gettarget(5,4,$team,$view_startdate,'high')."</td>"; // nps
							echo "<td></td>";
							echo "<td></td>";
							echo "<td>". round($totalbeforesick,2) . "</td>";
							echo "<td></td>";
							echo "<td>". round($totalaftersick, 2) . "</td>";
							echo "</tr>";
	            echo "<tr>";
							echo "<td colspan=5><b>GOOD</b></td>";
	            echo "<td></td>";
	            echo "<td></td>";
	            echo "<td>".getgoodtarget(5,2,$team,$view_startdate,'low')."</td>"; // aht
							echo "<td>".getgoodtarget(5,5,$team,$view_startdate,'high')."</td>"; // kdi
							echo "<td>".getgoodtarget(5,6,$team,$view_startdate,'low')."</td>"; // tr
							echo "<td>".getgoodtarget(5,17,$team,$view_startdate,'low')."</td>"; // rcr
							echo "</tr>";
	            echo "<tr>";
							echo "<td colspan=5><b>GREAT</b></td>";
	            echo "<td></td>";
	            echo "<td></td>";
	            echo "<td>".getgreattarget(5,2,$team,$view_startdate,'low')."</td>"; // aht
							echo "<td>".getgreattarget(5,5,$team,$view_startdate,'high')."</td>"; // kdi
							echo "<td>".getgreattarget(5,6,$team,$view_startdate,'low')."</td>"; // tr
							echo "<td>".getgreattarget(5,17,$team,$view_startdate,'low')."</td>"; // rcr
							echo "</tr>";
	            echo "</tfoot>";
							echo "</table>";
							if(ismgr()){
  							if ($b=='e'){
  								echo "<input type=submit><input type=hidden name=c value='saved'><input type=hidden name=a value=$a><input type=hidden name=b value=$b>
  								<input type=hidden name=tms value=$counti><input type=hidden name=month value='$startdate_year-$startdate_month'></form>";
  							}
  						}
          			}
  				}
  				else{echo "This view only works with a team selected.";}
  			}
			elseif($a=='calendar'){
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
      elseif($a=='masters'){
        if ($team>0){
          echo "Team " . getteamname($team) . "<br><br>";
        }
        if ($b=="show"){
          $showhide = "";
        }
        else {
          $onemonthago = unixdate_to_exceldate(mktime(0,0,0,$currentmonth-1,1,$currentyear));
          $showhide = "AND teammate_contact_date > $onemonthago";
        }
        $sql = "SELECT teammate_name,teammate_contact_date,teammate_nt_id, count(external_survey_id) as surveys FROM raw_data WHERE teammate_nt_id <> '' $showhide GROUP by teammate_nt_id ORDER BY teammate_name ASC";

        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        $tm_count = 0;
		$recommendedstartdate = date("Y-m-d",mktime(0,0,0,$currentmonth-3,1,$currentyear));
		$recommendedenddate = date("Y-m-d");
        echo "<a href='?a=$a&b=show'>Display ex-employees</a> | ";
		echo "<a href='?a=$a&b=$b&startdate=$recommendedstartdate&enddate=$recommendedenddate'>Recommended interval</a><br><br>";
        echo "<table class='sortable bt' id='queuelist' cellspacing=0 cellpadding=0><thead>";
		echo "<th>Rank</th>";
		echo "<th></th>";
        echo "<th>Name and Title</th>";
		echo "<th>Level</th>";
        echo "</tr></thead>";

        // Define EP for each action
        $surveyxp = 1;
        $callxp = 1;
        $emailxp = 1;
        $EP_contact_tracking = 1;

		$levelxp = 30;
		$rankskill = 0;

		$generalrankmultiplier = 1;

        $PVPPoints_KDI = $generalrankmultiplier * 1;
        $PVPPoints_NPS = $generalrankmultiplier * 2;
        $PVEPoints_CrRR = $generalrankmultiplier * 1;

		$date_start_month = substr($startdate,5,2);
		$date_start_day = substr($startdate,8,2);
		$date_start_year = substr($startdate,0,4);

		$date_end_month = substr($enddate,5,2);
		$date_end_day = substr($enddate,8,2);
		$date_end_year = substr($enddate,0,4);

		$date_start_unix = mktime(0,0,0,$date_start_month, $date_start_day, $date_start_year);
		$date_end_unix = mktime(23,59,59,$date_end_month, $date_end_day, $date_end_year);

		$date_start_raw = unixdate_to_exceldate($date_start_unix);
		$date_end_raw = unixdate_to_exceldate($date_end_unix);

		// Starting values
		$ahtsqltext = '';

		$ahtstartyear = substr($startdate,0,4);
		$ahtstartmonth =  substr($startdate,5,2);
		$ahtendyear = substr($enddate,0,4);
		$ahtendmonth =  substr($enddate,5,2);

		$notfinished = true;
		while ($notfinished) {
			$notfinished = false;
		}

		//$ahtdates = "AND month >= '" . $monthy[substr($date_start,5,2)] . "-".substr($date_start,0,4)."'
		//AND month <= '" . $monthy[substr($date_end,5,2)] . "-".substr($date_end,0,4)."'  ";
		//echo $ahtdates;
		$exceldates = "AND teammate_contact_date >= '$date_start_raw' AND teammate_contact_date <= '$date_end_raw'";

        while ($row=$result->fetch_assoc()){
			$experiencepoints = 0;
            if (($team<0) || (guessteam($row[teammate_nt_id])==$team)){
              $pvppoints = 0; $kdi = 0; $tm_count++;

			  // Contact tracking
			  $sqlb = "SELECT count(contact_tracking_reason) as ctr from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' $exceldates";
              if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
              $rowb=$resultb->fetch_assoc();
              $ctr = $rowb[ctr] *  $EP_contact_tracking;
			  $experiencepoints = $ctr;
			  //$experiencepoints = 0;

			  // Contacts
              $sqlb = "SELECT SUM(Phone_Answered) as pa, SUM(Email_replied) as er FROM prt060data WHERE NTID = '{$row[teammate_nt_id]}' AND Queue_Name <> 'Total'  $ahtdates";
              if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
              $rowb=$resultb->fetch_assoc();
              $ep = (($row[surveys]*$surveyxp) + ($rowb[pa]*$callxp) + ($rowb[er]*$emailxp));
			  $experiencepoints += $ep;

              // NPS Skill (PVP);
              $sqlb = "SELECT count(likely_to_recommend_paypal) as nps from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND likely_to_recommend_paypal > 8 $exceldates";
              if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
              $rowb=$resultb->fetch_assoc();
              $nps_good = $rowb[nps];
              $sqlb = "SELECT count(likely_to_recommend_paypal) as nps from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND likely_to_recommend_paypal < 7 $exceldates";
              if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
              $rowb=$resultb->fetch_assoc();
              $pvppoints = ($nps_good - $rowb[nps])*$PVPPoints_NPS;

              // KDI Skill (PVP);
              $sqlb = "SELECT count(KDI___email) as ekdi, count(KDI___phone) as pkdi from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND (kdi___phone > 70 OR kdi___email > 70) $exceldates";
              if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
              $rowb=$resultb->fetch_assoc();
              $kdi = $rowb[ekdi] + $rowb[pkdi];
			  $pvppoints = $pvppoints + ($kdi*$PVPPoints_KDI);

			  // KDI Skill (PVP);
              $sqlb = "SELECT count(KDI___email) as ekdi, count(KDI___phone) as pkdi from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND (kdi___phone < 70 OR kdi___email < 70) $exceldates";
              if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
              $rowb=$resultb->fetch_assoc();
			  $kdi = $rowb[ekdi] + $rowb[pkdi];
              $pvppoints = $pvppoints - $kdi*$PVPPoints_KDI;


              // CrRR Skill (PVE);
              $sqlb = "SELECT count(Issue_resolved) as ir from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND issue_resolved = 'Yes' $exceldates";
			  //echo $sqlb . '<br><br>';
              if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
              $rowb=$resultb->fetch_assoc();
              $pvepoints = ($rowb[ir]*$PVEPoints_CrRR);
              $sqlb = "SELECT count(Issue_resolved) as ir from raw_data WHERE teammate_nt_id = '{$row[teammate_nt_id]}' AND issue_resolved <> 'Yes' $exceldates";
              if(!$resultb=$db->query($sqlb)){cl($sqlb);cl($db->error);}
              $rowb=$resultb->fetch_assoc();
              $pvepoints = $pvepoints - ($rowb[ir]*$PVEPoints_CrRR);

			  //$level = round($experiencepoints / $levelxp,0);
			  $level = round(sqrt($experiencepoints/$levelxp),0);
			  //$knowledgelevel = round($pvepoints/$level/4.2,0);
			  //$softlevel = round($pvppoints/$level/3.45,0);
			  //$rank = round(($pvepoints + $pvppoints)/($ctr*$rankskill),0);
			  $rank = round(sqrt($pvepoints+$pvppoints),0);
			  //$rank = round(($softlevel + $knowledgelevel - 24)/1.4,0);
			  //if ((($knowledgelevel+$softlevel) > 24) && ($level>9)) {
			  //if ((($knowledgelevel+$softlevel) > 2) && ($level>1)) {
				$bgc = round($rankcc * $rank,0);
				$titleid = g('ranks','titleid',$rank);
				$rankname = g('titles','title',$titleid);
				$rankposition = g('titles','left_or_right',$titleid);
				$guessteam = guessteam($row[teammate_nt_id]);
				$race = g('teams','team_race',$guessteam);
				$racefgcolor = g('teams','team_fgcolor',$guessteam);
				$racebgcolor = g('teams','team_bgcolor',$guessteam);
				$raceborder = g('teams','team_border',$guessteam);
				$racelinkcolor = g('teams','team_linkcolor',$guessteam);

				echo "<tr ";
				//echo "style='background:rgba(0, 0, " . (55+$bgc) . ",0.8);'";
				echo "style='background-color: #$racebgcolor; color: #$racefgcolor;'";
				echo ">";
				echo "<td>";
				if ($level<10) {
					//echo "($rank)";
				}
				else { echo $rank; }
				//echo " ($pvppoints/$pvepoints/$ctr) ";
				echo "</td>";
				echo "<td>";
				if ($level>9) {
					if ($rank>0) {
					echo "<img src='{$path}ranks/rank$rank.png' width=32 height=32>";
				}
				}
				echo "</td>";
                echo "<td>";
				echo "<a class='name-text' style='color:#$racelinkcolor' href='?a=levels&c={$row[teammate_nt_id]}'>";
				if ($level>9) {
					if ($rankname && ($rankposition=='left')) { echo "<span class='rank-text'>$rankname</span> "; }
				}
				echo "{$row[teammate_name]}";
				if ($level>9) {
					if ($rankname && ($rankposition=='right')) { echo "<span class='rank-text'>$rankname</span>"; }
				}
				echo "</a></td>";
				echo "<td>";
				echo "<span class='level-text'>$level</span> ";
				echo "</td>";
				echo "<td>";
				echo "<span class='race-text' style='color:#$racebgcolor;
				text-shadow: 2px 2px 0px #$raceborder, -1px -1px 10px #$raceborder;
				background-color:#$racefgcolor;'>$race</span> ";
				echo "<span class='class-text'>$class</span> ";
				echo "</td>";
                echo "</tr>\n\n";
			  //}
            }
        }
        echo "</table>";
      }
      elseif($a=='team'){
        echo "<a href='?a=$a'>View</a> | ";
        echo "<a href='?a=$a&b=$b&x=1'>Edit</a>";
        $x = $_GET[x];
        $latestsurvey = getlatest($teamdefinition);
        echo "<div id='message'>";
        echo "Hi Dave";
        $date1 = new DateTime($today);
        $date2 = new DateTime($latestsurvey);
        $interval = $date1->diff($date2);
        echo "<br><br><b>Call Outs</b>";
        echo "<ul>";
        echo "<li>In {$month[$startdate_month]} $startdate_year, team ".getteamname($team);
        if ($interval->d <3) { echo " has so far"; }
        echo " received ".surveycount()." surveys. ";
        if ($interval->d <3) {
          $prevmonth=$currentmonth-1; if($prevmonth<1){$prevmonth=12;}
          $twomonthsago=$prevmonth-1; if($twomonthsago<1){$twomonthsago=12;}
          echo " (" . round(surveycount()/surveycount("previous month")*100,0) . "% vs $month[$prevmonth] and ";
          echo " " . round(surveycount()/surveycount("two months ago")*100,0) . "% vs $month[$twomonthsago]). ";
          echo " The latest survey has been received on ";
        }
        else {
          echo "The last survey was received on ";
        }
        echo $latestsurvey . '.</li> ';
        // Metrics
        $aht = vm(2,$team,$startdate);
        $ahttarget = gettarget(5,2,$team,$startdate,"low");

        $kdi = getvalue(5,$startdate,$enddate);;
        $kditarget = gettarget(5,5,$team,$startdate,"high");

        $crrr = getvalue(3,$startdate,$enddate);;
        $crrrtarget = gettarget(5,3,$team,$startdate,"high");

        $nps = getvalue(4,$startdate,$enddate);;
        $npstarget = gettarget(5,4,$team,$startdate,"high");

        $pvol = vm("pvol",$team,$startdate); $evol = vm("evol",$team,$startdate);
        $ptr = vm("ptr",$team,$startdate); $etr = vm("etr",$team,$startdate);
        $tr = round((($pvol*$ptr + $evol*$etr)/($pvol + $evol))*100,1);
        $trtarget = gettarget(5,6,$team,$startdate,"low");
        $ahtmet = -1;$kdimet = -1;$crrrmet = -1;$npsmet = -1;$trmet = -1;
        if ($aht <= $ahttarget) { $ahtmet = 1; }
        if ($kdi >= $kditarget) { $kdimet = 1; }
        if ($crrr >= $crrrtarget) { $crrrmet = 1; }
        if ($nps >= $npstarget) { $npsmet = 1; }
        if ($tr <= $trtarget) { $trmet = 1; }
        if ($c == "removeaction") {
          if ($d) {
            $sql = "DELETE FROM monthlyactions WHERE id = '$d'";
            if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
          }
        }
        $sql = "SELECT * FROM calloutactions";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        while ($row=$result->fetch_assoc()){
          $actions[$row[id]] = $row[actiontext];
        }
        if ($c=="addactiondone"){
          $sql = "SELECT id FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND calloutactionid = '$d' AND metricid = '{$_GET[m]}' LIMIT 1";
          if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
          $row=$result->fetch_assoc();
          if ($row[id]==''){
            $sql = "INSERT INTO monthlyactions (calloutactionid,teamid,year,month,metricid) VALUES('{$d}','{$team}','{$startdate_year}','{$startdate_month}','{$_GET[m]}')";
            if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
          }
        }

        echo "<li>AHT "; if ($ahtmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($aht-$ahttarget,0)."s / ".round((($aht-$ahttarget)/$ahttarget)*100,0)."%)";
        if ($x) {
          if (($c=='addaction') && ($_GET[m]==2)){
            echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
            echo "<option></option>";
            $sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
            if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            while ($row=$result->fetch_assoc()){
              echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
            }
            echo "</select>";
          }
          elseif ($ahtmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=2&x=1'>Add action</a>";}
        }
        echo "</li>";
        $sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '2'";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        $actios=0;
        while($row=$result->fetch_assoc()){
          $actios++;
          if ($actios==1){echo "<ul>"; }
          echo "<li>";
          if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
          echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
        }
        if ($actios>0){echo "</ul>"; }

        echo "<li>KDI "; if ($kdimet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($kdi-$kditarget,1)."%)";
        if ($x) {
          if (($c=='addaction') && ($_GET[m]==5)){
            echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
            echo "<option></option>";
            $sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
            if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            while ($row=$result->fetch_assoc()){
              echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
            }
            echo "</select>";
          }
          elseif ($kdimet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=5&x=1'>Add action</a>";}
        }
        echo "</li>";
        $sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '5'";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        $actios=0;
        while($row=$result->fetch_assoc()){
          $actios++;
          if ($actios==1){echo "<ul>"; }
          echo "<li>";
          if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
          echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
        }
        if ($actios>0){echo "</ul>"; }
        echo "<li>CrRR "; if ($crrrmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($crrr-$crrrtarget,1)."%)";
        if ($x) {
          if (($c=='addaction') && ($_GET[m]==3)){
            echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
            echo "<option></option>";
            $sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
            if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            while ($row=$result->fetch_assoc()){
              echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
            }
            echo "</select>";
          }
          elseif ($crrrmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=3&x=1'>Add action</a>";}
        }
        echo "</li>";
        $sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '3'";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        $actios=0;
        while($row=$result->fetch_assoc()){
          $actios++;
          if ($actios==1){echo "<ul>"; }
          echo "<li>";
          if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
          echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
        }
        if ($actios>0){echo "</ul>"; }
        echo "</li>";
        echo "<li>NPS "; if ($npsmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($nps-$npstarget,1)."%)";
        if ($x) {
          if (($c=='addaction') && ($_GET[m]==4)){
            echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
            echo "<option></option>";
            $sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
            if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            while ($row=$result->fetch_assoc()){
              echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
            }
            echo "</select>";
          }
          elseif ($npsmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=4&x=1'>Add action</a>";}
        }
        echo "</li>";
        $sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '4'";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        $actios=0;
        while($row=$result->fetch_assoc()){
          $actios++;
          if ($actios==1){echo "<ul>"; }
          echo "<li>";
          if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
          echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
        }
        if ($actios>0){echo "</ul>"; }

        echo "</li>";
        echo "<li>TR "; if ($trmet==1) { echo " <span class=goodie>met</span> "; } else { echo " <span class=baddie>missed</span> "; } echo "(by ".round($tr-$trtarget,1)."%)";
        if ($x) {
          if (($c=='addaction') && ($_GET[m]==6)){
            echo "<select onChange='location.href=\"?a=team&b=$team&m={$_GET[m]}&x=1&c=addactiondone&d=\" + this.value;'>";
            echo "<option></option>";
            $sql = "SELECT * FROM calloutactions WHERE metric_id = '{$_GET[m]}'";
            if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            while ($row=$result->fetch_assoc()){
              echo "<option value='{$row[id]}'>" . $row[actiontext] . "</option>";
            }
            echo "</select>";
          }
          elseif ($trmet==-1){ echo " <a href='?a=team&b=$b&c=addaction&m=6&x=1'>Add action</a>";}
        }
        echo "</li>";
        $sql = "SELECT * FROM monthlyactions WHERE teamid = '$team' AND year = '$startdate_year' AND month = '$startdate_month' AND metricid = '6'";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        $actios=0;
        while($row=$result->fetch_assoc()){
          $actios++;
          if ($actios==1){echo "<ul>"; }
          echo "<li>";
          if ($x) { echo "[<a href='?a=team&b=$b&c=removeaction&d={$row[id]}&x=1'>x</a>] "; }
          echo "Action: " . $actions[$row[calloutactionid]] . "</li>";
        }
        if ($actios>0){echo "</ul>"; }

        echo "</li>";

        echo "</li>";
        echo "</ul>";

        echo "<br><b>Focus Areas</b>";

      }
      // surv3ys
      elseif($a=='surveys'){
        // A survey id has been supplied
        if ($e!=''){
          $sql = "SELECT * FROM raw_data WHERE external_survey_id = '$e'";
          if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
          $row=$result->fetch_assoc();
          echo "<h2>Survey $e</h2>";
          echo "<table>";
            echo "<tr><td>Account number</td><td><a href='https://admin.paypal.com/cgi-bin/admin?node=loaduserpage_home&account_number={$row[Customer_Account_ID]}'>{$row[Customer_Account_ID]}</a></td></tr>";
            echo "<tr><td>Teammate name:</td><td>{$row[Teammate_Name]}</td></tr>";
            echo "<tr><td>Teammate NT ID:</td><td>{$row[Teammate_NT_ID]}</td></tr>";
            echo "<tr><td>Teammate tenure</td><td>{$row[Teammate_Tenure]}</td></tr>";
            echo "<tr><td>Team leader name:</td><td>{$row[Team_Leader_Name]}</td></tr>";
            echo "<tr><td>Response date:</td><td>".date("D M jS y, H:i:s", exceldate_to_unixedate($row['Response_Date']))."</td></tr>";
            echo "<tr><td>Contact date:</td><td>".date("D M jS y, H:i:s", exceldate_to_unixedate($row['Teammate_Contact_Date']))."</td></tr>";
            echo "<tr><td>Queue:</td><td>{$row[Queue_Source_Name]}</td></tr>";
            echo "<tr><td>Contact tracking reason:</td><td>{$row[Contact_Tracking_Reason]}</td></tr>";
            echo "<tr><td>UCID</td><td>{$row[Work_Item_Phone_UCID]}</td></tr>";
            echo "<tr><td>ASAT</td><td>{$row[Teammate_Satisfaction__ASAT_]}</td></tr>";
            echo "<tr><td>LTR</td><td>{$row[Likely_to_recommend_PayPal]}</td></tr>";
            echo "<tr><td>Issue Resolved</td><td>{$row[Issue_resolved]}</td></tr>";
            echo "<tr><td>Customer effort to handle issue</td><td>{$row[Customer_Effort_to_Handle_Issu]}</td></tr>";
            echo "<tr><td>Reason for contact</td><td>{$row[Reason_for_contact]}</td></tr>";
            echo "<tr><td>Handled professionally</td><td>{$row[Handled_professionally]}</td></tr>";
            echo "<tr><td>Showed genuine interest</td><td>{$row[Showed_genuine_interest]}</td></tr>";
            echo "<tr><td>Took ownership</td><td>{$row[Took_ownership]}</td></tr>";
            echo "<tr><td>Knowledge to handle request</td><td>{$row[Knowledge_to_handle_request]}</td></tr>";
            echo "<tr><td>Value customer</td><td>{$row[Valued_customer]}</td></tr>";
            echo "<tr><td>Was professional</td><td>{$row[Was_professional]}</td></tr>";
            echo "<tr><td>Easy to understand</td><td>{$row[Easy_to_understand]}</td></tr>";
            echo "<tr><td>Provided accurate info</td><td>{$row[Provided_accurate_info]}</td></tr>";
            echo "<tr><td>Helpful response</td><td>{$row[Helpful_response]}</td></tr>";
            echo "<tr><td>Answered concisely</td><td>{$row[Answered_concisely]}</td></tr>";
            echo "<tr><td>Sent in a timely manner</td><td>{$row[Sent_in_timely_manner]}</td></tr>";
            echo "<tr><td>What would it take to earn higher LTR?</td><td>{$row[What_would_it_take_to_earn_hig]}</td></tr>";
            echo "<tr><td>What would it take to earn 10 LTR?</td><td>{$row[What_would_it_take_to_earn_10_]}</td></tr>";
            echo "<tr><td>Like most about PayPal</td><td>{$row[Like_most_about_PayPal__LTR_]}</td></tr>";
            echo "<tr><td>What could be done differently?</td><td>{$row[What_could_be_done_differently]}</td></tr>";
            echo "<tr><td>What could be done to earn 10 ASAT?</td><td>{$row[What_could_be_done_to_earn_10_]}</td></tr>";
            echo "<tr><td>What teammate did to earn satisfaction?</td><td>{$row[What_teammate_did_to_earn_sati]}</td></tr>";
            echo "<tr><td>How to improve knowledge to handle request?</td><td>{$row[Improve_Knowledge_to_Handle_Re]}</td></tr>";
            echo "<tr><td>How to improve handled professionally?</td><td>{$row[Improve_Handled_Professionally]}</td></tr>";
            echo "<tr><td>How to improve took ownership?</td><td>{$row[Improve_Took_Ownership]}</td></tr>";
            echo "<tr><td>How to improve genuine interest?</td><td>{$row[Improve_Genuine_Interest]}</td></tr>";
            echo "<tr><td>How to improve valued customer?</td><td>{$row[Improve_Valued_Customer]}</td></tr>";
            echo "<tr><td>Why was issue not resolved?</td><td>{$row[Why_issue_not_resolved]}</td></tr>";
            echo "<tr><td>Why are you not sure issue is resolved?</td><td>{$row[Not_sure_issue_is_resolved]}</td></tr>";
            echo "<tr><td>How could we have reduced effort?</td><td>{$row[How_could_have_reduced_custome]}</td></tr>";
            echo "<tr><td>Customer contact count</td><td>{$row[customer_contact_count]}</td></tr>";
            echo "<tr><td>Customer's primary country of residence</td><td>{$row[Customers_Primary_Country_of_]}</td></tr>";
            echo "<tr><td>Talk time</td><td>{$row[Workitem_Phone_talk_time]}</td></tr>";
            echo "<tr><td>KDI email</td><td>{$row[KDI___email]}</td></tr>";
            echo "<tr><td>KDI pone</td><td>{$row[KDI___phone]}</td></tr>";
          echo "</table>";
        }
				$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='raw_data' AND table_schema='concentrix'";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "Column filter: <select name=column_name onChange='location.href=\"?a=$a&b=$b&team=$team&c=\" + this.value;'>";
				echo "<option></option>";
				while($row=$result->fetch_assoc()){
					echo "<option";
					if ($c==$row[column_name]){echo" selected";}
					echo ">".$row[column_name]."</option>";
				}
				echo "</select>";
				echo "<table class='sortable bt' id='queuelist' cellspacing=0 cellpadding=0>";
				echo "<thead><tr><th>%</th><th>Surveys</th><th>$c</th><th>KDI</th><th>CrRR</th><th>NPS</th><th>FCR</th><th>ATT</th></tr></thead>";
				if ($c){
					$column_name=$c;
					$sql = "SELECT distinct $column_name FROM raw_data $sqldater $teamdefinition ORDER by $column_name ASC";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					echo "<select name=data onChange='location.href=\"?a=$a&b=$b&team=$team&c=$column_name&d=\" + this.value;'>";
					echo "<option></option>";
					while($row=$result->fetch_assoc()){
						echo "<option";
						$tmpe = str_replace("&",";and;",$row[$column_name]);
						$tmpe = str_replace("/",";slash;",$tmpe);

						echo " value='$tmpe'";
						$dc = str_replace(";and;","&",$d);
						$dc = str_replace(";slash;","/",$dc);
						if ($dc==$row[$column_name]){echo" selected";}
						echo ">".$row[$column_name]."</option>";
					}
					echo "</select><br><br><br>";
					if($d!=''){
						if($teamdefinition){ $surveydefinition="AND "; }
						else {
							if ($sqldater){ $surveydefinition="AND "; }
							else{ $surveydefinition="WHERE "; }
						}
						$d=html_entity_decode($d);
						$td = str_replace(";and;","&",$d);
						$td = str_replace(";slash;","/",$td);
						$surveydefinition.="$c='$td'";
					}
					else{
						$contract = 5;
						$sql = "SELECT COUNT(*) as id FROM raw_data $sqldater $teamdefinition $surveydefinition";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						$row=$result->fetch_assoc();
						$surveys=$row[id];
						$totalsurveys = $surveys;
						echo "<tfoot>";
						echo "<tr>";
						echo "<td>100%</td>";
						echo "<td>" . $surveys . "</td><td>TOTAL</td>";
						$kdi=sv(5,$surveydefinition,$surveys);
						$crrr=sv(3,$surveydefinition,$surveys);
            $fcr=sv(16,$surveydefinition,$surveys);
						$nps=sv(4,$surveydefinition,$surveys);
						$att=sv(15,$surveydefinition,$surveys);
            list($bg,$fg)=targetcolor($kdi, $contract, 5, $team, $startdate);
						echo "<td style='color:#$fg;background-color:#$bg'>";
						echo $kdi;
						echo "</td>\n";
            list($bg,$fg)=targetcolor($crrr, $contract, 3, $team, $startdate);
						echo "<td style='color:#$fg;background-color:#$bg'>";
						echo  $crrr;
						echo "</td>\n";
            list($bg,$fg)=targetcolor($nps, $contract, 4, $team, $startdate);
						echo "<td style='color:#$fg;background-color:#$bg'>";
						echo $nps;
						echo "</td>\n";
            list($bg,$fg)=targetcolor($fcr, $contract, 16, $team, $startdate);
						echo "<td style='color:#$fg;background-color:#$bg'>";
						echo $fcr;
						echo "</td>\n";
						echo "<td>";
						echo $att;
						echo "</td>\n";
						echo "</tr>\n\n";
						echo "</tr>";
						echo "</tfoot>";
            echo "<tbody>";
						$sql = "SELECT distinct $column_name FROM raw_data $sqldater $teamdefinition ORDER by $column_name ASC LIMIT 100";
						if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						$contract = 5;
						while($row=$result->fetch_assoc()){
							echo "<tr>";
							if($teamdefinition){
								$surveydefinitions="AND ";
							}
							else {
								if ($sqldater){
									$surveydefinitions="AND ";
								}
								else{
									$surveydefinitions="WHERE ";
								}
							}
							$tmpd = str_replace("&",";and;",$row[$c]);
							$tmpd = str_replace("/",";slash;",$tmpd);
							$surveydefinitions.="$c='{$row[$c]}'";
							$sqla = "SELECT COUNT(*) as id FROM raw_data $sqldater $teamdefinition $surveydefinitions LIMIT 50";
							if(!$resulta=$db->query($sqla)){cl($sqla);cl($db->error);}
							$rowa=$resulta->fetch_assoc();
							$surveysa=$rowa[id];
							echo "<td>".round(($surveysa/$totalsurveys)*100) . "%</td>";
							echo "<td>$surveysa</td><td><a href='?&a=$a&b=$b&c=$c&d=";
							echo $tmpd;
							echo "&team=$team'>{$row[$c]}</a></td>\n";
							$kdi=sv(5,$surveydefinitions,$surveysa);
							$crrr=sv(3,$surveydefinitions,$surveysa);
              $fcr=sv(16,$surveydefinitions,$surveysa);
							$nps=sv(4,$surveydefinitions,$surveysa);
							$att=sv(15,$surveydefinitions,$surveysa);
              list($bg,$fg)=targetcolor($kdi, $contract, 5, $team, $startdate);
							echo "<td style='color:#$fg;background-color:#$bg'>";
							echo $kdi;
							echo "</td>\n";
              list($bg,$fg)=targetcolor($crrr, $contract, 3, $team, $startdate);
							echo "<td style='color:#$fg;background-color:#$bg'>";
							echo  $crrr;
							echo "</td>\n";
              list($bg,$fg)=targetcolor($nps, $contract, 4, $team, $startdate);
							echo "<td style='color:#$fg;background-color:#$bg'>";
							echo $nps;
							echo "</td>\n";
              list($bg,$fg)=targetcolor($fcr, $contract, 16, $team, $startdate);
							echo "<td style='color:#$fg;background-color:#$bg'>";
							echo $fcr;
							echo "</td>\n";
							echo "<td>";
							echo $att;
							echo "</td>\n";
							echo "</tr>\n\n";
						}
					}
				}
        echo "</tbody>";
				echo "</table>";
				if($d!=''){
          if (true){
            echo "<table class=bt width=95%>";
            echo "<thead>";
            echo "<tr><th>Metric</th>";

            $startyear = $startdate_year;
            $startmonth = $startdate_month - 11;
            if ($startmonth<1){$startmonth=12+$startmonth;$startyear--;}
            $showyear = $startyear;
            $showmonth = $startmonth;
            for ($x=0;$x<12;$x++){
              if ($showmonth > 12){$showmonth = 1;$showyear++;}
              echo "<th><a href='?a=$a&b=$b&team=$team&c=$c&d=$d&startdate=$showyear-";
              echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
              echo "-01&enddate=$showyear-";
              echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
              echo "-";
              echo str_pad(getlastday($showyear,$showmonth),2,"0",STR_PAD_LEFT);
              echo "'>$month[$showmonth]</a>";
              $showmonth++;
              echo "</th>";
            }
            echo "</tr>";
            echo "</thead>";
            echo "<tr><td>Surveys</td>";
            $showmonth = $startmonth;
            $showyear = $startyear;
            for ($x=1;$x<13;$x++){
              $ldm=cal_days_in_month(CAL_GREGORIAN,$showmonth,$showyear);
              $sde = unixdate_to_exceldate(mktime(0,0,0,$showmonth,1,$showyear));
              $ede = unixdate_to_exceldate(mktime(23,59,59,$showmonth,$ldm,$showyear));
              $tmptimedef = " WHERE teammate_contact_date > '$sde' AND teammate_contact_date < '$ede' ";
              $qsql = "SELECT COUNT(*) as id FROM raw_data $tmptimedef $teamdefinition AND $c = '$d'";
              if(!$qresult=$db->query($qsql)){cl($qsql);cl($db->error);}
              $qrow=$qresult->fetch_assoc(); $surveys = $qrow[id];
              $surveysm[$showyear][$showmonth] = $qrow[id];
              echo "<td>$surveys</td>";
              $showmonth++; if($showmonth>12){$showmonth=1;$showyear++;}
            }
            echo "</tr>";
            $teamname = getteamname($team);
            echo "<tr><td>AHT ($teamname)</td>";
            $showmonth = $startmonth;
            $showyear = $startyear;
            $rounding = gmr(2);
            for ($x=1;$x<13;$x++){
              echo "<td";
            	$monti = $monthy[$showmonth];
            	$dator = $monti.'-'.$showyear;
              $mm = str_pad($showmonth,2,"0",STR_PAD_LEFT);
              $qvalue = vm(2, $team, "$showyear-$mm-01");
              list($bg,$fg) = targetcolor($qvalue, 5, 2, $team, "$showyear-$mm-01");
              echo " style='background-color:#$bg;color:#$fg'>";
              echo round($qvalue,$rounding);
              echo "</td>";
              $showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
            }
            echo "</tr>";
            if ($c == 'Teammate_NT_ID') {
              echo "<tr><td>AHT ($d)</td>";
              $showmonth = $startmonth;
              $showyear = $startyear;
              $rounding = gmr(2);
              $ahtdef = " AND ntid = '$d'";
              for ($x=1;$x<13;$x++){
                echo "<td";
                $monti = $monthy[$showmonth];
            	  $dator = $monti.'-'.$showyear;
                $mm = str_pad($showmonth,2,"0",STR_PAD_LEFT);
                $qvalue = av("aht","$showyear-$mm-01",$ahtdef);
                list($bg,$fg) = targetcolor($qvalue, 5, 2, $team, "$showyear-$mm-01");
                echo " style='background-color:#$bg;color:#$fg'>";
                echo round($qvalue,$rounding);
                echo "</td>";
                $showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
              }
              echo "</tr>";

			        // TODO: RCR
			        echo "<tr><td>RCR</td>";
			        $showmonth = $startmonth;
              $showyear = $startyear;
              $rounding = gmr(17);
              $ahtdef = " AND ntid = '$d'";
              for ($x=1;$x<13;$x++){
                echo "<td";
                $monti = $monthy[$showmonth];
            	  $dator = $monti.'-'.$showyear;
                $mm = str_pad($showmonth,2,"0",STR_PAD_LEFT);
                $qvalue = av("rcr","$showyear-$mm-01",$ahtdef)*100;
                list($bg,$fg) = targetcolor($qvalue, 5, 17, $team, "$showyear-$mm-01");
                echo " style='background-color:#$bg;color:#$fg'>";
                echo round($qvalue,$rounding);
				        echo gms(17);
                echo "</td>";
                $showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
              }
			        echo "</tr>";

			        // TODO: TR
			        echo "<tr><td>TR</td>";
			        $showmonth = $startmonth;
              $showyear = $startyear;
              $rounding = gmr(6);
              $ahtdef = " AND ntid = '$d'";
              for ($x=1;$x<13;$x++){
                echo "<td";
                $monti = $monthy[$showmonth];
            	  $dator = $monti.'-'.$showyear;
                $mm = str_pad($showmonth,2,"0",STR_PAD_LEFT);
                $qvalue = av("tr","$showyear-$mm-01",$ahtdef)*100;
                list($bg,$fg) = targetcolor($qvalue, 5, 6, $team, "$showyear-$mm-01");
                echo " style='background-color:#$bg;color:#$fg'>";
                echo round($qvalue,$rounding);
				        echo gms(6);
                echo "</td>";
                $showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
              }
			        echo "</tr>";
            }
            $qsql = "SELECT metric_id,metric_name FROM metrics WHERE metric_quality = '1' ORDER by metric_name ASC";
            if(!$qresult=$db->query($qsql)){cl($qsql);cl($db->error);}
            $contract = 5;$x=0;
            while($qrow=$qresult->fetch_assoc()){
              echo "<tr><td>$qrow[metric_name]</td>";
              $showmonth = $startmonth;
              $showyear = $startyear;
              for ($x=1;$x<13;$x++){
                $ldm=cal_days_in_month(CAL_GREGORIAN,$showmonth,$showyear);
                echo "<td";
                $sde = unixdate_to_exceldate(mktime(0,0,0,$showmonth,1,$showyear));
                $ede = unixdate_to_exceldate(mktime(23,59,59,$showmonth,$ldm,$showyear));
                $tmptimedef = " WHERE teammate_contact_date > '$sde' AND teammate_contact_date < '$ede' ";
                $qvalue = sv($qrow[metric_id], "AND $c = '$d'", $surveysm[$showyear][$showmonth],$tmptimedef);
                list($bg,$fg) = targetcolor($qvalue, 5, $qrow[metric_id], $team, "$showyear-$showmonth-01");
                echo " style='background-color:#$bg;color:#$fg'>";
                echo round($qvalue,gmr($qrow[metric_id]));
                echo gms($qrow[metric_id]);
                echo "</td>";
                $showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
              }
              echo "</tr>";
            }
          }
          echo "</table>";
					echo "<table class='sortable bt' width=95%><thead><tr><th>Num</th><th>Survey ID</th><th>Queue</th>
          <th>Teammate</th>
          <th>LTR</th>
          <th>Issue Resolved?</th>
          <th>Customer Contact Count</th>
          <th>KDI</th>
          </tr></thead>";
					$sql = "SELECT likely_to_recommend_paypal,issue_resolved,customer_contact_count,customer_account_id,kdi___email,kdi___phone,external_survey_id,$c,queue_source_name,teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition $surveydefinition LIMIT 100";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					$counter=0;
					while($row=$result->fetch_assoc()){
						$counter++;
						echo "<tr>";
						echo "<td>$counter</td>";
						echo "<td><a href='?a=$a&b=$b&c=$c&d=$d&e=$row[external_survey_id]'>$row[external_survey_id]</a>";
						echo "</td>";
						echo "<td><a href='?a=$a&b=$b&c=Queue_Source_Name&d=$row[queue_source_name]'>$row[queue_source_name]</a>";
						echo "</td>";
						echo "<td><a href='?a=$a&b=$b&c=Teammate_Name&d=$row[teammate_name]'>$row[teammate_name]</a>";
						echo "</td>";
            echo "<td";
            if ($row[likely_to_recommend_paypal]>8){echo " class='deltagood'";}
            elseif ($row[likely_to_recommend_paypal]<5){echo " class='deltabad'";}
            else{echo " style='background:#FFEF0F;'";}
            echo ">$row[likely_to_recommend_paypal]</td>";
            echo "<td";
            if ($row[issue_resolved]=="Yes"){echo " style='background-color:#080;color:#8f8;'";}
            else{echo " style='background-color:#800;color:#f88;'";}
            echo ">$row[issue_resolved]</td>";
            echo "<td";
            if ($row[customer_contact_count]<2){echo " style='background-color:#080;color:#8f8;'";}
            else{echo " style='background-color:#800;color:#f88;'";}
            echo ">$row[customer_contact_count]</td>";
            echo "<td>$row[kdi___email]$row[kdi___phone]</td>";
						echo "</tr>\n";
					}
					echo "</table>\n";
				}
			}
      elseif($a=='contracts'){
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
      // targ3ts
      elseif($a=='targets'){
				if(isadmin()){
					echo "<a href='?a=targets&b=add&f=$f'>Add target</a>.<br>";
					echo "<a href='?a=targets&b=autoadjusttargets'>Automatically adjust targets</a>.<br>";
					echo "<a href='?a=targets&b=addgroup'>Add a target group</a>.<br><br>";
					if ($b=='d'){
						if ($c!=''){
							$sql = "UPDATE targets SET active = 0 WHERE target_id = '$c' LIMIT 1";
							if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						}
					}
					if ($b=='e'){
						if ($c!=''){
							if ($_GET[tevaluelow]!=''){
								$tevaluelow=$_GET[tevaluelow];
								$tevaluehigh=$_GET[tevaluehigh];
								$tecolor=$_GET[tecolor];
                $tefcolor=$_GET[tefcolor];
								$testartdate=$_GET[testartdate];
								$testopdate=$_GET[testopdate];
								$temetric=$_GET[temetric];
								$tesubmetric=$_GET[tesubmetric];
								$tecontract=$_GET[tecontract];
								$teteamid=$_GET[teteam];
								$tgtclass=$_GET[teclass];
								$teid=$c;
								$sql = "UPDATE targets SET target_value_low='$tevaluelow',target_value_high='$tevaluehigh',
								target_metric_id='$temetric',target_contract_id='$tecontract',target_team_id='$teteamid',target_start_date='$testartdate',
								target_stop_date='$testopdate',target_color='$tecolor',submetric='$tesubmetric',target_textcolor='$tefcolor',target_class='$tgtclass' WHERE target_id = '$c' LIMIT 1";
								if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
							}
							else {
								$sql = "SELECT * FROM targets WHERE target_id = '$c' LIMIT 1";
								if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
								$tgt=$result->fetch_assoc();
								$tevaluelow = $tgt[target_value_low];
								$tevaluehigh = $tgt[target_value_high];
								$tecolor = $tgt[target_color];
                $tefcolor = $tgt[target_textcolor];
								$testartdate = substr($tgt[target_start_date],0,10);
								$testopdate = substr($tgt[target_stop_date],0,10);
								$temetricid = $tgt[target_metric_id];
								$tecontractid = $tgt[target_contract_id];
								$tesubmetric = $tgt[submetric];
								$teteamid = $tgt[target_team_id];
								$tgtclass = $tgt[target_class];
								echo "<form>
								Target value low: <input name='tevaluelow' value='$tevaluelow'><br>
								Target value high: <input name='tevaluehigh' value='$tevaluehigh'><br>
								Target bgcolor: <input name='tecolor' value='$tecolor'><br>
                Target fgcolor: <input name='tefcolor' value='$tefcolor'><br>
								Target start date: <input type=date name='testartdate' value='$testartdate'> (optional)<br>
								Target stop date: <input type=date name='testopdate' value='$testopdate'> (optional)<br>
	              Metric: <select name='temetric' value='$temetric'>";
	              $sql = "SELECT metric_id,metric_name FROM metrics ORDER by metric_name ASC";
	              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	              while($row=$result->fetch_assoc()){
									$selected='';if($row[metric_id]==$temetricid){$selected=' selected';}
	                echo "<option value='{$row[metric_id]}'$selected>{$row[metric_name]}</option>";
	              }
	              echo "</select><br>
	              Submetric: <select name='tesubmetric'>";
								$selected='';if($tesubmetric==''){$selected=' selected';}
	              echo "<option value=''$selected>Combined</option>";
								$selected='';if($tesubmetric=='phone'){$selected=' selected';}
								echo "<option value='phone'$selected>Phone</option>";
								$selected='';if($tesubmetric=='email'){$selected=' selected';}
								echo "<option value='email'$selected>Email</option>";
	              echo "</select><br>
								Contract: <select name='tecontract'>";
	              $sql = "SELECT contract_id,contract_name FROM contracts ORDER by contract_name ASC";
	              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	              while($row=$result->fetch_assoc()){
									$selected='';if($row[contract_id]==$tecontractid){$selected=' selected';}
									echo "<option value='{$row[contract_id]}'$selected>{$row[contract_name]}</option>";
								}
	              echo "</select><br>
	              Team: <select name='teteam'>";
	              $sql = "SELECT id, team_name FROM teams ORDER BY team_name ASC";
	              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	              while($row=$result->fetch_assoc()){
									$selected='';if($row[id]==$teteamid){$selected=' selected';}
	                echo "<option value='{$row[id]}'$selected>{$row[team_name]}</option>";
	              }
	              echo "</select><br>";
				  echo "Class: <input name=teclass value='$tgtclass'><br>";
								echo "
								<input name=a value=targets type=hidden>
								<input name=b value=e type=hidden>
								<input name=c value='$c' type=hidden>
								<input name=f value='$f' type=hidden>
								<input type=submit>
								</form><br>";
							}
						}
					}
		elseif ($b=='addgroup'){
			echo "<form>";
			echo "Team: <select name='team'>";
			echo "<option value='-1'>All teams</option>";
			$sql = "SELECT id, team_name FROM teams ORDER BY team_name ASC";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			while($row=$result->fetch_assoc()){
				echo "<option value={$row[id]}";
				if ($team==$row[id]){echo " selected";}
				echo ">{$row[team_name]}</option>";
			}
			echo "</select><br>";
			echo "Start date: $startdate<br>";
			echo "End date: $enddate<br>";
            $sql = "SELECT metric_id, metric_name FROM metrics ORDER BY metric_name ASC";
			echo "<table width=100%><thead><tr><th></th>";
            if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            while($row=$result->fetch_assoc()){
				echo "<th>$row[metric_name]</th>";
			}
			echo "<tr>";
			echo "<td>Submetric</td>";
			echo "<td><input name='submetric_aht' size=10></td>";
			echo "<td><input name='submetric_crrr' size=10></td>";
			echo "<td><input name='submetric_kdi' size=10></td>";
			echo "<td><input name='submetric_nps' size=10></td>";
			echo "<td><input name='submetric_pp' size=10></td>";
			echo "<td><input name='submetric_sla' size=10></td>";
			echo "<td><input name='submetric_tr' size=10></td>";
			echo "<td></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>Low</td>";
			echo "<td><input name='low_aht' size=10></td>";
			echo "<td><input name='low_crrr' size=10></td>";
			echo "<td><input name='low_kdi' size=10></td>";
			echo "<td><input name='low_nps' size=10></td>";
			echo "<td><input name='low_pp' size=10></td>";
			echo "<td><input name='low_sla' size=10></td>";
			echo "<td><input name='low_tr' size=10></td>";

			echo "</tr>";
			echo "<tr>";
			echo "<td>High</td>";
			echo "<td><input name='high_aht' size=10></td>";
			echo "<td><input name='high_crrr' size=10></td>";
			echo "<td><input name='high_kdi' size=10></td>";
			echo "<td><input name='high_nps' size=10></td>";
			echo "<td><input name='high_pp' size=10></td>";
			echo "<td><input name='high_sla' size=10></td>";
			echo "<td><input name='high_tr' size=10></td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>Startdate</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>Enddate</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>Color</td>";
			echo "</tr>";

			echo "</tr></thead>";
			echo "</table>";
			echo "</form>";
		}
		elseif ($b=='add'){
            if ($_GET[contract]){
              $sql = "INSERT INTO targets (target_contract_id, target_team_id, target_metric_id, target_value_low, target_value_high,
							target_color,target_start_date,target_stop_date,submetric,target_textcolor)
							VALUES('{$_GET[contract]}','{$_GET[team]}','{$_GET[metric]}','{$_GET[value_low]}','{$_GET[value_high]}','{$_GET[bgcolor]}','{$_GET[startdate]}'
							,'{$_GET[stopdate]}','{$_GET[submetric]}','{$_GET[fgcolor]}')";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            }
						else {
              echo "<form>
							Target value low: <input name='value_low'><br>
							Target value high: <input name='value_high'><br>
							Target bgcolor: <input name='bgcolor'><br>
              Target fgcolor: <input name='fgcolor'><br>
							Target start date: <input type=date name='startdate' value='{$startdate}'> (optional)<br>
							Target stop date: <input type=date name='stopdate' value='{$enddate}'> (optional)<br>
              Metric: <select name='metric'>";
              $sql = "SELECT metric_id,metric_name FROM metrics ORDER by metric_name ASC";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
              while($row=$result->fetch_assoc()){
								$s='';if($f==$row[metric_id]){$s=' selected';}
                echo "<option value='{$row[metric_id]}'$s>{$row[metric_name]}</option>";
              }
              echo "</select><br>
              Submetric: <select name='submetric'>";
              echo "<option value=''>Combined</option>";
							echo "<option value='phone'>Phone</option>";
							echo "<option value='email'>Email</option>";
              echo "</select><br>
							Contract: <select name='contract'>";
              $sql = "SELECT contract_id,contract_name FROM contracts ORDER by contract_name ASC";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
              while($row=$result->fetch_assoc()){ echo "<option value={$row[contract_id]}>{$row[contract_name]}</option>"; }
              echo "</select><br>
              Team: <select name='team'>";
              $sql = "SELECT id, team_name FROM teams ORDER BY team_name ASC";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
              while($row=$result->fetch_assoc()){
								$selected='';if ($row[id]==$team){$selected = ' selected';}
                echo "<option value='{$row[id]}'$selected>{$row[team_name]}</option>";
              }
              echo "</select><br>";
							echo "<input type=hidden name=enddate value='$enddate'>";
							echo "<input type=hidden name=f value='$f'>";
							echo "<input name=a value=targets type=hidden><input name=b value=add type=hidden><input type=submit></form><br>";
						}
					}
				}
				$sql = "SELECT metric_id, metric_name FROM metrics LIMIT 50";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				$x = 0;
				$f=$_GET[f];
				echo "Filter by metric: <select onChange='location.href=\"?a=$a&f=\" + this.value;'>";
				echo "<option value=''>---</option>";
				while($row=$result->fetch_assoc()){
					$x++;
					$metrics[$row[metric_id]] = $row[metric_name];
					$metric[$x] = $row;
					$s='';if($row[metric_id]==$f){$s=' selected';}
					echo "<option value='{$row[metric_id]}'$s>{$row[metric_name]}</option>";
				}
				echo "</select>";
				$mf='';if ($f){$mf=" AND target_metric_id='$f'";}
				$sql = "SELECT id, team_name FROM teams LIMIT 50";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){ $teams[$row[id]] = $row[team_name]; }
				if ($team>0){$teamster="AND target_team_id = '$team'";}
				$sql = "SELECT COUNT(DISTINCT submetric) as id FROM targets WHERE target_start_date <= '$startdate' AND target_stop_date >= '$enddate' $teamster AND active = 1 ORDER by target_team_id ASC, target_metric_id ASC LIMIT 500";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				$row=$result->fetch_assoc(); $submetrics = $row[id];
				$sql = "SELECT DISTINCT submetric FROM targets WHERE target_start_date <= '$startdate' AND target_stop_date >= '$enddate' $teamster AND active = 1 ORDER by target_team_id ASC, target_metric_id ASC LIMIT 500";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				$a = 0;
				while($row=$result->fetch_assoc()){
					$a++; $submetric[$a] = $row[submetric];
				}
				$sql = "SELECT * FROM targets WHERE target_start_date <= '$startdate' AND target_stop_date >= '$enddate' $teamster AND active = 1 ORDER by target_team_id ASC, target_metric_id ASC LIMIT 500";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				while($row=$result->fetch_assoc()){
					$tgtdata[$row[target_id]] = $row;
					$tds[$row[submetric]] = $row;
				}
				/*echo "<table class=tabler><thead><tr><th>submetric</th>";
				foreach($metrics as $key => $value){
					echo "<th>$value</th>";
				}
				echo "</tr></thead>";
				$smc = 1;
				foreach($tgtdata as $data) {
					// First the submetric.
					if ($smc <= $submetrics) {
						echo "<tr>";
						if ($submetric[$smc]==''){$submetric[$smc]='combined';}
						//$spanner = count($tgtdata[$metriccounter][$submetric[$smc]]);
						echo "<td rowspan='{$spanner}'>{$submetric[$smc]}</td>";
						echo "<td>$spanner</td>";
					}
				}
				echo "</table>";*/

				$sql = "SELECT * FROM targets WHERE target_start_date <= '$startdate' AND target_stop_date >= '$enddate' $teamster AND active = 1 $mf ORDER by target_team_id ASC, target_metric_id ASC, submetric ASC, target_value_low ASC LIMIT 500";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "<table class='tabler sortable' cellspacing=0 cellpadding=5>";
				echo "<thead><tr>
				<th>Edit</th>
				<th>Delete</th>
				<th>Team</th>
				<th>Metric</th>
				<th>Submetric</th>
				<th>Low</th>
				<th>High</th>
				<th>Startdate</th>
				<th>Stopdate</th>
				<th>BG</th>
				<th>FG</th>
				<th>Class</th>
				</tr></thead>";
				while($row=$result->fetch_assoc()){
					echo "<tr>";
					$xo = " style='background:#$row[target_color];color:#$row[target_textcolor];'";
					$xx = " class='$row[target_class]'";
					echo "<td$xo><button onClick=\"location.href='?a=targets&b=e&c={$row[target_id]}';\">Edit</button></td>";
					echo "<td$xo><button onClick=\"location.href='?a=targets&b=d&c={$row[target_id]}';\">Delete</button></td>";
					echo "<td$xo>{$teams[$row[target_team_id]]}</td>";
					echo "<td$xo>{$metrics[$row[target_metric_id]]}</td>";
					echo "<td$xo>$row[submetric]</td>";
					echo "<td$xo>$row[target_value_low]</td>";
					echo "<td$xo>$row[target_value_high]</td>";
					echo "<td$xo>$row[target_start_date]</td>";
					echo "<td$xo>$row[target_stop_date]</td>";
					echo "<td$xo>$row[target_color]</td>";
					echo "<td$xo>$row[target_textcolor]</td>";
					echo "<td$xx>$row[target_class]</td>";
					echo "</tr>";
        }
				echo "</table>";
				echo "<h2>Timeless targets {$teams[$team]}</h2>";
				$sql = "SELECT * FROM targets WHERE target_start_date = '0000-00-00 00:00:00' $teamster AND active = 1 $mf ORDER by target_team_id ASC, target_metric_id ASC, submetric ASC, target_value_low ASC LIMIT 500";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "<table class='tabler sortable' cellspacing=0 cellpadding=5>";
				echo "<thead><tr>
				<th>Edit</th>
				<th>Delete</th>
        <th>Team</th>
				<th>Metric</th>
				<th>Submetric</th>
				<th>Low</th>
				<th>High</th>
				<th>Startdate</th>
				<th>Stopdate</th>
				<th>BG</th>
        <th>FG</th>
				</tr></thead>";
        while($row=$result->fetch_assoc()){
          echo "<tr>";
          $xo = " style='background:#$row[target_color];color:#$row[target_textcolor];'";
					echo "<td$xo><button onClick=\"location.href='?a=targets&b=e&c={$row[target_id]}';\">Edit</button></td>";
					echo "<td$xo><button onClick=\"location.href='?a=targets&b=d&c={$row[target_id]}';\">Delete</button></td>";
					echo "<td$xo>{$teams[$row[target_team_id]]}</td>";
					echo "<td$xo>{$metrics[$row[target_metric_id]]}</td>";
					echo "<td$xo>$row[submetric]</td>";
					echo "<td$xo>$row[target_value_low]</td>";
					echo "<td$xo>$row[target_value_high]</td>";
					echo "<td$xo>$row[target_start_date]</td>";
					echo "<td$xo>$row[target_stop_date]</td>";
          echo "<td$xo>$row[target_color]</td>";
          echo "<td$xo>$row[target_textcolor]</td>";
					echo "</tr>";
        }
				echo "</table>";
			}
			elseif($a=='employees'){
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
      elseif($a=='teams'){
        if (isadmin()) {
          echo "<a href='?a=teams&b=add'>Add team</a>.<br><br>";
          if ($b=='add'){
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
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
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
      // m3trics
      elseif($a=='metrics'){
				$sql = "SELECT metric_id,metric_name FROM metrics ORDER by metric_name ASC";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				$contract = 5;
				$x=0;
        while($row=$result->fetch_assoc()){
					$x++;
					echo "<h2>{$row[metric_name]} $teamname $startdate_year</h2>";
					echo "<script>";
					echo "var chartData$x = [";
					$minimum = 10000;
					for ($m=1;$m<13;$m++){
						echo '
						{
							"date": "'.$month[$m].'",
							"';
							echo $row[metric_name];
							echo '": ';
						$mm = str_pad($m,2,"0",STR_PAD_LEFT);
						$lastdayo=cal_days_in_month(CAL_GREGORIAN,$m,$startdate_year);
						if (($row[metric_id]==2) || ($row[metric_id]==8) || ($row[metric_id]==12) || ($row[metric_id]==13) || ($row[metric_id]==14))  {
							$tn[Sweden] = '4';
							$tn[Denmark] = '5';
							$tn[Norway] = '6';
							$tn[Netherlands] = '7';
							$value = vm($row[metric_id],$tn[$teamname],"$startdate_year-$mm-01");
						}
						else {
							$value = getvalue($row[metric_id],"$startdate_year-$mm-01","$startdate_year-$mm-$lastdayo");
						}
						$mul = 1;
						if (($row[metric_id]==8) || ($row[metric_id]==13)){echo round($value*100);$mul=100;}
						elseif (($row[metric_id]==2)){echo round($value);}
						else { echo $value; }
						if ($value*$mul!=0){ if ($minimum>$value*$mul){ $minimum = $value*$mul; } }
						echo ',
							"target": ';
						if ($row[metric_name]=='AHT'){
							$highlow='low';
						}
						else{
							$highlow='high';
						}
						$dator = "$startdate_year-$mm-01";
						if (($row[metric_id]==8) || ($row[metric_id]==13)){echo gettarget($contract,$row[metric_id],$team,$dator,$highlow)*$mul;}
						else { echo gettarget($contract,$row[metric_id],$team,$dator,$highlow); }
						if (gettarget($contract,$row[metric_id],$team,$dator,$highlow) < $minimum){
							$minimum=$mul*gettarget($contract,$row[metric_id],$team,$dator,$highlow);
						}
						echo '},';
					}
					$minimum=$minimum-2;
					echo "
            ];
            var chart$x;

            AmCharts.ready(function () {
                // SERIAL CHART
                chart$x = new AmCharts.AmSerialChart();
                chart$x.addClassNames = true;
                chart$x.dataProvider = chartData$x;
                chart$x.categoryField = 'date';
                chart$x.dataDateFormat = 'YYYY-MM';
                chart$x.startDuration = 1;
                chart$x.color = '#aaa';
                chart$x.marginLeft = 0;

                // AXES
                // category
                var categoryAxis = chart$x.categoryAxis;
                categoryAxis.autoGridCount = true;
                categoryAxis.gridAlpha = 0.3;
                categoryAxis.gridColor = '#403075';
                categoryAxis.axisColor = '#403075';
								categoryAxis.gridPosition = 'start';
								categoryAxis.tickPosition = 'start';

                var valueAxis = new AmCharts.ValueAxis();
                valueAxis.title = '$row[metric_name]';
                valueAxis.gridAlpha = 0.5;
								valueAxis.gridColor = '#403075';
                valueAxis.axisAlpha = 0;
								valueAxis.minimum = $minimum;
                chart$x.addValueAxis(valueAxis);

                var targetAxis = new AmCharts.ValueAxis();
                targetAxis.title = 'target';
                targetAxis.gridAlpha = 0;
                targetAxis.axisAlpha = 0;
                chart$x.addValueAxis(targetAxis);

								var targetGraph = new AmCharts.AmGraph();
								targetGraph.valueField = 'target';
								targetGraph.title = 'Target';
								targetGraph.valueAxis = valueAxis;
								targetGraph.balloonText = '[[value]]';
								targetGraph.legendValueText = '[[value]]';
								targetGraph.legendPeriodValueText = '[[value.average]]';
								chart$x.addGraph(targetGraph);

								// GRAPHS
                // value graph
                var valueGraph = new AmCharts.AmGraph();
                valueGraph.valueField = '$row[metric_name]';
                valueGraph.title = '$row[metric_name]';
                valueGraph.type = 'column';
                valueGraph.fillAlphas = 0.4;
                valueGraph.valueAxis = valueAxis; // indicate which axis should be used
                valueGraph.balloonText = '[[value]]';
                valueGraph.legendValueText = '[[value]]';
								valueGraph.labelText = '[[value]]';
								valueGraph.labelPosition = 'bottom';
                valueGraph.lineColor = '#403075';
								targetGraph.lineColor = '#f00';
                valueGraph.alphaField = 'alpha';
                chart$x.addGraph(valueGraph);

								// LEGEND
                var legend = new AmCharts.AmLegend();
                legend.bulletType = 'round';
                legend.equalWidths = false;
                legend.valueWidth = 120;
                legend.useGraphSettings = true;
                legend.color = '#ccc';
                chart$x.addLegend(legend);

                // WRITE
                chart$x.write('chartdiv$x');
            });
						</script>
					<div id='chartdiv$x' style='width:100%; height:600px; background:#261758;'></div>
					";
				}
			}
      // tr3nds
      elseif($a=='trends'){
        if ($team>0){
          $m = $_GET[m];
          $view_startdate_month=$startdate_month;
          $view_startdate_year=$startdate_year;
          $view_enddate_year=$enddate_year;
          $view_enddate_month=$enddate_month;
          $view_startdate_day="01";
          $view_enddate_day = cal_days_in_month(CAL_GREGORIAN,$view_startdate_month,$view_enddate_year);
          $view_startdate = "$view_startdate_year-$view_startdate_month-01";
          $view_enddate = "$view_startdate_year-$view_startdate_month-$view_enddate_day";
          if ($enddate_day!=$view_enddate_day){echo"<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";}
          if ($view_startdate_month!=$view_enddate_month){echo"<script>location.href='?a=$a&b=$b&c=$c&d=$c&e=$c&startdate=$view_startdate&enddate=$view_enddate';</script>";}
          echo "<h2>12 months rolling until " . $month[$view_startdate_month]." $view_startdate_year</h2>";
          $h = $_GET[h];
          $newh=1;if ($h==1){$newh=0;}
          if ($c == "Teammate_NT_ID"){
            echo "<div id='hidetms' style='cursor:pointer;' onClick='location.href=\"?a=$a&b=$b&c=$c&m=$m&h=$newh\";'><input ";
            if ($h==1){echo "checked ";}
            echo "type=checkbox> Exclude teammates not part of this team.</div>";
          }

          $sql = "SELECT column_name FROM information_schema.columns WHERE table_name='raw_data' AND table_schema='concentrix'";
  				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  				echo "Medallia column: <select name=c onChange='location.href=\"?a=$a&b=$b&team=$team&m=$m&c=\" + this.value;'>";
  				echo "<option></option>";
  				while($row=$result->fetch_assoc()){
  					echo "<option";
  					if ($c==$row[column_name]){echo" selected";}
  					echo ">".$row[column_name]."</option>";
  				}
  				echo "</select>";
				$sql = "SELECT metric_id,metric_name FROM metrics WHERE metric_quality = '1' ORDER by metric_name ASC";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  				$contract = 5;
  				$x=0;
		echo " Quality Metric: <select name=m id=m onChange='location.href=\"?a=$a&b=$b&team=$team&c=$c&m=\" + this.value;'>";
		echo "<option></option>";
		while($row=$result->fetch_assoc()){
			echo "<option value='{$row[metric_id]}'";
			if ($m == $row[metric_id]){echo " selected";}
			echo ">{$row[metric_name]}</option>";
		}
		  echo "</select><br>";
		  $m2 = $_GET[m2];
		  $c2 = $_GET[c2];
		  $sql = "SELECT column_name FROM information_schema.columns WHERE table_name='prt060data' AND table_schema='concentrix'";
  				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  				echo "PRT060p column: <select name=c onChange='location.href=\"?a=$a&b=$b&team=$team&&m2=$m2&m=$m&c2=\" + this.value;'>";
  				echo "<option></option>";
  				while($row=$result->fetch_assoc()){
  					echo "<option";
  					if ($c2==$row[column_name]){echo" selected";}
  					echo ">".$row[column_name]."</option>";
  				}
  				echo "</select>";
          $sql = "SELECT metric_id,metric_name FROM metrics WHERE metric_prt060p = '1' ORDER by metric_name ASC";
          if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  				$contract = 5;
  				$x=0;
          echo " Prt060p Metric: <select name=m2 id=m2 onChange='location.href=\"?a=$a&b=$b&team=$team&c2=$c2&c=$c&m2=\" + this.value;'>";
          echo "<option></option>";
          while($row=$result->fetch_assoc()){
            echo "<option value='{$row[metric_id]}'";
            if ($m2 == $row[metric_id]){echo " selected";}
            echo ">{$row[metric_name]}</option>";
          }
          echo "</select>";

          if (($c2!='') && ($m2!='')){
            echo "<table class='bt sortable' width=95%>";
            echo "<thead>";
            echo "<tr><th>$c2</th>";
            $startyear = $startdate_year;
            $startmonth = $startdate_month - 11;
            if ($startmonth<1){$startmonth=12+$startmonth;$startyear--;}
            $showyear = $startyear;
            $showmonth = $startmonth;
            for ($x=0;$x<12;$x++){
              if ($showmonth > 12){$showmonth = 1;$showyear++;}
              echo "<th><a href='?a=$a&b=$b&team=$team&c2=$c2&m2=$m2&c=$c&d=$d&startdate=$showyear-";
              echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
              echo "-01&enddate=$showyear-";
              echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
              echo "-";
              echo str_pad(getlastday($showyear,$showmonth),2,"0",STR_PAD_LEFT);
              echo "'>$month[$showmonth]</a>";
              $showmonth++;
              echo "</th>";
            }
            echo "</tr>";
            echo "</thead>";
            $curmonth = $startdate_month;
            $curyear = $startdate_year;
            $monti = $monthy[$curmonth] . "-" . $curyear;
            $qsql = "SELECT distinct $c2 FROM prt060data WHERE month = '$monti' $ahtteamdefinition ORDER by $c2 ASC";
  				  $contract = 5;
  				  $teammate_counter=0;
            if(!$qresult=$db->query($qsql)){cl($qsql);cl($db->error);}
            $contract = 5;$x=0;
            $displayme = true;
            while($qrow=$qresult->fetch_assoc()){
              if ($displayme) {
                echo "<tr><td>$qrow[$c2]</td>";
                $showmonth = $startmonth;
                $showyear = $startyear;
				for ($x=1;$x<13;$x++){
					echo "<td";
				$showmonth = str_pad($showmonth,2,"0",STR_PAD_LEFT);
				$qvalue = av($m2,"$showyear-$showmonth-01"," AND $c2 = '{$qrow[$c2]}'");
				if ($qvalue!='--'){
					if ($m2 == 17) { $qvalue *= 100;}
					if ($m2 == 6) { $qvalue *= 100;}
					list($bg,$fg) = targetcolor($qvalue, 5, $m2, $team, "$showyear-$showmonth-01");
					echo " style='background-color:#$bg;color:#$fg;'>";
					echo round($qvalue,gmr($m2));
					echo gms($m2);
				}
				else {echo ">$qvalue";}
				echo "</td>\n";
				$showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
			}
			echo "</tr>";
		}
            }
            echo "</table>";
          }
          if (($c!='') && ($m!='')){
            echo "<table class='bt sortable' width=95%>";
            echo "<thead>";
            echo "<tr><th>Metric</th>";
            $startyear = $startdate_year;
            $startmonth = $startdate_month - 11;
            if ($startmonth<1){$startmonth=12+$startmonth;$startyear--;}
            $showyear = $startyear;
            $showmonth = $startmonth;
            for ($x=0;$x<12;$x++){
              if ($showmonth > 12){$showmonth = 1;$showyear++;}
              echo "<th><a href='?a=$a&b=$b&team=$team&c=$c&d=$d&startdate=$showyear-";
              echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
              echo "-01&enddate=$showyear-";
              echo str_pad($showmonth,2,"0",STR_PAD_LEFT);
              echo "-";
              echo str_pad(getlastday($showyear,$showmonth),2,"0",STR_PAD_LEFT);
              echo "'>$month[$showmonth]</a>";
              $showmonth++;
              echo "</th>";
            }
            echo "</tr>";
            echo "</thead>";
            $m = $_GET[m];
            $qsql = "SELECT distinct $c FROM raw_data $sqldater $teamdefinition ORDER by $c ASC";
  				  $contract = 5;
  				  $teammate_counter=0;
            if(!$qresult=$db->query($qsql)){cl($qsql);cl($db->error);}
            $contract = 5;$x=0;
            $displayme = true;
            while($qrow=$qresult->fetch_assoc()){
              $displayme = true;
              if (($_GET[h]) and ($c == 'Teammate_NT_ID')){
                $displayme = false;
                if (guessteam($qrow[$c])==$team){ $displayme = true; }
              }
              if ($displayme) {
                echo "<tr><td>$qrow[$c]</td>";
                $showmonth = $startmonth;
                $showyear = $startyear;
                for ($x=1;$x<13;$x++){
                  $ldm=cal_days_in_month(CAL_GREGORIAN,$showmonth,$showyear);
                  echo "<td";
                  $sde = unixdate_to_exceldate(mktime(0,0,0,$showmonth,1,$showyear));
                  $ede = unixdate_to_exceldate(mktime(23,59,59,$showmonth,$ldm,$showyear));
                  $tmptimedef = " WHERE teammate_contact_date > '$sde' AND teammate_contact_date < '$ede' ";
                  list($qvalue, $qsurveys) = svs($m, " AND $c = '{$qrow[$c]}'", 0,$tmptimedef);
                  if ($qsurveys > 0) {
                    list($bg,$fg) = targetcolor($qvalue, 5, $m, $team, "$showyear-$showmonth-01");
                    echo " style='background-color:#$bg;color:#$fg;' title='$qsurveys'>";
                    echo round($qvalue,gmr($m));
                    echo gms($m);
                  }
                  else {
                    echo ">";
                  }
                  echo "</td>";
                  $showmonth++;if ($showmonth>12){$showmonth=1;$showyear++;}
                }
                echo "</tr>";
              }
            }
            echo "</table>";
          }
          else {
            echo "<br><br>Pick a data column and metric.";
          }
        }
        else{
          echo "This view only works with a team selected.";}
      }

	  elseif($a=='certification'){
		  echo "Upload a PRT058p report for daily AHT.";
		  echo "<form name=uploadformprt method=post enctype='multipart/form-data'>
		  <input type=hidden name=a value=uploadprt>
		  <input type=file name='filedataprt' id='filedataprt'>
		  <input type=submit></form>";

		  echo "<h1>Week</h1>";
		  echo "<a href='?a=certification&e=useprevious'>Use previous week</a>";
		  if ($_GET[b]=='addntid'){
			  echo "Adding <i>{$_GET[d]}</i> to the list.";
			  $sql = "INSERT INTO certificationdata (ntid,date) VALUES('{$_GET[d]}','$today')";
			  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		  }
		  if ($_GET[b]=='removeid'){
			  if ($_GET[d]=='remove') {
			  	echo "Removing <i>{$_GET[c]}</i> from the list.";
			  	$sql = "DELETE FROM certificationdata WHERE id = {$_GET[c]}";
			  	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
		  }

		echo "<table id='qdtable' class=tabler width=100%>";
		if ($_GET[e]=='useprevious'){
			$today = strtotime("$today -1week");
			$today = date("Y-m-d",$today);
			$e='useprevious';
		}


		$dayofweek = date('w', strtotime($today));
		$dayofweek++;
		$firstdayofweek = 1;
		$lastdayofweek = 5;
		$diff = $dayofweek - $firstdayofweek;

		echo "<thead><th rowspan=3>Name</th>";
		echo "<tr><th colspan=10>PHONE</th><th colspan=10>EMAIL</th></tr>";
		echo "<tr>";

		for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
			$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
			$date_of_day = date('Y-m-d', $dator);
			echo "<th colspan=2>";
			echo date("l",$dator);
			echo "<br>$date_of_day</th>";
		}
		for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
			$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
			$date_of_day = date('Y-m-d', $dator);
			echo "<th colspan=2>";
			echo date("l",$dator);
			echo "<br>$date_of_day</th>";
		}
		echo "</tr><tr><th></th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "<th>#</th><th>AHT</th>";
		echo "</thead>";
		echo "<tr><td>PRT058 uploaded?</td>";
		for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
			$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
			$date_of_day = date('Y-m-d', $dator);
			$exceldator = round(unixdate_to_exceldate($dator));
			$sql = "SELECT id FROM prt058data WHERE date = '$exceldator' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			echo "<td colspan=2>";
			if ($row[id]) {echo "<b>yes</b>";}
			else {echo "<i>no</i>";}
			echo "</td>";
		}
		echo "</tr>";

		$sql = "SELECT DISTINCT teammate_name, teammate_nt_id FROM raw_data ORDER by response_date DESC LIMIT 20";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$tmlist = '<option>--- add ---</option>';
		while($row=$result->fetch_assoc()){
		  $tmlist .= "<option value='" . $row[teammate_nt_id] . "'>{$row[teammate_name]}</option>";
		}
		$sql = "SELECT id,ntid FROM certificationdata WHERE ntid <> '' LIMIT 40";
		  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		  while($row=$result->fetch_assoc()){
			  echo "<tr><td><select onChange='location.href=\"?e=$e&a=certification&b=removeid&c={$row[id]}&d=\" + this.value;' id='cert_ntid_select_{$row[ntid]}'>";
			  echo "<option>$row[ntid]</option>";
			  echo "<option value=remove>--- remove ---</option>";
			  echo "</select></td>";

			  for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
	  			$dator = strtotime("$today -".($diff-$a)."days") . '<br>';
	  			$date_of_day = date('Y-m-d', $dator);
	  			$exceldator = round(unixdate_to_exceldate($dator));
				$sql = "SELECT phone_answered,phone_aht_secs FROM prt058data WHERE ntid = '{$row[ntid]}' AND date = '$exceldator' and queue_name='Total' LIMIT 1";
				if(!$results=$db->query($sql)){cl($sql);cl($db->error);}
				$rows=$results->fetch_assoc();
				// Phone
				echo "<td style='border-bottom: 1px solid #666;'>";
				if ($rows[phone_answered]>0){echo round($rows[phone_answered],0);}
				else {echo "";}
				echo "</td>";
				echo "<td style='border-right: 3px solid #666;border-bottom: 1px solid #666;'>";
				if ($rows[phone_aht_secs]>0){echo round($rows[phone_aht_secs],0);}
				else {echo "";}
				echo "</td>";

			}
			for($a=$firstdayofweek;$a<=$lastdayofweek;$a++){
			  $dator = strtotime("$today -".($diff-$a)."days") . '<br>';
			  $date_of_day = date('Y-m-d', $dator);
			  $exceldator = round(unixdate_to_exceldate($dator));
			  $sql = "SELECT email_worked,email_aht_secs FROM prt058data WHERE ntid = '{$row[ntid]}' AND date = '$exceldator' and queue_name='Total' LIMIT 1";
			  if(!$results=$db->query($sql)){cl($sql);cl($db->error);}
			  $rows=$results->fetch_assoc();
			  // Phone
			  echo "<td style='border-bottom: 1px solid #666;'>";
			  if ($rows[email_worked]>0){echo round($rows[email_worked],0);}
			  else {echo "";}
			  echo "</td>";
			  echo "<td style='border-right: 3px solid #666;border-bottom: 1px solid #666;'>";
			  if ($rows[email_aht_secs]>0){echo round($rows[email_aht_secs],0);}
			  else {echo "";}
			  echo "</td>";

		  }
			echo "</tr>";
		}
		echo "<tr><td><select onChange='location.href=\"?e=$e&a=certification&b=addntid&c={$row[id]}&d=\" + this.value;' id='cert_ntid_select_new'>$tmlist</select></td></tr>";


		  echo "</table>";
	  }
      // s3ttings
      elseif($a=='settings'){
				echo "<h1>Regions</h1>";
        if (isadmin()) {
          echo "<a href='?a=settings&b=add_region'>Add region</a>.<br><br>";
          if ($b=='add_region'){
            if ($_GET[region_name]){
              $sql = "INSERT INTO regions (region_name, region_admin_user_id) VALUES('{$_GET[region_name]}','$uid')";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            }
            else {
              echo "<form>
              Region name: <input name='region_name'><br>
              <input name=a value=settings type=hidden><input name=b value=add_region type=hidden>
              <input type=submit></form><br>";
            }
          }
        }
        $sql = "SELECT * FROM regions ORDER by region_name ASC";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        while($row=$result->fetch_assoc()){
          echo "<div class='tbl_region'><a href='?a=settings&b=show_region&region={$row[region_id]}'>".$row[region_name]."</a></div>";
        }
				echo "<h1>Metrics</h1>";
        if (isadmin()) {
          echo "<a href='?a=settings&b=add_metric'>Add metric</a>.<br><br>";
          if ($b=='add_metric'){
            if ($_GET[metric_name]){
              $sql = "INSERT INTO metrics (metric_name, contract_id) VALUES('{$_GET[metric_name]}','{$_GET[contract_id]}')";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            }
            else {
              echo "<form>
              Metric name: <input name='metric_name'><br>
              Contract: <select name='contract_id'>";
              $sql = "SELECT contract_id,contract_name FROM contracts";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
              while($row=$result->fetch_assoc()){
                  echo "<option value={$row[contract_id]}>{$row[contract_name]}</option>";
              }
              echo "</select><br>
              <input name=a value=settings type=hidden><input name=b value=add_metric type=hidden>
              <input type=submit></form><br>";
            }
          }
        }
        $sql = "SELECT * FROM metrics ORDER by metric_name ASC";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        echo "<table class=tabler>";
        echo "<thead><th>Metric</th><th>Symbol</th><th>Rounding</th><th>Quality metric?</th></thead>";
        while($row=$result->fetch_assoc()){
          echo "<tr>";
          echo "<td><a href='?a=settings&b=show_metric&metric={$row[metric_id]}'>".$row[metric_name]."</a></td>";
          echo "<td>{$row[metric_symbol]}</td>";
          echo "<td>{$row[metric_rounding]}</td>";
          echo "<td>{$row[metric_quality]}</td>";
          echo "</tr>";
        }
        echo "</table>";
				echo "<h1>Users</h1>";
        if (isadmin()) {
          echo "<a href='?a=settings&b=add_user'>Add user</a>.<br><br>";
          if ($b=='add_user'){
            if ($_GET[user_name]){
              $sql = "INSERT INTO users (user_name) VALUES('{$_GET[user_name]}')";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
							$sql = "SELECT user_id FROM users WHERE user_name = '{$_GET[user_name]}' LIMIT 1";
							if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();$newuserid=$row[user_id];
							showerror("User {$_GET[user_name]} has been created. Send the user this link to create a password:<br> http://7612.uk/?n={$newuserid}");
            }
            else {
              echo "<form>
              user name: <input name='user_name'><br>
              <input name=a value=settings type=hidden><input name=b value=add_user type=hidden>
              <input type=submit></form><br>";
            }
          }
        }
        $sql = "SELECT * FROM users ORDER by user_name ASC";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        while($row=$result->fetch_assoc()){
          echo "<div class='tbl_user'><a href='?a=settings&b=show_user&user={$row[user_id]}'>".$row[user_name]."</a></div>";
        }

				if (isadmin()){
					echo "<h1>Data</h1>";
					echo "Upload a Medallia report.";
					echo "<form name=uploadform method=post enctype='multipart/form-data'>
					<input type=hidden name=a value=upload>
					<input type=file name='filedata' id='filedata'>
					<input type=submit></form>";

					echo "Upload a PRT060 report for individual AHT, volumes and RCR.";
					echo "<form name=uploadformaht method=post enctype='multipart/form-data'>
					<input type=hidden name=a value=uploadaht>
					<input type=file name='filedataaht' id='filedataaht'>
					<input type=submit></form>
					";

					echo "Upload a VM055p report for team AHT & TR.";
					echo "<form name=uploadformvm method=post enctype='multipart/form-data'>
					<input type=hidden name=a value=uploadvm055p>
					<input type=file name='filedatavm' id='filedatavm'>
					<input type=submit></form>
					";

          echo "Upload a PRT073 report for team RCR.";
					echo "<form name=uploadformprt073 method=post enctype='multipart/form-data'>
					<input type=hidden name=a value=uploadprt073>
					<input type=file name='filedataprt073' id='filedataprt073'>
					<input type=submit></form>
					";

				}
			}
			elseif($a=='qds'){
				$lastday=cal_days_in_month(CAL_GREGORIAN,$startdate_month,$startdate_year);
        $pstartdate_year = $startdate_year;
        $pstartdate_month = $startdate_month - 1;
        if ($startdate_month == '01') { $pstartdate_year--; $pstartdate_month = '12'; }
				$plastday = cal_days_in_month(CAL_GREGORIAN,$pstartdate_month,$pstartdate_year);
				$previous_month = date("m",mktime(0,0,0,$startdate_month-1,1,$startdate_year));
				$previous_year = date("Y",mktime(0,0,0,$startdate_month-1,1,$startdate_year));
				$startdate_excel = unixdate_to_exceldate(mktime(0,0,0,$startdate_month,1,$startdate_year));
				$enddate_excel = unixdate_to_exceldate(mktime(23,59,59,$startdate_month,$lastday,$startdate_year));
				$pstartdate_excel = unixdate_to_exceldate(mktime(0,0,0,$startdate_month-1,1,$startdate_year));
				$penddate_excel = unixdate_to_exceldate(mktime(23,59,59,$startdate_month-1,$plastday,$startdate_year));
				$previous_date = "$previous_year-$previous_month-01";
				$sqldater = " WHERE Teammate_Contact_Date > $startdate_excel";
				$sqldater .= " AND Teammate_Contact_Date < $enddate_excel";
				$psqldater = " WHERE Teammate_Contact_Date > $pstartdate_excel";
				$psqldater .= " AND Teammate_Contact_Date < $penddate_excel";
				$ahttgt = gettarget(5,2,$team,$startdate,"low");
				$crrrtgt = gettarget(5,3,$team,$startdate,"high");
				$npstgt = gettarget(5,4,$team,$startdate,"high");
				$kditgt = gettarget(5,5,$team,$startdate,"high");
				if ($b=='aq'){
					echo "Last month, <a href='?a=employees&b=show_employee&c=$c'>$c</a>'s biggest opportunity was {$_GET[e]}.<br>";
					echo "The following is a table of all surveys where {$_GET[e]} was not met.<br>";
					echo "Click on one of the surveys to mark it as reviewed:<br><br>";
					if ($_GET[e]=='KDI'){
						$kpifilter = "AND ((kdi___email < $kditgt AND kdi___phone = '') OR (kdi___email = '' AND kdi___phone < $kditgt))";
					}
					$sql = "SELECT kdi___email,kdi___phone,external_survey_id FROM raw_data $sqldater AND teammate_nt_id = '$c' $kpifilter ORDER by external_survey_id LIMIT 10";
					cl($sql);
	       	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					$counta = 0;
					while($row=$result->fetch_assoc()){
						$counta++;
						echo "$counta. <a href='?a=qds&b=aqq&c={$row[external_survey_id]}'>{$row[external_survey_id]}</a><br>";
					}
					echo "<br>";
				}
				echo "QD overview for Team $teamname in $month[$startdate_month] $startdate_year.<br>";
				$sql = "SELECT distinct Teammate_NT_ID,Teammate_Name FROM raw_data $sqldater ORDER by Teammate_Name ASC";
       	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				$empnumber = 0;
				echo "<table id=qdtable cellspacing=0 class='tabler'>";
				echo "<thead>";
				echo "<tr><th rowspan=2>Pos</th><th rowspan=2>Teammate</th>
				<th colspan=3>AHT</th>
				<th colspan=3>KDI</th>
				<th colspan=3>CrRR</th>
				<th colspan=3>NPS</th>
				<th colspan=8>QD</th>
				</tr>";
				echo "<tr>
				<th>$previous_month</th>
				<th>$startdate_month</th>
				<th>&Delta;</th>
				<th>$previous_month</th>
				<th>$startdate_month</th>
				<th>&Delta;</th>
				<th>$previous_month</th>
				<th>$startdate_month</th>
				<th>&Delta;</th>
				<th>$previous_month</th>
				<th>$startdate_month</th>
				<th>&Delta;</th>
				<th class=qdth>#1</th>
				<th class=qdth>#2</th>
				<th class=qdth>#3</th>
				<th class=qdth>#4</th>
        <th class=qdth>#5</th>
				<th class=qdth>#6</th>
				<th class=qdth>#7</th>
				<th class=qdth>#8</th>
				</tr>";
				echo "</thead>";
       	while($row=$result->fetch_assoc()){
					$bo_metric="";
					$bo_value=0;
					$empteam=guessteam($row[Teammate_NT_ID]);
					if ($team>-1){
						if ($empteam == $team) {
							$empnumber++;
							echo "<tr>";
							echo "<td>$empnumber.</td>";
							echo "<td>".substr($row[Teammate_Name],0,11)."</td>";

							//aht
							$aht = av("paht",$startdate,"AND ntid = '$row[Teammate_NT_ID]'");
							$rsqldater = $sqldater;$sqldater = $psqldater;
							$paht = av("paht",$previous_date,"AND ntid = '$row[Teammate_NT_ID]'");
							$sqldater = $rsqldater;
							$delta = round($aht-$paht,0);
							$deltaproc = round(100*$delta/$paht);
							if ($deltaproc>$bo_value){$bo_value=-$deltaproc;$bo_metric="AHT";}

							echo "<td";
							echo ">".round($paht)."</td>";
							echo "<td";
							echo ">".round($aht)."</td>";
							echo "<td style='background-color:#";
							if ($delta>0){echo '660000;color:#ff0000;';}
							elseif ($delta<0){echo '006600;color:#00ff00;';}
							echo "'>$delta</td>";

							//kdi
							$surveys = countsurveys($row[Teammate_NT_ID]);
							$kdi = sv(5,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
              $rsqldater = $sqldater;

              $sqldater = $psqldater;
              $psurveys = countsurveys($row[Teammate_NT_ID]);
							$pkdi = sv(5,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);

							$sqldater = $rsqldater;
							$delta = round($kdi-$pkdi,0);
							$deltaproc = round(100*$delta/$pkdi);
							if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="KDI";}
							echo "<td";
							echo ">".round($pkdi)."</td>";
							echo "<td";
							echo ">".round($kdi)."</td>";
							echo "<td style='background-color:#";
							if ($delta>0){echo '006600;color:#00ff00;';}
							elseif ($delta<0){echo '660000;color:#ff0000;';}
							echo "'>$delta</td>";

							//crrr
							$crrr = sv(3,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
							$rsqldater = $sqldater;$sqldater = $psqldater;
							$pcrrr = sv(3,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);
							$sqldater = $rsqldater;
							$delta = round($crrr-$pcrrr,0);
							$deltaproc = round(100*$delta/$pcrrr);
							if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="CrRR";}
							echo "<td";
							echo ">".round($pcrrr)."</td>";
							echo "<td";
							echo ">".round($crrr)."</td>";
							echo "<td style='background-color:#";
							if ($delta>0){echo '006600;color:#00ff00;';}
							elseif ($delta<0){echo '660000;color:#ff0000;';}
							echo "'>$delta</td>";

							//nps
							$nps = sv(4,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
							$rsqldater = $sqldater;$sqldater = $psqldater;
							$pnps = sv(4,"AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);
							$sqldater = $rsqldater;
							$delta = round($nps-$pnps,0);
							$deltaproc = round(100*$delta/$pnps);
							//if ($nps>$pnps){$deltaproc=abs($deltaproc);}
							if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="NPS";}
							echo "<td";
							echo ">".round($pnps)."</td>";
							echo "<td";
							echo ">".round($nps)."</td>";
							echo "<td style='background-color:#";
							if ($delta>0){echo '006600;color:#00ff00;';}
							elseif ($delta<0){echo '660000;color:#ff0000';}
							echo "'>$delta</td>";

							$sqla = "SELECT external_survey_id,qd_id FROM qds WHERE user_id = '$uid' AND teammate_nt_id = '{$row[Teammate_NT_ID]}' AND month = '$startdate_year-$startdate_month' LIMIT 4";
							if(!$resulta=$db->query($sqla)){cl($sqla);cl($db->error);}
							$qds = 0;
							while($rowa=$resulta->fetch_assoc()){
								$qds++;
								echo "<td><a href='?a=qds&b=q&c={$rowa[qd_id]}'>{$rowa[qd_id]}</a></td>";
							}
							for ($x=$qds+1;$x<5;$x++){
								echo "<td><a href='?a=qds&b=aq&c={$row[Teammate_NT_ID]}&d=$startdate_year-$startdate_month&e=$bo_metric'>Add</a></td>";
							}
							echo "</tr>";
						}
					}
				}

			}
			elseif($a=='mystats'){
				if ($team>0){
					echo "<h2>Team ". getteamname($team) ."</h2>";
				}
				else {
					echo "<h2>All Teams</h2>";
				}
				$daychosen = $today;
				if ($_GET[showday]){$daychosen=$_GET[showday];}
				echo "<input onChange='location.href=\"?a=$a&team=$team&showday=\" + this.value;' type=date value='$daychosen'>";
				if ($daychosen != $today){
					echo " <a href='?a=$a&team=$team&showday=$today'>Show today</a>";
				}
				if ($_GET[msid]){
					$msid = $_GET[msid];
					$sql = "SELECT completed FROM mystats WHERE msid = '$msid'";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					$row=$result->fetch_assoc();
					$newcompleted=0;if($row[completed]==0){$newcompleted=1;}
					$sql = "UPDATE mystats SET completed = '$newcompleted' WHERE msid = {$_GET[msid]}";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				}

				$sql = "SELECT * FROM mystats WHERE msdate = '$daychosen'";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
				echo "<table border=1>";
				echo "<th>ntid</th>";
				echo "<th>empid</th>";
				echo "<th>starttime</th>";
				echo "<th>Extend break by</th>";
				echo "<th>Trn/Mtg Minutes</th>";
				echo "<th>Trn/Mtg Explanation</th>";
				echo "<th>Completed</th>";
				while($row=$result->fetch_assoc()){
					if (($team<0) or (guessteam($row[ntid])==$team)){
						echo "<tr>";
						echo "<td>" . $row[ntid] . "</td>";
						echo "<td>" . $row[empid] . "</td>";
						echo "<td>" . $row[starthour] . ":" . $row[startmin]. "</td>";
						echo "<td>" . $row[deduct] . "</td>";
						echo "<td>" . $row[trnmtgmins] . "</td>";
						echo "<td>" . $row[trnmtgexplanation] . "</td>";
						$totaldeduct += $row[deduct];
						$totaltrnmtg += $row[trnmtgmins];
						$cc='c20';
						$msid = $row[msid];
						$checked='';if ($row[completed]>0){$checked=' checked';$cc='02c';}
						echo "<td onClick='location.href=\"?a=$a&b=$b&c=$c&d=$d&startdate=$startdate&enddate=$enddate&team=$team&showday=$daychosen&msid=$msid\";' style='background-color:#$cc'><input type='checkbox'$checked>";
						echo "</td>";
						echo "</tr>";
					}
				}
				echo "</table>";
				echo "Total deducted break minutes: $totaldeduct.<br>";
				echo "Total training/meeting minutes: $totaltrnmtg.<br>";
			}
      elseif($a=='dashboard') {
        $sql = "SELECT team_name,id FROM teams";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        $teams = 0;
        while($row=$result->fetch_assoc()){
          $teams++;
          $teamname = $row[team_name];
          $teamid = $row[id];
          $sqla = "SELECT * FROM team_data_definitions WHERE team_id = $teamid";
          if(!$resulta=$db->query($sqla)){cl($sqla);cl($db->error);}
          $definitions = 0;
          $teamdef[$teamname] = '';
          $tdef[$teamid] = '';
          while($rowa=$resulta->fetch_assoc()){
            if ($definitions){$teamdef[$teamname].=' OR ';}
            if ($definitions){$tdef[$teamid].=' OR ';}
            $teamdef[$teamname] .= $rowa[raw_data_column] . "='{$rowa[raw_data_data]}'";
            $tdef[$teamid] .= $rowa[raw_data_column] . "='{$rowa[raw_data_data]}'";
            $definitions++;
          }
          if ($teamdef[$teamname]){$teamdef[$teamname]=" AND (".$teamdef[$teamname] . ")";}
          if ($tdef[$teamid]){$tdef[$teamid]=" AND (".$tdef[$teamid] . ")";}
        }

        echo "<table id='dashboard' cellspacing=0 cellpadding=0>";

        $teamdefinition=$teamdef['Denmark']; $dksurveys = surveycount();
        $teamdefinition = $teamdef['Netherlands'];$nlsurveys=surveycount();
        $teamdefinition = $teamdef['Norway'];$nosurveys=surveycount();
        $teamdefinition = $teamdef['Sweden'];$sesurveys=surveycount();
        echo "<tr><td></td>
          <td class='sectionheader'><a href='?a=team&b=5'>DK</a><span class='sectioncounter xtoggle' title='Surveys received'>$dksurveys</span>
          <div class='latest xtoggle'>".getlatest($teamdef['Denmark'])."</div></td>
          <td class='sectionheader'><a href='?a=team&b=7'>NL</a><span class='sectioncounter xtoggle' title='Surveys received'>$nlsurveys</span>
          <div class='latest xtoggle'>".getlatest($teamdef['Netherlands'])."</div></td>
          <td class='sectionheader'><a href='?a=team&b=6'>NO</a><span class='sectioncounter xtoggle' title='Surveys received'>$nosurveys</span>
          <div class='latest xtoggle'>".getlatest($teamdef['Norway'])."</div></td>
          <td class='sectionheader'><a href='?a=team&b=4'>SE</a><span class='sectioncounter xtoggle' title='Surveys received'>$sesurveys</span>
          <div class='latest xtoggle'>".getlatest($teamdef['Sweden'])."</div></td>
        </tr>";

        echo "<tr><td class='dashboard-divider'>Core Metrics</td></tr>";
				echo "<tr><td class='sectionname'><b>AHT</b></td>";echo displayvmbox(5,2);echo displayvmbox(7,2);echo displayvmbox(6,2);echo displayvmbox(4,2);echo "</tr>";
        echo "<tr><td class='sectionname'><b>KDI</b></td>";echo displaydashboardbox(5,5);echo displaydashboardbox(7,5);echo displaydashboardbox(6,5);echo displaydashboardbox(4,5);echo "</tr>";
        echo "<tr><td class='sectionname'><b>TR</b></td>";
          $pvalue[dk]=vm("pvol",5,$startdate);$pvalue[nl]=vm("pvol",7,$startdate);$pvalue[no]=vm("pvol",6,$startdate);$pvalue[se]=vm("pvol",4,$startdate);
          $evalue[dk]=vm("evol",5,$startdate);$evalue[nl]=vm("evol",7,$startdate);$evalue[no]=vm("evol",6,$startdate);$evalue[se]=vm("evol",4,$startdate);
          $ptr[dk]=vm("ptr",5,$startdate);$ptr[nl]=vm("ptr",7,$startdate);$ptr[no]=vm("ptr",6,$startdate);$ptr[se]=vm("ptr",4,$startdate);
          $etr[dk]=vm("etr",5,$startdate);$etr[nl]=vm("etr",7,$startdate);$etr[no]=vm("etr",6,$startdate);$etr[se]=vm("etr",4,$startdate);
          $value=round((($etr[dk]*$evalue[dk])+($ptr[dk]*$pvalue[dk]))/($evalue[dk]+$pvalue[dk])*100,1);echo displayvmbox(5,6,$value);
          $value=round((($etr[nl]*$evalue[nl])+($ptr[nl]*$pvalue[nl]))/($evalue[nl]+$pvalue[nl])*100,1);echo displayvmbox(7,6,$value);
          $value=round((($etr[no]*$evalue[no])+($ptr[no]*$pvalue[no]))/($evalue[no]+$pvalue[no])*100,1);echo displayvmbox(6,6,$value);
          $value=round((($etr[se]*$evalue[se])+($ptr[se]*$pvalue[se]))/($evalue[se]+$pvalue[se])*100,1);echo displayvmbox(4,6,$value);
        echo "</tr>";
        echo "<tr><td class='sectionname'><b>RCR</b></td>";echo displayvmbox(5,17);echo displayvmbox(7,17);echo displayvmbox(6,17);echo displayvmbox(4,17);echo "</tr>";
				echo "<tr><td class='dashboard-divider'>Kickers and Legacy Metrics</td></tr>";
        echo "<tr><td class='sectionname'>NPS</td>";echo displaydashboardbox(5,4);echo displaydashboardbox(7,4);echo displaydashboardbox(6,4);echo displaydashboardbox(4,4);echo "</tr>";
        echo "<tr><td class='sectionname'>CrRR</td>";echo displaydashboardbox(5,3);echo displaydashboardbox(7,3);echo displaydashboardbox(6,3);echo displaydashboardbox(4,3);echo "</tr>";
        echo "<tr><td class='sectionname'>FCR</td>";echo displaydashboardbox(5,16);echo displaydashboardbox(7,16);echo displaydashboardbox(6,16);echo displaydashboardbox(4,16);echo "</tr>";
        echo "<tr><td class='dashboard-divider'>Miscellaneous</td></tr>";
				echo "<tr><td class='sectionname'>Phone Volume</td>";echo displayvmbox(5,12,$pvalue[dk]);echo displayvmbox(7,12,$pvalue[nl]);echo displayvmbox(6,12,$pvalue[no]);
        echo displayvmbox(4,12,$pvalue[se]);echo "</tr>";

        echo "<tr><td class='sectionname'>Email Volume</td>";echo displayvmbox(5,14,$evalue[dk]);echo displayvmbox(7,14,$evalue[nl]);echo displayvmbox(6,14,$evalue[no]);
        echo displayvmbox(4,14,$evalue[se]);echo "</tr>";

        echo "<tr><td class='sectionname'>Combined Volume</td>";echo displayvmbox(5,14,$evalue[dk]+$pvalue[dk]);echo displayvmbox(7,14,$evalue[nl]+$pvalue[nl]);echo displayvmbox(6,14,$evalue[no]+$pvalue[no]);
        echo displayvmbox(4,14,$evalue[se]+$pvalue[se]);echo "</tr>";

        echo "<tr><td class='sectionname'>Email vs Phone Balance</td>";
          echo displayvmbox(5,19,round($evalue[dk]/$pvalue[dk]*100));
          echo displayvmbox(7,19,round($evalue[nl]/$pvalue[nl]*100));
          echo displayvmbox(6,19,round($evalue[no]/$pvalue[no]*100));
          echo displayvmbox(4,19,round($evalue[se]/$pvalue[se]*100));
        echo "</tr>";

        echo "<tr><td class='sectionname'>Surveys vs Volume</td>";
          echo displayvmbox(5,20,round($dksurveys/($evalue[dk]+$pvalue[dk])*100));
          echo displayvmbox(7,20,round($nlsurveys/($evalue[nl]+$pvalue[nl])*100));
          echo displayvmbox(6,20,round($nosurveys/($evalue[no]+$pvalue[no])*100));
          echo displayvmbox(4,20,round($sesurveys/($evalue[se]+$pvalue[se])*100));
        echo "</tr>";
        echo "<tr><td class='sectionname'>SL Phone</td>";
          echo displayvmbox(5,8);
          echo displayvmbox(7,8);
          echo displayvmbox(6,8);
          echo displayvmbox(4,8);
        echo "</tr>";
        echo "<tr><td class='sectionname'>SL Email</td>";
          echo displayvmbox(5,13);
          echo displayvmbox(7,13);
          echo displayvmbox(6,13);
          echo displayvmbox(4,13);
        echo "</tr>";
        echo "<tr><td class='sectionname'>TR Phone</td>";
          echo displayvmbox(5,6,round($ptr[dk]*100,1));
          echo displayvmbox(7,6,round($ptr[nl]*100,1));
          echo displayvmbox(6,6,round($ptr[no]*100,1));
          echo displayvmbox(4,6,round($ptr[se]*100,1));
        echo "</tr>";
        echo "<tr><td class='sectionname'>TR Email</td>";
          echo displayvmbox(5,6,round($etr[dk]*100,1));
          echo displayvmbox(7,6,round($etr[nl]*100,1));
          echo displayvmbox(6,6,round($etr[no]*100,1));
          echo displayvmbox(4,6,round($etr[se]*100,1));
        echo "</tr>";
        echo "</table>";
        echo "</div>";
      }
      echo "</div>";
			//echo "<div class='z-button showextra' onClick='toggleShowExtra();'>toggle extra</div>";
    }
	}
}
if ($forked) {
	//echo "<div class='forked z-button'>forked</div>";
}
echo "</body></html>";
$db->close();
?>
