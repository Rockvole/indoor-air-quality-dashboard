<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';

$result=mysqli_query($conn,"SELECT * from readings WHERE core_id=$id and ts>= $start_ts and ts<= $end_ts order by ts"); 
$ts=Array();
$temperature=Array();
$humidity=Array();
while($row = mysqli_fetch_array($result)) {
	$ts_str=gmdate('r', $row['ts']);
	error_log("temp=".$row['temperature']."||humidity=".$row['humidity']."||ts=".$row['ts']."||ts=".$ts_str);
	$ts[]=$row['ts'];
	$temperature[]=$row['temperature'];
	$humidity[]=$row['humidity'];
}
$temperature_plot=new LinePlot($temperature,$ts);
$temperature_plot->SetColor('hotpink3');
$humidity_plot=new LinePlot($humidity,$ts);
$humidity_plot->SetColor('dodgerblue');
$graph = new Graph($width,$height);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('datlin',0,100);
$graph->Add($humidity_plot);
$graph->SetY2Scale('lin',-10,35);
$graph->AddY2($temperature_plot);
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetDateFormat('g a');
$graph->yaxis->SetColor('dodgerblue');
$graph->yaxis->title->SetColor('dodgerblue');
$graph->yaxis->SetWeight(2);
$graph->yaxis->title->Set('%RH');
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);
$graph->y2axis->SetColor('hotpink3');
$graph->y2axis->SetWeight(2);
$graph->y2axis->title->SetColor('hotpink3'); 
$graph->y2axis->title->Set('C');
$graph->y2axis->title->SetAngle(90);
$graph->y2axis->title->SetMargin(10);

// Display the graph
$graph->Stroke();
?>
