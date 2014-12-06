<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';
$range_interval=10;
$range_count=10;

$result=mysqli_query($conn,"SELECT * from readings WHERE core_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts"); 
$ts=Array();
$humidity=Array();
while($row = mysqli_fetch_array($result)) {
	$ts_str=gmdate('r', $row['ts']);
	//error_log("temp=".$row['temperature']."||humidity=".$row['humidity']."||ts=".$row['ts']."||ts=".$ts_str);
	$ts[]=$row['ts'];
	$humidity[]=$row['humidity'];
}
// Deal with putting values in buckets for histogram
$bucket=Array();
$tick_labels=Array();
// Initialize bucket with 0's
for($i=0;$i<$range_count;$i++) {
  $bucket[$i]=0;
  $tick_labels[$i]=($i*$range_interval)."+"; //.((($i+1)*$range_interval)-1);
}
foreach($humidity as $value) {
  $bucket_pos=floor($value / $range_interval);
  if($bucket_pos>=$range_count) $bucket_pos=($range_count-1);  
  $bucket[$bucket_pos]++;
  //error_log("item=".$value."||bucket_pos=".$bucket_pos."||count=".$bucket[$bucket_pos]);  
}

$humidity_plot=new BarPlot($bucket);
$humidity_plot->SetColor('dodgerblue');
$humidity_plot->SetWeight(2);
$humidity_plot->SetFillColor('dodgerblue');

$graph = new Graph($width,$height);
$graph->SetFrame(false);
$graph->SetBackgroundImage('background_v_40_60.png',BGIMG_FILLPLOT);
$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('textlin');
$graph->Add($humidity_plot);

$graph->ygrid->SetColor("azure3");

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetWeight(2);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->xaxis->SetTickLabels($tick_labels);

$graph->yaxis->SetWeight(2);
$graph->yaxis->SetColor('dodgerblue');
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->yaxis->title->SetColor('dodgerblue');
$graph->yaxis->title->Set('Number of occurrences');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);

// Display the graph
$graph->Stroke();
?>
