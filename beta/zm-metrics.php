<?php
function show_module() {
	global $db, $_GET, $a, $b, $c, $d, $e, $f, $team, $sqldater, $teamdefinition, $contract, $startdate, $enddate;
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
