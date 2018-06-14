<?php
$application_name = "Concentrix End of Day";
$application_copyright = "Copyright Concentrix Europe Ltd 2016";
$application_contact = "joakim.saettem@concentrix.com";
set_time_limit(600);
session_start();
$application_version_major = "16.10.26";
$application_version_minor = "16.10";

error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

include "../../zm-core-db.php"; $ddb = 'concentrix'; db_connect();
include "../../zm-core-functions.php";
echo "
<!DOCTYPE html>
<html>
	<head>
		<meta charset='UTF-8''>";
    echo "<title>{$application_name}</title>
    <style>
    body {
      background-color: #0000aa;
      font-family: arial, sans-serif;
      color: #aaffee;
      font-size: 1.8em;
    }
		#save {
			font-size: 0.5em;
			padding: 1em;
			border: 2px solid #fff;
			background-color: #333;
			color: #fff;
		}
      .central {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%,-50%);
      }
      .biginput {
        background-color: #fff;
        border: 1px solid #ccc;
        color: #000;
        font-size: 1em;
        color: #00f;
      }
      button {
        font-size: 0.5em;
        padding: 0.5em;
      }
      textarea{
        background-color: #fff;
        border: 1px solid #ccc;
        color: #000;
        color: #00f;

      }
			.bad {
				color: #ff7777;
			}
			.warning {
				color: #eeee77;
			}
    </style>
	</head>
	<body>";

$ntid = $_POST[ntid];
$empid = $_POST[empid];
$starthour = $_POST[starthour];
$startmin = $_POST[startmin];
$mystatstext = $_POST[mystatstext];
$validated = $_POST[validated];
$deduct = $_POST[deduct];
if ($ntid==''){
  echo "<form method=post>";
  echo "<div class=central>NTID:<br><input class=biginput name='ntid' autofocus></div>";
  echo "</form>";
}
elseif ($empid==''){
  echo "<form method=post>";
  echo "<div class=central>EmpID:<br><input class=biginput name='empid' autofocus></div>";
  echo "<input type=hidden name=ntid value='$ntid'>";
  echo "</form>";
}
elseif ($starthour=='') {
  echo "<form method=post>";
  echo "<div class=central>When were you ready today?<br>
  <input size=2 class=biginput name='starthour' autofocus>:
  <input size=2 class=biginput name='startmin'>
  <input type=submit value='=>'>
  </div>";
  echo "<input type=hidden name=empid value='$empid'>";
  echo "<input type=hidden name=ntid value='$ntid'>";
  echo "</form>";
}
elseif ($mystatstext=='') {
  echo "<form method=post>";
  echo "<div class=central>Paste your MyStats total page:<br>";
  echo "<textarea name='mystatstext' rows=10 cols=90></textarea><br>";
  echo "<input type=hidden name=empid value='$empid'>";
  echo "<input type=hidden name=ntid value='$ntid'>";
  echo "<input type=hidden name=starthour value='$starthour'>";
  echo "<input type=hidden name=startmin value='$startmin'>";
  echo "<input type=submit value='Validate'>";
  echo "</div>";
  echo "</form>";
}
elseif ($validated==''){
	//echo "<pre>$mystatstext</pre>";
	$mst = explode("\n",$mystatstext);$y = 0;
	foreach($mst as $key){$msr = explode(chr(9),$key);$x = 0;foreach($msr as $fook){$msd[$y][$x] = $fook;$x++;}$y++;}
	for ($z=0;$z<$y;$z++){
		//echo $msd[$z][3]."<br>";
		if ($msd[$z][2]=="IDLE_Break"){
			if (substr($msd[$z][3],0,2)=="01"){
				if (substr($msd[$z][3],3,2)>0) {
					$deductedminutes = substr($msd[$z][3],3,2);
					$breaks = "<span class=warning>Ensure you do not exceed 1h in breaks per day.</span> <span class=bad>Today $deductedminutes minutes have been deducted from your pay.
					</span><br><br>\n";
				}
			}
		}
		else if ($msd[$z][2]=="IDLE_UnBrk"){
			if ((substr($msd[$z][3],3,2)>10) or (substr($msd[$z][3],0,2)>0)) {
				$deductedminutesunbrk = substr($msd[$z][3],3,2);
				$unbreak = "<span class=warning>Ensure you do not exceed 10 minutes unscheduled break per day.</span> <span class=bad>Today $deductedminutesunbrk minutes have been deducted from your pay.
				</span><br><br>\n";
			}
		}
		else if ($msd[$z][2]=="IDLE_FollowUp"){
		}
		else if ($msd[$z][2]=="IDLE_Trn/Mtg"){
			if ((substr($msd[$z][3],3,2)>4) or (substr($msd[$z][3],0,2)>0)) {
				$trnmtgmins = $msd[$z][3];
				$training = "To finish, enter explanation for Training/Meeting {$msd[$z][3]}. Explain all activities if there were more than one.<br>\n";
			}
		}
	}

	echo "<form method=post>";
	echo "<input type=hidden name=deduct value='".($deductedminutes+$deductedminutesunbrk)."'>";
	echo "<input type=hidden name=trnmtgmins value='".$trnmtgmins."'>";
	echo "<input type=hidden name=mystatstext value='$mystatstext'>";
	echo "<input type=hidden name=empid value='$empid'>";
	echo "<input type=hidden name=ntid value='$ntid'>";
	echo "<input type=hidden name=starthour value='$starthour'>";
	echo "<input type=hidden name=startmin value='$startmin'>";
	echo "<input type=hidden name=validated value=true>";
	  echo "<div class=central>";
	  echo "<b></b><br>$breaks";
		echo "$unbreak";
		echo "$training";
		if ($training){ echo "<textarea id=trnmtgexplanation name=trnmtgexplanation cols=90 rows=10></textarea><br>"; }
	  echo "<input id=save type=submit name=save value=Save>";
		echo "</div>";
	echo "</form>";
}
else {
	// Time to save this shit.
	// First check if this user has submitted data already.
	$msdate = date("Y-m-d");
	$sql = "SELECT ntid FROM mystats WHERE ntid = '$ntid' AND msdate = '$msdate' AND empid = '$empid'";
	if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
	$row=$result->fetch_assoc();
	if ($row[ntid]){
		echo "You have already submitted your end of day information for today. Would you like to overwrite?<br><br>";
		echo "<button>Yes</button> <button>No</button>";
	}
	else {
		$trnmtgexplanation = $_POST[trnmtgexplanation];
		$trnmtgmins = $_POST[trnmtgmins];
		$sql = "INSERT INTO mystats (ntid, msdate, empid, starthour, startmin, deduct, trnmtgexplanation, trnmtgmins, techissuecomments) VALUES('$ntid','$msdate','$empid','$starthour','$startmin','$deduct','$trnmtgexplanation','$trnmtgmins', '$techissuecomments')";
		if(!$result=$db->query($sql)){cl($sql);cl($db->error);}
		echo "<div class=central><b>Thanks!</b> You can now close this page.</div>";
	}
}

echo "</body></html>";

?>
