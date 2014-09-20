<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';

$result=mysqli_query($conn,"SELECT * from readings WHERE core_id=$id and ts>= $start_ts and ts<= $end_ts order by ts"); 
$ts=Array();
$ozone=Array();
$chlorine=Array();
$level=Array();
while($row = mysqli_fetch_array($result)) {
	$ts_str=gmdate('r', $row['ts']);
	error_log("temp=".$row['temperature']."||humidity=".$row['humidity']."||ts=".$row['ts']."||ts=".$ts_str);
	$ts[]=$row['ts'];
	$ozone[]=$row['ozone'];
	$chlorine[]=$row['chlorine'];
	$level[]=1000;
}
$ozone_plot=new LinePlot($ozone,$ts);
$ozone_plot->SetColor('firebrick4');
$ozone_plot->SetWeight(2);
$chlorine_plot=new LinePlot($chlorine,$ts);
$chlorine_plot->SetColor('deepskyblue2');
$chlorine_plot->SetWeight(2);

$graph = new Graph($width,$height);
$graph->SetFrame(false);
$graph->SetBackgroundImage('traffic33_66.png',BGIMG_FILLPLOT);
$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('datlin',0,20);
$graph->Add($ozone_plot);
$graph->SetY2Scale('lin',0,20);
$graph->AddY2($chlorine_plot);

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetDateFormat('g a');
$graph->xaxis->SetWeight(2);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);

$graph->yaxis->SetWeight(2);
$graph->yaxis->SetColor('firebrick4');
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->yaxis->title->SetColor('firebrick4');
$graph->yaxis->title->Set('% Ozone');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);

$graph->y2axis->SetWeight(2);
$graph->y2axis->SetColor('deepskyblue2');
$graph->y2axis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->y2axis->title->SetColor('deepskyblue2'); 
$graph->y2axis->title->Set('% Chlorine');
$graph->y2axis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->y2axis->title->SetAngle(90);
$graph->y2axis->title->SetMargin(10);

// Display the graph
$graph->Stroke();
?>
