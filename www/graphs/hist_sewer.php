<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';
$INTERVAL_COUNT=24;
$range_interval=($MAX_RANGE_SEWER/$INTERVAL_COUNT);

$ts=Array();
$sewer=Array();
if($result=mysqli_query($conn,"SELECT * from readings WHERE group_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts")) {
  while($row = mysqli_fetch_array($result)) {
    $ts_str=gmdate('r', $row['ts']);
    //error_log("temp=".$row['temperature']."||humidity=".$row['humidity']."||ts=".$row['ts']."||ts=".$ts_str);
    $ts[]=$row['ts'];
    $sewer[]=$row['sewer'];
  }
}
$bucket=Array();
$tick_labels=Array();
// Initialize bucket with 0's
for($i=0;$i<$INTERVAL_COUNT;$i++) {
  $bucket[$i]=0;
  $tick_labels[$i]=($i*$range_interval)."+"; //.((($i+1)*$range_interval)-1);
}
foreach($sewer as $value) {
  $bucket_pos=floor($value / $range_interval);
  if($bucket_pos>=$INTERVAL_COUNT) $bucket_pos=($INTERVAL_COUNT-1);   
  $bucket[$bucket_pos]++;
  //error_log("item=".$value."||bucket_pos=".$bucket_pos."||count=".$bucket[$bucket_pos]);  
}

$sewer_plot=new BarPlot($bucket);
$sewer_plot->SetColor('darkgoldenrod');
$sewer_plot->SetWeight(2);
$sewer_plot->SetFillColor('darkgoldenrod');

$graph = new Graph($width,$height);
$graph->SetFrame(false);
$graph->SetBackgroundImage('background_v_33_66.png',BGIMG_FILLPLOT);
$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('textlin');
$graph->Add($sewer_plot);

$graph->ygrid->SetColor("azure3");

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetWeight(2);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->xaxis->SetTickLabels($tick_labels);

$graph->yaxis->SetWeight(2);
$graph->yaxis->SetColor('darkgoldenrod');
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->yaxis->title->SetColor('darkgoldenrod');
$graph->yaxis->title->Set('Number of occurrences');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);

// Display the graph
$graph->Stroke();
?>
