<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';

$result=mysqli_query($conn,"SELECT * from readings WHERE core_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts"); 
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
$humidity_plot=new LinePlot($humidity,$ts);
$humidity_plot->SetColor('dodgerblue');
$humidity_plot->SetWeight(2);
$humidity_plot->SetFillColor($line_fill_color);
$temperature_plot=new LinePlot($temperature,$ts);
$temperature_plot->SetColor('hotpink3');
$temperature_plot->SetWeight(2);

$graph = new Graph($width,$height);
$graph->SetFrame(false);
$graph->SetBackgroundImage('traffic40_60.png',BGIMG_FILLPLOT);
$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('datlin',0,100);
$graph->Add($humidity_plot);
$graph->SetY2Scale('lin',5,40);
$graph->AddY2($temperature_plot);

$graph->ygrid->SetColor("azure3");
$graph->ygrid->Show(true, true);

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetDateFormat('g a');
$graph->xaxis->SetWeight(2);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);

$graph->yaxis->SetWeight(2);
$graph->yaxis->SetColor('dodgerblue');
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->yaxis->title->SetColor('dodgerblue');
$graph->yaxis->title->Set('%RH');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);

$graph->y2axis->SetWeight(2);
$graph->y2axis->SetColor('hotpink3');
$graph->y2axis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->y2axis->title->SetColor('hotpink3'); 
$graph->y2axis->title->Set('C');
$graph->y2axis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->y2axis->title->SetAngle(90);
$graph->y2axis->title->SetMargin(10);

// Display the graph
$graph->Stroke();
?>
