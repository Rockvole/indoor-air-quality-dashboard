<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';
$range_interval=250;
$range_count=18;

$result=mysqli_query($conn,"SELECT * from readings WHERE core_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts"); 
$ts=Array();
$dust=Array();
while($row = mysqli_fetch_array($result)) {
	$ts_str=gmdate('r', $row['ts']);
	//error_log("temp=".$row['temperature']."||humidity=".$row['humidity']."||ts=".$row['ts']."||ts=".$ts_str);
	$ts[]=$row['ts'];
	$dust[]=$row['dust'];
}
// Deal with putting values in buckets for histogram
$bucket=Array();
$tick_labels=Array();
// Initialize bucket with 0's
for($i=0;$i<$range_count;$i++) {
  $bucket[$i]=0;
  $tick_labels[$i]=($i*$range_interval)."+"; //.((($i+1)*$range_interval)-1);
}
foreach($dust as $value) {
  $bucket_pos=floor($value / $range_interval);
  if($bucket_pos>=$range_count) $bucket_pos=($range_count-1);  
  $bucket[$bucket_pos]++;
  //error_log("item=".$value."||bucket_pos=".$bucket_pos."||count=".$bucket[$bucket_pos]);  
}

// Now draw bar plot
$dust_plot=new BarPlot($bucket);
$dust_plot->SetColor('darkgray');
$dust_plot->SetWeight(2);
$dust_plot->SetFillColor('darkgray');

$graph = new Graph($width,$height);
$graph->SetFrame(false);
$graph->SetBackgroundImage('background_v_33_66.png',BGIMG_FILLPLOT);
$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('textlin');
$graph->Add($dust_plot);

$graph->ygrid->SetColor("azure3");

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetWeight(2);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->xaxis->SetTickLabels($tick_labels);

$graph->yaxis->SetWeight(2);
$graph->yaxis->SetColor('darkgray');
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->yaxis->title->SetColor('darkgray');
$graph->yaxis->title->Set('Dust');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);

// Display the graph
$graph->Stroke();
?>
