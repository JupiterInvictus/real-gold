<?php

$application_name = "Concentrix Global Online Leader Dashboard";
$application_copyright = "Copyright Concentrix Europe Ltd 2016";
$application_contact = "joakim.saettem@concentrix.com";
set_time_limit(600);
session_start();
$application_version_major = "2016-11-02";
$application_version_minor = "08:37";
$month['01']="January";$month['02']="February";$month['03']="March";$month['04']="April";$month['05']="May";$month['06']="June";$month['07']="July";$month['08']="August";
$month['09']="September";$month['10']="October";$month['11']="November";$month['12']="December";
$month['1']="January";$month['2']="February";$month['3']="March";$month['4']="April";$month['5']="May";$month['6']="June";$month['7']="July";$month['8']="August";
$month['9']="September";
$monthy['01']='JAN';$monthy['02']='FEB';$monthy['03']='MAR';$monthy['04']='APR';$monthy['05']='MAY';$monthy['06']='JUN';
$monthy['07']='JUL';$monthy['08']='AUG';$monthy['09']='SEP';$monthy['10']='OCT';
$monthy['11']='NOV';$monthy['12']='DEC';

error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$uid = $_SESSION[user_id];
include "../../zm-core-db.php"; $ddb = 'concentrix'; db_connect();
include "../../zm-core-login.php";
include "../../zm-core-functions.php";

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
		for($a = 1; $a<=$columnnumber; $a++){ $sql = "alter table ahtreport_data add {$column[$a]} text";	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}	}

		// Add the data.
		for ($a = 7; $a<$rownumber; $a++){
			// Try to find out if the data already exists.
			$sql = "SELECT id FROM ahtreport_data WHERE month = '{$data[$a][$monthcolumn]}' AND ntid = '{$data[$a][$ntidcolumn]}' AND queue_name = '{$data[$a][$queuenamecolumn]}' LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[id]>0){
				$sql = "DELETE FROM ahtreport_data WHERE id = '{$row[id]}' LIMIT 1";
				if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			}
			$sql="INSERT INTO ahtreport_data (";for ($b=1;$b<=$columnnumber;$b++){$sql.="{$column[$b]},";}$sql=substr($sql,0,-1);$sql.=") VALUES(";
			for ($b=1;$b<=$columnnumber;$b++){$data[$a][$b]=$db->real_escape_string($data[$a][$b]);$sql.="'{$data[$a][$b]}',";}$sql=substr($sql,0,-1);$sql.=')';
 			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		}
	}
	echo "Done.";
	setsetting('prt060pdata',date("Y-m-d H:i:s"));
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
			echo "moo";
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
  global $_GET;
  echo "<div id='{$name}' onmouseup=\"location.href='?a={$name}';\" class='leftchoice";
  if ($_GET[a]==$name) {echo " leftchoiceselected";}
  echo "'><img src='images/{$name}.png'> ".ucfirst($name)."</div>";
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
function ismgr() {
  global $db, $uid;
  $sql = "SELECT user_manager FROM users WHERE user_id={$uid} LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}$row=$result->fetch_assoc();
  return $row[user_manager];
}
function getmanager($username){
	global $db;
	$sql = "SELECT team_leader_name FROM raw_data WHERE teammate_nt_id = '{$username}' LIMIT 1";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  $row=$result->fetch_assoc();
	return $row[team_leader_name];
}
function guessteam($username){
	global $db;
	$manager = getmanager($username);
	// this will only work for a very specific situation...
	$sql = "SELECT raw_data_column FROM team_data_definitions LIMIT 1";
 	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	$column=$row[raw_data_column];
	// Sample size of 3 should suffice
	$sql = "SELECT $column FROM raw_data WHERE team_leader_name = '$manager' LIMIT 3";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){ $queue[$row[$column]]++; } arsort($queue);
	$quenumb = 0;
	foreach($queue as $que => $queuenum) { $topqueue[$quenumb] = $que; $quenumb++; }
	$sql = "SELECT id FROM team_data_definitions WHERE raw_data_column = '$column' AND raw_data_data = '{$topqueue[0]}'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[team_id];
}
function getteamname($team_id){
	global $db;
	$sql = "SELECT team_name FROM teams WHERE team_id = '$team_id'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	return $row[team_name];
}
function targetcolor($value, $contract, $metric, $team, $date, $submetric) {
	if ($value=='--'){return'';}
	if ($team){
		global $db;
		// There should only be one match.
		if (!$value){return '444';}
		$sub=" AND submetric = '$submetric'";
		$sql = "SELECT target_color FROM targets WHERE target_value_low <= $value AND target_value_high >= $value-0.01 AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND target_start_date <= '$date' AND target_stop_date >= '$date' $sub LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  	$row=$result->fetch_assoc();
		if ($row[target_color]==''){
			$date = "0000-00-00 00:00:00";
			$sql = "SELECT target_color FROM targets WHERE target_value_low <= $value AND target_value_high >= $value-0.01 AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND target_start_date <= '$date' AND target_stop_date >= '$date' $sub LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	  	$row=$result->fetch_assoc();

		}
		return $row[target_color];
	}
	else {return '444';}
}
function gettarget($contract,$metric,$team,$date,$highorlow,$submetric){
	if($team){
		global $db;
		if ($submetric){$submetric="AND submetric='$submetric'";}
		else {
			$submetric = "AND submetric=''";
		}
		$tvhol = 'target_value_' . $highorlow;
		$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '8b0000' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' $submetric LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){
			$sql = "SELECT $tvhol FROM targets WHERE target_color = '8b0000' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_start_date = '0000-00-00 00:00:00' AND target_stop_date = '0000-00-00 00:00:00' AND target_team_id='$team' $submetric LIMIT 1";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$row=$result->fetch_assoc();
			if ($row[$tvhol]==''){return 0;}
		}
		return $row[$tvhol];
	}
	return 0;
}
function getsubtarget($contract,$metric,$team,$date,$submetric){
	global $db;
	$hol='low';
	if ($metric==2){$hol='high';}
	$tvhol = "target_value_$hol";
	$sql = "SELECT $tvhol FROM targets WHERE target_start_date <= '$date' AND target_stop_date >= '$date' AND target_color = '0000ff' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND submetric='$submetric' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
 	$row=$result->fetch_assoc();
	if ($row[$tvhol]==''){
		$sql = "SELECT $tvhol FROM targets WHERE target_color = '0000ff' AND target_contract_id='$contract' AND target_metric_id='$metric' AND target_team_id='$team' AND submetric='$submetric' LIMIT 1";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	 	$row=$result->fetch_assoc();
		if ($row[$tvhol]==''){ return 0; }
	}
	return $row[$tvhol];
}
function surveycount(){
	global $db,$teamdefinition,$sqldater;
	$surveys=0;
	$sql = "SELECT COUNT(*) as id FROM raw_data $sqldater $teamdefinition";
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
	$sql = "SELECT $selector FROM vm055_data WHERE team = '{$vm_team}' AND month = '{$vm_date}' LIMIT 1";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[$selector]==''){return "0";}
	return $row[$selector]*$mul;
}
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
			$valuesqldater = " WHERE Teammate_Contact_Date > $valuestartdate_excel";
			$valuesqldater .= " AND Teammate_Contact_Date < $valueenddate_excel";

		$sql = "SELECT external_survey_id FROM raw_data".$valuesqldater . $teamdefinition;
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$surveys = 0;
  while($row=$result->fetch_assoc()){ $surveys++;}

	$sql = "SELECT kdi___email,kdi___phone,likely_to_recommend_paypal,issue_resolved FROM raw_data $valuesqldater ". $teamdefinition;
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	while($row=$result->fetch_assoc()){
		if ($row[likely_to_recommend_paypal]>8){$topperformer++;}
		if ($row[likely_to_recommend_paypal]<7){$bottomperformer++;}
		if ($row[issue_resolved]=='Yes'){$crrr_yes++;}
		if ($row[issue_resolved]!='') {$crrr_inc++;}
		if($row[kdi___email]!=''){$kdi_email++;$kdi_email_sum+=$row[kdi___email];$kdi++;$kdi_sum+=$row[kdi___email];}
		if($row[kdi___phone]!=''){$kdi_phone++;$kdi_phone_sum+=$row[kdi___phone];$kdi++;$kdi_sum+=$row[kdi___phone];}
	}
	$sql = "SELECT metric_id,metric_name FROM metrics ORDER by metric_name ASC";
  if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
  while($row=$result->fetch_assoc()){
		$metricname[$row[metric_id]]=$row[metric_name];
	}
	if ($metricname[$metric]=='NPS'){$value = round((100*$topperformer/$surveys)-(100*$bottomperformer/$surveys),2);}
	elseif($metricname[$metric]=='CrRR'){$value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metricname[$metric]=='KDI'){$value = round(($kdi_sum/$kdi),2);}
	else { $value = 0; }
	return $value;
}
function sv($metric,$filter,$surveys){
	global $db,$teamdefinition,$sqldater;
	$topperformer=0;
	$bottomperformer=0;
	$crrr_yes=0;$crrr_inc=0;
	$kdi_sum=0;$kdi_phone=0;$kdi_phone_sum=0;
	$kdi_email=0;$kdi_email_sum=0;
	if($metric=='NPS'){$co='likely_to_recommend_paypal';}
	if($metric=='CrRR'){$co='issue_resolved';}
	if($metric=='KDI'){$co='kdi___email,kdi___phone,Handled_professionally,Showed_genuine_interest,Took_ownership,Knowledge_to_handle_request,Valued_customer,Was_professional,Easy_to_understand,Provided_accurate_info,Helpful_response,Answered_concisely,Sent_in_timely_manner';}
	if($metric=='ATT'){$co='workitem_phone_talk_time';}
	$sql = "SELECT $co FROM raw_data $sqldater $teamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$contra = 0;
	while($row=$result->fetch_assoc()){
		$contra++;
		if($metric=='NPS'){
			if ($row[likely_to_recommend_paypal]>8){$topperformer++;}
			if ($row[likely_to_recommend_paypal]<7){$bottomperformer++;}
		}
		elseif($metric=='CrRR'){
			if ($row[issue_resolved]=='Yes'){$crrr_yes++;}
			if ($row[issue_resolved]!='') {$crrr_inc++;}
		}
		elseif($metric=='KDI'){
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
				$k='Sent_in_timely_manner';if ($row[$k]!=''){ $kdi++; if ($row[$k]>7){$kdi_top++;}}
			}
		}
		elseif($metric=='ATT'){
			if ($row[workitem_phone_talk_time]!=''){$att++;$att_sum+=$row[workitem_phone_talk_time];}
		}
	}
	if($metric=='NPS'){$value = round((100*$topperformer/$surveys)-(100*$bottomperformer/$surveys),2);}
	elseif($metric=='CrRR'){ $value = round(100*($crrr_yes/$crrr_inc),2);}
	elseif($metric=='KDI'){cl($kdi_top . "/" . $kdi);$value = round(($kdi_top/$kdi*100),2);}
	elseif($metric=='ATT'){$value = round(($att_sum/$att),0);}
	else { $value = 0; }
	if ($contra>0){return $value;}
	else{return '--';}
}
function av($what,$monthdate,$filter){
	global $db,$sqldater,$monthy,$ahtteamdefinition;
	$answered=0;$worked=0;$eaht=0;$paht=0;$montha = substr($monthdate,5,2);$montha=$monthy[$montha];$yeara = substr($monthdate,0,4);
	$sql = "SELECT ntid,queue_name,phone_answered,phone_aht_secs,email_worked,email_aht_secs FROM ahtreport_data WHERE month='$montha-$yeara'$ahtteamdefinition $filter";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$contra = 0;
	$counto=0;$counta=0;
	while($row=$result->fetch_assoc()){
		$contra++;
		if ($what=='answered'){ $counto+=$row[phone_answered]; }
		if ($what=='worked'){ $counto+=$row[email_worked]; }
		if ($what=='eaht'){
			$counto+=$row[email_aht_secs]*$row[email_worked];
			$counta+=$row[email_worked];
		}
		if ($what=='paht'){
			$counto+=$row[phone_aht_secs]*$row[phone_answered];
			$counta+=$row[phone_answered];
		}
	}
	if (($what=='paht') or ($what=='eaht')) { $counto=$counto/$counta; }
	if ($contra>0){
		return round($counto,0);
	}
	else{ return '--';}
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
echo "
<!DOCTYPE html>
<html>
	<head>
		<meta charset='UTF-8''>
		<script src='sorttable.js'></script>
		<script src='../ac/amcharts/amcharts.js'></script>
		<script src='../ac/amcharts/serial.js'></script>
		<link rel=stylesheet href='gold.css.2''>";
echo '<link href="https://fonts.googleapis.com/css?family=Lato|Raleway|Raleway:900" rel="stylesheet">';
echo "<title>{$application_name}</title>
	</head>
	<body>";
	echo date("Y-m-d H:i:s");
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
				echo "<script>location.href='./';</script>";
			}
			else {
				echo "Sorry, there was an error uploading your PRT060P file.";
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
				echo "<script>location.href='./';</script>";
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

		// Pick teammate
		echo "<div id='tmpicker'>Display teammate <select onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d$extraurl&startdate=$startdate&enddate=$enddate&team=$team&teammate=\" + this.value;'>";
		echo "<option value='-1'>Full team</option>";
		echo "</select></div>";

		// Pick team
				echo "<div id='teampicker'>Display Team <select onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d$extraurl&startdate=$startdate&enddate=$enddate&team=\" + this.value;'name='team'>";
        echo "<option value='-1'>Belfast___________</option>";
				$sql = "SELECT id,team_name FROM teams LIMIT 50";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        while($row=$result->fetch_assoc()){
					echo "<option "; if ($team==$row[team_id]){
						echo "selected ";
						$teamname=$row[team_name];
					} echo "value={$row[team_id]}>{$row[team_name]}</option>"; }
				echo "</select></div>";

		// Pick preferred team
				echo "<div id='preferred_team'><select onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d$extraurl&startdate=$startdate&enddate=$enddate&team=\" + this.value + \"&prefteam=\" + this.value;' name='prefteam'>";
        echo "<option value='-1'>Belfast___________</option>";
				$sql = "SELECT id,team_name FROM teams LIMIT 50";
        if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
        while($row=$result->fetch_assoc()){
					echo "<option "; if ($prefteam==$row[team_id]){echo "selected ";} echo "value={$row[team_id]}>{$row[team_name]}</option>"; }
				echo "</select> Preferred Team</div>";

				$lastmonth = date("Y-m-d", mktime(0, 0, 0, $startdate_month-1, $startdate_day, $startdate_year));
				$lastend = date("Y-m-d", mktime(0, 0, 0, $enddate_month-1, cal_days_in_month(CAL_GREGORIAN,$enddate_month-1,$currentyear), $enddate_year));
				$nextmonth = date("Y-m-d", mktime(0, 0, 0, $startdate_month+1, $startdate_day, $startdate_year));
				$nextend = date("Y-m-d", mktime(0, 0, 0, $enddate_month+1, cal_days_in_month(CAL_GREGORIAN,$enddate_month+1,$currentyear), $enddate_year));
				echo "<div id='prevmonth'><a href='?a=$a&b=$b&c=$c&team=$team&startdate=$lastmonth&enddate=$lastend'><img src='images/rewind-1.png' title='Previous month'></a></div>";
				echo "<div id='currentmonth'><a href='?a=$a&b=$b&c=$c&team=$team&startdate=$currentyear-$currentmonth-01&enddate=$currentyear-$currentmonth-$lastday'>Current Month</a></div>";
				echo "<div id='nextmonth'><a href='?a=$a&b=$b&c=$c&team=$team&startdate=$nextmonth&enddate=$nextend'><img src='images/fast-forward-1.png' title='Next month'></a></div>";
				echo "<div id='startdatepicker'>Start date <input id=startdate name=startdate type=date value='$startdate' onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d&team=$team&enddate=$enddate&startdate=\"+this.value;'></div>";
				echo "<div id='enddatepicker'><input id=enddate name=enddate type=date value='$enddate' onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d&team=$team&startdate=$startdate&enddate=\"+this.value;'> End date</div>";
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
    echo "<div id='div_left_area'>";
		echo "<a href='http://www.concentrix.com' id='cnx_logo'><img src='cnx_logo.png'></a>";
		echo "<div id='div_logo'><a href='./'>Gold</a></div>";
		echo "<div id='sep1' class='separator'></div>";
		$un=getusername($uid);
		echo "<div id='div_username'><a href='mailto:$un@concentrix.com'>$un@concentrix.com</a></div>";
		addleftchoice("metrics");
		addleftchoice("surveys");
		addleftchoice("bonus");
		//addleftchoice("employees");
		addleftchoice("qds");
		addleftchoice("targets");
		//addleftchoice("teams");
		//addleftchoice("contracts");
		addleftchoice("settings");
		echo "</div>";
		echo "<div id='leftinfo'>Last updated:<br>";
		$today = date("Y-m-d");
		if ($application_version_major==$today){$application_version_major="today";}
		$medalliaupdated = getsetting('medalliadata');
		$vm055updated = getsetting('vm055data');
		$prt060pupdated = getsetting('prt060pdata');
		$medalliaupdated = getsetting('medalliadata');
		echo "System: <b>$application_version_major</b> @ $application_version_minor.<br>";
		echo "Medallia: " . dd($medalliaupdated) . "<br>";
		echo "VM055: " . dd($vm055updated) . "<br>";
		echo "PRT060p: " . dd($prt060pupdated) . "<br>";
		echo "</div>";
    echo "<div id='div_right_area'>";
    if ($a) {
      echo "<div id='area_title'><a href='?a=$a'>".ucfirst($a)."</a></div>";
      echo "<div id='area_body'>";
			if($a=='bonus'){
				if ($team>0){
					echo "This view works only with full months and cannot stretch beyond a single month.<br>\n";
					if (ismgr()){echo "<a href='?a=$a&startdate=$startdate&enddate=$enddate&team=$team'>View</a> / "; echo "<a href='?a=$a&b=e&startdate=$startdate&enddate=$enddate&team=$team'>Edit</a>";}
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
					echo "<table  class=sortable id='bonustable' cellpadding=0 cellspacing=0>";
					echo "<thead><tr>
						<th class=rotated width=5>Position</th>
						<th>Name</th>
						<th>Surname</th>
						<th class=rotated width=10>Phone Answered</th>
						<th class=rotated width=10>Phone Surveys</th>
						<th class=rotated width=10>Email Worked</th>
						<th class=rotated width=10>Email Surveys</th>
						<th class=rotated width=10>Phone AHT</th>
						<th class=rotated width=10>Email AHT</th>
						<th class=rotated width=10>Phone KDI</th>
						<th class=rotated width=10>Phone CrRR</th>
						<th class=rotated width=10>Phone NPS</th>
						<th class=rotated width=10>Email KDI</th>
						<th class=rotated width=10>Email CrRR</th>
						<th class=rotated width=10>Email NPS</th>
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
					echo "<td></td>";
					echo "<td><b>TARGET</b></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td>".getsubtarget(5,2,$team,$view_startdate,'phone')."</td>";
					echo "<td>".getsubtarget(5,2,$team,$view_startdate,'email')."</td>";
					echo "<td>".getsubtarget(5,5,$team,$view_startdate,'phone')."</td>";
					echo "<td>".getsubtarget(5,3,$team,$view_startdate,'phone')."</td>";
					echo "<td>".getsubtarget(5,4,$team,$view_startdate,'phone')."</td>";
					echo "<td>".getsubtarget(5,5,$team,$view_startdate,'email')."</td>";
					echo "<td>".getsubtarget(5,3,$team,$view_startdate,'email')."</td>";
					echo "<td>".getsubtarget(5,4,$team,$view_startdate,'email')."</td>";
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
							$sqlb="SELECT distinct external_survey_id FROM raw_data $sqldater $teamdefinition AND teammate_nt_id = '{$row[teammate_nt_id]}' AND queue_source_name LIKE '%VOICE'";
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
							$bd[phonekdi][$teammate[$x]] = sv("KDI",$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
							$bd[phonecrrr][$teammate[$x]] = sv("CrRR",$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
							$bd[phonenps][$teammate[$x]] = sv("NPS",$surveydefinition,$bd[phonesurveys][$teammate[$x]]);
							$bd[phoneanswered][$teammate[$x]] = av("answered",$startdate,$ahtdef);
							$bd[phoneaht][$teammate[$x]] = av("paht",$startdate,$ahtdef);
							$bd[hours][$teammate[$x]] = gethours($teammate[$x],$startdate);
							$bd[instances][$teammate[$x]] = getinstances($teammate[$x],$startdate);

							$surveydefinition = " AND teammate_nt_id = '{$teammate[$x]}' AND queue_source_name LIKE '%EMAIL'";
							$ahtdef = " AND ntid = '{$teammate[$x]}' AND queue_name LIKE '%Email'";
							$bd[emailkdi][$teammate[$x]] = sv("KDI",$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
							$bd[emailcrrr][$teammate[$x]] = sv("CrRR",$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
							$bd[emailnps][$teammate[$x]] = sv("NPS",$surveydefinition,$bd[emailsurveys][$teammate[$x]]);
							$bd[emailworked][$teammate[$x]] = av("worked",$startdate,$ahtdef);
							$bd[emailaht][$teammate[$x]] = av("eaht",$startdate,$ahtdef);

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
					//for($x=1;$x<=$teammate_counter;$x++){
					foreach($bd[performancepoints] as $tm => $points){
						//$tm = $teammate[$x];
						if (guessteam($tm)==$team){
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
							echo "<td	 style='color:#ddd;background-color:#".targetcolor($bd[phoneaht][$tm], $contract, 2, $team, $view_startdate,"phone")."'>{$bd[phoneaht][$tm]}</td>";

							// Email AHT
							echo "<td	 style='color:#bbb;background-color:#".targetcolor($bd[emailaht][$tm], $contract, 2, $team, $view_startdate,"email")."'>{$bd[emailaht][$tm]}</td>";

							// Phone KDI
							echo "<td	 style='color:#bbb;background-color:#".targetcolor($bd[phonekdi][$tm], $contract, 5, $team, $view_startdate,"phone")."'>{$bd[phonekdi][$tm]}</td>";
							// Phone CrRR
							echo "<td	 style='color:#bbb;background-color:#".targetcolor($bd[phonecrrr][$tm], $contract, 3, $team, $view_startdate,"phone")."'>{$bd[phonecrrr][$tm]}</td>";

							// Phone NPS
							echo "<td	 style='color:#bbb;background-color:#".targetcolor($bd[phonenps][$tm], $contract, 4, $team, $view_startdate,"phone")."'>{$bd[phonenps][$tm]}</td>";

							// Email KDI
							echo "<td	 style='color:#bbb;background-color:#".targetcolor($bd[emailkdi][$tm], $contract, 5, $team, $view_startdate,"email")."'>{$bd[emailkdi][$tm]}</td>";

							// Email CrRR
							echo "<td	 style='color:#bbb;background-color:#".targetcolor($bd[emailcrrr][$tm], $contract, 3, $team, $view_startdate,"email")."'>{$bd[emailcrrr][$tm]}</td>";

							// Email NPS
							echo "<td	style='color:#bbb;background-color:#".targetcolor($bd[emailnps][$tm], $contract, 4, $team, $view_startdate,"email")."'>{$bd[emailnps][$tm]}</td>";

							// Performance Points
							echo "<td	class=pptd style='color:#fff;background-color:#". targetcolor($bd[performancepoints][$tm], $contract, 11,5)."'>";
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

							echo "<td>".round($bonusbeforesick[$tm],0)."</td>";

							// Sick instances
							echo "<td";
							if ($bd[instances][$tm]==1){echo " style='color:#f80;'";}
							if ($bd[instances][$tm]>1){echo " style='color:#f00;'";}
							echo ">";
							if ($im&&$b=='e'){echo "<input class='editor' size=3 name='si$counti' value='{$bd[instances][$tm]}'>";}
							else{echo $bd[instances][$tm];}
							echo "</td>";
							$multo = 1;
							if ($bd[instances][$tm]>0){$multo='0.5';}
							if ($bd[instances][$tm]>1){$multo='0';}
							$bonuspaidout[$tm] = round($bonusbeforesick[$tm]*$multo,0);
							// Bonus paid out
							echo "<td";
							if ($bonuspaidout[$tm]>0){echo " style='color:#0f0;'";}
							echo ">{$bonuspaidout[$tm]}</td>";
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
				else{echo "This view only works with a team selected.";}
			}
			elseif($a=='surveys'){
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
				echo "<table class='sortable bt' id='queuelist'>";
				echo "<thead><tr><th>%</th><th>Surveys</th><th>$c</th><th>KDI</th><th>CrRR</th><th>NPS</th><th>ATT</th></tr></thead>";
				if ($c){
					$column_name=$c;
					$sql = "SELECT distinct $column_name FROM raw_data ORDER by $column_name ASC";
					if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
					echo "<select name=data onChange='location.href=\"?a=$a&b=$b&team=$team&c=$column_name&d=\" + this.value;'>";
					echo "<option></option>";
					while($row=$result->fetch_assoc()){
						echo "<option";
						if ($d==$row[$column_name]){echo" selected";}
						echo ">".$row[$column_name]."</option>";
					}
					echo "</select><hr>Displaying a maximum of 50 results.";
					if($d!=''){
						if($teamdefinition){ $surveydefinition="AND "; }
						else {
							if ($sqldater){ $surveydefinition="AND "; }
							else{ $surveydefinition="WHERE "; }
						}
						$surveydefinition.="$c='$d'";
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
						echo "<td></td>";
						echo "<td>100%</td>";
						echo "<td>" . $surveys . "</td><td>TOTAL</td>";
						$kdi=sv("KDI",$surveydefinition,$surveys);
						$crrr=sv("CrRR",$surveydefinition,$surveys);
						$nps=sv("NPS",$surveydefinition,$surveys);
						$att=sv("ATT",$surveydefinition,$surveys);
						echo "<td style='background-color:#".targetcolor($kdi, $contract, 5, $team, $_GET[startdate])."'>";
						echo $kdi;
						echo "</td>\n";
						echo "<td style='background-color:#".targetcolor($crrr, $contract, 3, $team, $_GET[startdate])."'>";
						echo  $crrr;
						echo "</td>\n";
						echo "<td style='background-color:#".targetcolor($nps, $contract, 4, $team, $_GET[startdate])."'>";
						echo $nps;
						echo "</td>\n";
						echo "<td>";
						echo $att;
						echo "</td>\n";
						echo "</tr>\n\n";
						echo "</tr>";
						echo "</tfoot>";


						$sql = "SELECT distinct $column_name FROM raw_data $sqldater $teamdefinition ORDER by $column_name ASC LIMIT 50";
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
							$surveydefinitions.="$c='{$row[$c]}'";
							$sqla = "SELECT COUNT(*) as id FROM raw_data $sqldater $teamdefinition $surveydefinitions LIMIT 50";
							if(!$resulta=$db->query($sqla)){cl($sqla);cl($db->error);}
							$rowa=$resulta->fetch_assoc();
							$surveysa=$rowa[id];
							echo "<td>".round(($surveysa/$totalsurveys)*100) . "%</td>";
							echo "<td>$surveysa</td><td><a href='?&a=$a&b=$b&c=$c&d=";echo urlencode($row[$c]); echo "&team=$team'>{$row[$column_name]}</a></td>\n";
							$kdi=sv("KDI",$surveydefinitions,$surveysa);
							$crrr=sv("CrRR",$surveydefinitions,$surveysa);
							$nps=sv("NPS",$surveydefinitions,$surveysa);
							$att=sv("ATT",$surveydefinitions,$surveysa);
							echo "<td style='background-color:#".targetcolor($kdi, $contract, 5, $team, $_GET[startdate])."'>";
							echo $kdi;
							echo "</td>\n";
							echo "<td style='background-color:#".targetcolor($crrr, $contract, 3, $team, $_GET[startdate])."'>";
							echo  $crrr;
							echo "</td>\n";
							echo "<td style='background-color:#".targetcolor($nps, $contract, 4, $team, $_GET[startdate])."'>";
							echo $nps;
							echo "</td>\n";
							echo "<td>";
							echo $att;
							echo "</td>\n";
							echo "</tr>\n\n";
						}
					}
				}
				echo "</table>";
				if($d!=''){
					echo "<table class=sortable><tr><th>Num</th><th>Survey ID</th><th>Queue</th><th>Teammate</th></tr>";
					$sql = "SELECT external_survey_id,$c,queue_source_name,teammate_name,teammate_nt_id FROM raw_data $sqldater $teamdefinition $surveydefinition LIMIT 50";
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
								$testartdate=$_GET[testartdate];
								$testopdate=$_GET[testopdate];
								$temetric=$_GET[temetric];
								$tesubmetric=$_GET[tesubmetric];
								$tecontract=$_GET[tecontract];
								$teteamid=$_GET[teteam];
								$teid=$c;
								$sql = "UPDATE targets SET target_value_low='$tevaluelow',target_value_high='$tevaluehigh',
								target_metric_id='$temetric',target_contract_id='$tecontract',target_team_id='$teteamid',target_start_date='$testartdate',
								target_stop_date='$testopdate',target_color='$tecolor',submetric='$tesubmetric' WHERE target_id = '$c' LIMIT 1";
								if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
							}
							else {
								$sql = "SELECT * FROM targets WHERE target_id = '$c' LIMIT 1";
								if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
								$tgt=$result->fetch_assoc();
								$tevaluelow = $tgt[target_value_low];
								$tevaluehigh = $tgt[target_value_high];
								$tecolor = $tgt[target_color];
								$testartdate = substr($tgt[target_start_date],0,10);
								$testopdate = substr($tgt[target_stop_date],0,10);
								$temetricid = $tgt[target_metric_id];
								$tecontractid = $tgt[target_contract_id];
								$tesubmetric = $tgt[submetric];
								$teteamid = $tgt[target_team_id];
								echo "<form>
								Target value low: <input name='tevaluelow' value='$tevaluelow'><br>
								Target value high: <input name='tevaluehigh' value='$tevaluehigh'><br>
								Target color: <input name='tecolor' value='$tecolor'><br>
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
									$selected='';if($row[team_id]==$teteamid){$selected=' selected';}
	                echo "<option value='{$row[team_id]}'$selected>{$row[team_name]}</option>";
	              }
	              echo "</select><br>";
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
                echo "<option value={$row[team_id]}";
								if ($team==$row[team_id]){echo " selected";}
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
							target_color,target_start_date,target_stop_date,submetric)
							VALUES('{$_GET[contract]}','{$_GET[team]}','{$_GET[metric]}','{$_GET[value_low]}','{$_GET[value_high]}','{$_GET[color]}','{$_GET[startdate]}'
							,'{$_GET[stopdate]}','{$_GET[submetric]}')";
              if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
            }
						else {
              echo "<form>
							Target value low: <input name='value_low'><br>
							Target value high: <input name='value_high'><br>
							Target color: <input name='color'><br>
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
								$selected='';if ($row[team_id]==$team){$selected = ' selected';}
                echo "<option value='{$row[team_id]}'$selected>{$row[team_name]}</option>";
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
				echo "Filter by metric: <select onChange='location.href=\"?a=$a&b=$b&c=$c&d=$d&f=\" + this.value;'>";
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
        while($row=$result->fetch_assoc()){ $teams[$row[team_id]] = $row[team_name]; }

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
				<th>Color</th>
				</tr></thead>";
        while($row=$result->fetch_assoc()){
          echo "<tr>";
					echo "<td><button onClick=\"location.href='?a=targets&b=e&c={$row[target_id]}';\">Edit</button></td>";
					echo "<td><button onClick=\"location.href='?a=targets&b=d&c={$row[target_id]}';\">Delete</button></td>";
					echo "<td>{$teams[$row[target_team_id]]}</td>";
					echo "<td>{$metrics[$row[target_metric_id]]}</td>";
					echo "<td>$row[submetric]</td>";
					echo "<td>$row[target_value_low]</td>";
					echo "<td>$row[target_value_high]</td>";
					echo "<td>$row[target_start_date]</td>";
					echo "<td>$row[target_stop_date]</td>";
					echo "<td style='background:#$row[target_color];color:#fff;'>$row[target_color]</td>";
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
				<th>Metric</th>
				<th>Submetric</th>
				<th>Low</th>
				<th>High</th>
				<th>Startdate</th>
				<th>Stopdate</th>
				<th>Color</th>
				</tr></thead>";
        while($row=$result->fetch_assoc()){
          echo "<tr>";
					echo "<td><button onClick=\"location.href='?a=targets&b=e&c={$row[target_id]}';\">Edit</button></td>";
					echo "<td><button onClick=\"location.href='?a=targets&b=d&c={$row[target_id]}';\">Delete</button></td>";
					echo "<td>{$teams[$row[target_team_id]]}</td>";
					echo "<td>{$metrics[$row[target_metric_id]]}</td>";
					echo "<td>$row[submetric]</td>";
					echo "<td>$row[target_value_low]</td>";
					echo "<td>$row[target_value_high]</td>";
					echo "<td>$row[target_start_date]</td>";
					echo "<td>$row[target_stop_date]</td>";
					echo "<td style='background:#$row[target_color];color:#fff;'>$row[target_color]</td>";
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
        while($row=$result->fetch_assoc()){
          echo "<div class='tbl_team'><a href='?a=teams&b=show_team&team={$row[team_id]}'>".$row[team_name]."</a></div>";
        }

				if ($b=='show_team'){
					$team=$_GET[team];
	        if (isadmin()) {
						if ($c == 'delsurvey'){
							$sql = "DELETE FROM team_data_definitions WHERE team_id = '$team' AND raw_data_column = '{$_GET[column]}' AND raw_data_data = '{$_GET[data]}' LIMIT 1";
							if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						}
						if ($c == 'delaht'){
							$sql = "DELETE FROM team_aht_definitions WHERE team_id = '$team' AND ahtreport_data_column = '{$_GET[column]}' AND ahtreport_data_data = '{$_GET[data]}' LIMIT 1";
							if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
						}
					}
					$sql = "SELECT * FROM teams WHERE team_id='$team' LIMIT 1";
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
					$sql = "SELECT column_name FROM information_schema.columns WHERE table_name='ahtreport_data' AND table_schema='concentrix'";
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
							$sql = "SELECT distinct $column_name FROM ahtreport_data ORDER by $column_name ASC";
							if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
							echo "<br>Data: <select name=data onChange='location.href=\"?a=teams&b=show_team&team=$team&acolumn_name=$column_name&adata=\" + this.value;'>";
							echo "<option></option>";
							while($row=$result->fetch_assoc()){
								echo "<option";
								if ($_GET[adata]==$row[$column_name]){echo" selected";}
								echo ">".$row[$column_name]."</option>";
							}
							echo "</select>";
							echo "<input name=a type=hidden value=teams>
							<input name=b type=hidden value=show_team>
							<input name=team type=hidden value=$team>
							</form>";
							if ($_GET[adata]){
								$sql = "INSERT INTO team_aht_definitions (team_id,ahtreport_data_column,ahtreport_data_data) VALUES('$team','$column_name','{$_GET[adata]}')";
								if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
								echo "Definition added.";
							}
							echo "<br><br><br>";
						}
				}
      }
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
							$tn[Sweden] = 'Swedish EU';
							$tn[Denmark] = 'Danish EU';
							$tn[Norway] = 'Norwegian EU';
							$tn[Netherlands] = 'NETHERLANDS';
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
        while($row=$result->fetch_assoc()){
          echo "<div class='tbl_metric'><a href='?a=settings&b=show_metric&metric={$row[metric_id]}'>".$row[metric_name]."</a></div>";
        }
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
					echo "Upload a PRT060 report for AHT.";
					echo "<form name=uploadformaht method=post enctype='multipart/form-data'>
					<input type=hidden name=a value=uploadaht>
					<input type=file name='filedataaht' id='filedataaht'>
					<input type=submit></form>
					";
					echo "Upload a VM055p report.";
					echo "<form name=uploadformvm method=post enctype='multipart/form-data'>
					<input type=hidden name=a value=uploadvm055p>
					<input type=file name='filedatavm' id='filedatavm'>
					<input type=submit></form>
					";
				}
			}
			elseif($a=='qds'){
				$lastday=cal_days_in_month(CAL_GREGORIAN,$startdate_month,$startdate_year);
				$plastday=cal_days_in_month(CAL_GREGORIAN,$startdate_month-1,$startdate_year);
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
				<th colspan=4>QD</th>
				</tr>";
				echo "<tr>
				<th>$previous_month</th>
				<th>$startdate_month</th>
				<th>&Delta;%</th>
				<th>$previous_month</th>
				<th>$startdate_month</th>
				<th>&Delta;%</th>
				<th>$previous_month</th>
				<th>$startdate_month</th>
				<th>&Delta;%</th>
				<th>$previous_month</th>
				<th>$startdate_month</th>
				<th>&Delta;%</th>
				<th class=qdth>#1</th>
				<th class=qdth>#2</th>
				<th class=qdth>#3</th>
				<th class=qdth>#4</th>
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
							$delta = round($aht-$paht,1);
							$deltaproc = round(100*$delta/$paht);
							if ($deltaproc>$bo_value){$bo_value=-$deltaproc;$bo_metric="AHT";}

							echo "<td";
							echo ">".round($paht)."</td>";
							echo "<td";
							echo ">".round($aht)."</td>";
							echo "<td style='color:#bbb;background-color:#";
							if ($deltaproc>0){echo '8b0000';}
							elseif ($deltaproc<0){echo '008000';}
							echo "'>$deltaproc%</td>";

							//kdi
							$surveys = countsurveys($row[Teammate_NT_ID]);
							$kdi = sv("KDI","AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
							$rsqldater = $sqldater;$sqldater = $psqldater;$psurveys = countsurveys($row[Teammate_NT_ID]);
							$pkdi = sv("KDI","AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);
							$sqldater = $rsqldater;
							$delta = round($kdi-$pkdi,1);
							$deltaproc = round(100*$delta/$pkdi);
							if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="KDI";}
							echo "<td";
							echo ">".round($pkdi)."</td>";
							echo "<td";
							echo ">".round($kdi)."</td>";
							echo "<td style='color:#bbb;background-color:#";
							if ($deltaproc>0){echo '008000';}
							elseif ($deltaproc<0){echo '8b0000';}
							echo "'>$deltaproc%</td>";

							//crrr
							$crrr = sv("CrRR","AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
							$rsqldater = $sqldater;$sqldater = $psqldater;
							$pcrrr = sv("CrRR","AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);
							$sqldater = $rsqldater;
							$delta = round($crrr-$pcrrr,1);
							$deltaproc = round(100*$delta/$pcrrr);
							if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="CrRR";}
							echo "<td";
							echo ">".round($pcrrr)."</td>";
							echo "<td";
							echo ">".round($crrr)."</td>";
							echo "<td style='color:#bbb;background-color:#";
							if ($deltaproc>0){echo '008000';}
							elseif ($deltaproc<0){echo '8b0000';}
							echo "'>$deltaproc%</td>";

							//nps
							$nps = sv("NPS","AND teammate_nt_id = '$row[Teammate_NT_ID]'",$surveys);
							$rsqldater = $sqldater;$sqldater = $psqldater;
							$pnps = sv("NPS","AND teammate_nt_id = '$row[Teammate_NT_ID]'",$psurveys);
							$sqldater = $rsqldater;
							$delta = round($nps-$pnps,1);
							$deltaproc = round(100*$delta/$pnps);
							if ($nps>$pnps){$deltaproc=abs($deltaproc);}
							if ($deltaproc<$bo_value){$bo_value=$deltaproc;$bo_metric="NPS";}

							echo "<td";
							echo ">".round($pnps)."</td>";
							echo "<td";
							echo ">".round($nps)."</td>";
							echo "<td style='color:#bbb;background-color:#";
							if ($deltaproc>0){echo '008000';}
							elseif ($deltaproc<0){echo '8b0000';}
							echo "'>$deltaproc%</td>";

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
      echo "</div>";
    }
		else {
			$sql = "SELECT team_name,team_id FROM teams";
			if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
			$teams = 0;
			while($row=$result->fetch_assoc()){
				$teams++;
				$teamname = $row[team_name];
				$teamid = $row[team_id];
				$sqla = "SELECT * FROM team_data_definitions WHERE team_id = $teamid";
      	if(!$resulta=$db->query($sqla)){cl($sqla);cl($db->error);}
				$definitions = 0;
				$teamdef[$teamname] = '';
      	while($rowa=$resulta->fetch_assoc()){
					if ($definitions){$teamdef[$teamname].=' OR ';}
					$teamdef[$teamname] .= $rowa[raw_data_column] . "='{$rowa[raw_data_data]}'";
					$definitions++;
				}
				if ($teamdef[$teamname]){$teamdef[$teamname]=" AND (".$teamdef[$teamname] . ")";}
			}

			echo "<div id='area_title'><a href='?'>Dashboard</a></div>";
      echo "<div id='area_body'>";
			echo "<table id='dashboard' width=100% cellspacing=0 cellpadding=0>";
			echo "
			<tr>
				<td class='sectiontitle'>Quality</td>
				<td class='sectionheader'>Danish</td>
				<td class='sectionheader'>Dutch</td>
				<td class='sectionheader'>Norwegian</td>
				<td class='sectionheader'>Swedish</td>
			</tr>
			<tr>
				<td>KDI</td>";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Denmark']; $value = getvalue(5,$startdate,$enddate); echo targetcolor($value, 5, 5, 5, $startdate); echo "'>$value%</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Netherlands']; $value = getvalue(5,$startdate,$enddate); echo targetcolor($value, 5, 5, 7, $startdate); echo "'>$value%</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Norway']; $value = getvalue(5,$startdate,$enddate); echo targetcolor($value, 5, 5, 6, $startdate); echo "'>$value%</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Sweden']; $value = getvalue(5,$startdate,$enddate); echo targetcolor($value, 5, 5, 4, $startdate); echo "'>$value%</td>\n";
			echo "</tr>
			<tr>
				<td>CrRR</td>";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Denmark']; $value = getvalue(3,$startdate,$enddate); echo targetcolor($value, 5, 3, 5, $startdate); echo "'>$value%</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Netherlands']; $value = getvalue(3,$startdate,$enddate); echo targetcolor($value, 5, 3, 7, $startdate); echo "'>$value%</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Norway']; $value = getvalue(3,$startdate,$enddate); echo targetcolor($value, 5, 3, 6, $startdate); echo "'>$value%</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Sweden']; $value = getvalue(3,$startdate,$enddate); echo targetcolor($value, 5, 3, 4, $startdate); echo "'>$value%</td>\n";
			echo "</tr>
			<tr>
				<td>NPS</td>";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Denmark']; $value = getvalue(4,$startdate,$enddate);     echo targetcolor($value, 5, 4, 5, $startdate); echo "'>$value</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Netherlands']; $value = getvalue(4,$startdate,$enddate); echo targetcolor($value, 5, 4, 7, $startdate); echo "'>$value</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Norway']; $value = getvalue(4,$startdate,$enddate);      echo targetcolor($value, 5, 4, 6, $startdate); echo "'>$value</td>\n";
			echo "<td style='color:#ddd;background-color:#"; $teamdefinition = $teamdef['Sweden']; $value = getvalue(4,$startdate,$enddate);      echo targetcolor($value, 5, 4, 4, $startdate); echo "'>$value</td>\n";
			echo "</tr>
			<tr>
				<td>Surveys</td>";
			echo "<td style='color:#ddd;'>"; $teamdefinition = $teamdef['Denmark']; $value = surveycount();     echo "$value</td>\n";
			echo "<td style='color:#ddd;'>"; $teamdefinition = $teamdef['Netherlands']; $value = surveycount(); echo "$value</td>\n";
			echo "<td style='color:#ddd;'>"; $teamdefinition = $teamdef['Norway']; $value = surveycount();      echo "$value</td>\n";
			echo "<td style='color:#ddd;'>"; $teamdefinition = $teamdef['Sweden']; $value = surveycount();      echo "$value</td>\n";
			echo "</tr>";
			echo "<tr><td class='sectiontitle'>Productivity</td>
			</tr>
			<tr>
				<td>AHT</td>";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("aht","Danish EU",$startdate); echo targetcolor($value, 5, 2, 5, $startdate); echo "'>". round($value)."</td>";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("aht","NETHERLANDS",$startdate); echo targetcolor($value, 5, 2, 7, $startdate);echo "'>".round($value)."</td>";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("aht","Norwegian EU",$startdate); echo targetcolor($value, 5, 2, 6, $startdate);echo "'>". round($value) . "</td>";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("aht","Swedish EU",$startdate); echo targetcolor($value, 5, 2, 4, $startdate);echo "'>" . round($value) . "</td>";
			echo "</tr>
			<tr>
				<td>Phone Volume</td>";
				echo "<td style='color:#ddd;background-color:#";$pvalue[dk]=vm("pvol","Danish EU",$startdate);echo "'>".round($pvalue[dk])."</td>";
				echo "<td style='color:#ddd;background-color:#";$pvalue[nl]=vm("pvol","NETHERLANDS",$startdate);echo "'>".round($pvalue[nl])."</td>";
				echo "<td style='color:#ddd;background-color:#";$pvalue[no]=vm("pvol","Norwegian EU",$startdate);echo "'>".round($pvalue[no])."</td>";
				echo "<td style='color:#ddd;background-color:#";$pvalue[se]=vm("pvol","Swedish EU",$startdate); echo "'>".round($pvalue[se])."</td>";
			echo "</tr>
			<tr>
				<td>Email Volume</td>";
				echo "<td style='color:#ddd;background-color:#";$evalue[dk]=vm("evol","Danish EU",$startdate);echo "'>".round($evalue[dk])."</td>";
				echo "<td style='color:#ddd;background-color:#";$evalue[nl]=vm("evol","NETHERLANDS",$startdate);echo "'>".round($evalue[nl])."</td>";
				echo "<td style='color:#ddd;background-color:#";$evalue[no]=vm("evol","Norwegian EU",$startdate);echo "'>".round($evalue[no])."</td>";
				echo "<td style='color:#ddd;background-color:#";$evalue[se]=vm("evol","Swedish EU",$startdate);echo "'>".round($evalue[se])."</td>";
			echo "</tr>
			<tr>
				<td>Combined Volume</td>";
				echo "<td style='color:#ddd'>"; echo ($evalue[dk]+$pvalue[dk])."</td>";
				echo "<td style='color:#ddd'>"; echo ($evalue[nl]+$pvalue[nl])."</td>";
				echo "<td style='color:#ddd'>"; echo ($evalue[no]+$pvalue[no])."</td>";
				echo "<td style='color:#ddd'>"; echo ($evalue[se]+$pvalue[se])."</td>";
			echo "</tr>
			<tr>
				<td>Email vs Phone Balance</td>";
				echo "<td style='color:#ddd'>"; echo round($evalue[dk]/$pvalue[dk]*100)."%</td>";
				echo "<td style='color:#ddd'>"; echo round($evalue[nl]/$pvalue[nl]*100)."%</td>";
				echo "<td style='color:#ddd'>"; echo round($evalue[no]/$pvalue[no]*100)."%</td>";
				echo "<td style='color:#ddd'>"; echo round($evalue[se]/$pvalue[se]*100)."%</td>";
			echo "</tr>
			<tr>
				<td>Phone SLA</td>";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("psl","Danish EU",$startdate); echo targetcolor($value, 5, 8, 5, $startdate); echo "'>". round($value*100)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("psl","NETHERLANDS",$startdate); echo targetcolor($value, 5, 8, 5, $startdate);echo "'>".round($value*100)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("psl","Norwegian EU",$startdate); echo targetcolor($value, 5, 8, 5, $startdate);echo "'>". round($value*100)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("psl","Swedish EU",$startdate); echo targetcolor($value, 5, 8, 5, $startdate);echo "'>" . round($value*100)."%</td>\n";
			echo "</tr>
			<tr>
				<td>Email SLA</td>";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("esl","Danish EU",$startdate); echo targetcolor($value, 5, 13, 5, $startdate); echo "'>". round($value*100)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("esl","NETHERLANDS",$startdate); echo targetcolor($value, 5, 13, 5, $startdate);echo "'>".round($value*100)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("esl","Norwegian EU",$startdate); echo targetcolor($value, 5, 13, 5, $startdate);echo "'>". round($value*100)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $value = vm("esl","Swedish EU",$startdate); echo targetcolor($value, 5, 13, 5, $startdate);echo "'>" . round($value*100)."%</td>\n";
			echo "</tr>
			<tr>
				<td>Phone Transfer Rate</td>";
				echo "<td style='color:#ddd;background-color:#"; $ptr[dk] = vm("ptr","Danish EU",$startdate);    echo "'>". round($ptr[dk]*100,2)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $ptr[nl] = vm("ptr","NETHERLANDS",$startdate);  echo "'>".round($ptr[nl]*100,2)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $ptr[no] = vm("ptr","Norwegian EU",$startdate); echo "'>". round($ptr[no]*100,2)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $ptr[se] = vm("ptr","Swedish EU",$startdate);   echo "'>" . round($ptr[se]*100,2)."%</td>\n";
			echo "</tr>
			<tr>
				<td>Email Transfer Rate</td>";
				echo "<td style='color:#ddd;background-color:#"; $etr[dk] = vm("etr","Danish EU",$startdate);    echo "'>". round($etr[dk]*100,1)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $etr[nl] = vm("etr","NETHERLANDS",$startdate);  echo "'>".round($etr[nl]*100,1)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $etr[no] = vm("etr","Norwegian EU",$startdate); echo "'>". round($etr[no]*100,1)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; $etr[se] = vm("etr","Swedish EU",$startdate);   echo "'>" . round($etr[se]*100,1)."%</td>\n";
			echo "</tr>
			<tr>
				<td>Combined Transfer Rate</td>";
				echo "<td style='color:#ddd;background-color:#"; echo "'>". round((($etr[dk]*$evalue[dk])+($ptr[dk]*$pvalue[dk]))/($evalue[dk]+$pvalue[dk])*100,1)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; echo "'>". round((($etr[nl]*$evalue[nl])+($ptr[nl]*$pvalue[nl]))/($evalue[nl]+$pvalue[nl])*100,1)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; echo "'>". round((($etr[no]*$evalue[no])+($ptr[no]*$pvalue[no]))/($evalue[no]+$pvalue[no])*100,1)."%</td>\n";
				echo "<td style='color:#ddd;background-color:#"; echo "'>". round((($etr[se]*$evalue[se])+($ptr[se]*$pvalue[se]))/($evalue[se]+$pvalue[se])*100,1)."%</td>\n";
			echo "</tr>";
			echo "</table>";
			echo "</div>";
		}
    echo "</div>";
  }
}
echo "</body></html>";
$db->close();
?>
