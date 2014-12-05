<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';

$result=mysqli_query($conn,"SELECT * from readings WHERE core_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts"); 
$ts=Array();
$hcho=Array();
$level=Array();
while($row = mysqli_fetch_array($result)) {
	$ts_str=gmdate('r', $row['ts']);
	error_log("temp=".$row['temperature']."||humidity=".$row['humidity']."||ts=".$row['ts']."||ts=".$ts_str);
	$ts[]=$row['ts'];
	$hcho[]=$row['hcho'];
	$level[]=1000;
}
$hcho_plot=new LinePlot($hcho,$ts);
$hcho_plot->SetColor('firebrick4');
$hcho_plot->SetWeight(2);
$hcho_plot->SetFillColor($line_fill_color);

$graph = new Graph($width,$height);
$graph->SetFrame(false);
$graph->SetBackgroundImage('background_h_33_66.png',BGIMG_FILLPLOT);
$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('datlin',0,200);
$graph->Add($hcho_plot);

$graph->ygrid->SetColor("azure3");

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetDateFormat('g a');
$graph->xaxis->SetWeight(2);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);

$graph->yaxis->SetWeight(2);
$graph->yaxis->SetColor('firebrick4');
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->yaxis->title->SetColor('firebrick4');
$graph->yaxis->title->Set('Formaldehyde');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);
//$graph->yaxis->SetTickPositions(array(0,10,20,30,40,50,60,70,80,90,100), null);

// Display the graph
$graph->Stroke();
?>
