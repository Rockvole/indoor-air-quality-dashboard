<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';
$range_interval=25;
$range_count=12;

$ts=Array();
$hcho=Array();
if($result=mysqli_query($conn,"SELECT * from readings WHERE core_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts")) {
  while($row = mysqli_fetch_array($result)) {
    $ts_str=gmdate('r', $row['ts']);
    //error_log("temp=".$row['temperature']."||humidity=".$row['humidity']."||ts=".$row['ts']."||ts=".$ts_str);
    $ts[]=$row['ts'];
    $hcho[]=$row['hcho'];
  }
}
// Deal with putting values in buckets for histogram
$bucket=Array();
$tick_labels=Array();
// Initialize bucket with 0's
for($i=0;$i<$range_count;$i++) {
  $bucket[$i]=0;
  $tick_labels[$i]=($i*$range_interval)."+"; //.((($i+1)*$range_interval)-1);
}
foreach($hcho as $value) {
  $bucket_pos=floor($value / $range_interval);
  if($bucket_pos>=$range_count) $bucket_pos=($range_count-1);   
  $bucket[$bucket_pos]++;
  //error_log("item=".$value."||bucket_pos=".$bucket_pos."||count=".$bucket[$bucket_pos]);  
}

// Now draw bar plot
$hcho_plot=new BarPlot($bucket);
$hcho_plot->SetColor('firebrick4');
$hcho_plot->SetWeight(2);
$hcho_plot->SetFillColor('firebrick4');

$graph = new Graph($width,$height);
$graph->SetFrame(false);
$graph->SetBackgroundImage('background_v_33_66.png',BGIMG_FILLPLOT);
$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('textlin');
$graph->Add($hcho_plot);

$graph->ygrid->SetColor("azure3");

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetWeight(2);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->xaxis->SetTickLabels($tick_labels);

$graph->yaxis->SetWeight(2);
$graph->yaxis->SetColor('firebrick4');
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->yaxis->title->SetColor('firebrick4');
$graph->yaxis->title->Set('Number of occurrences');
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);
//$graph->yaxis->SetTickPositions(array(0,10,20,30,40,50,60,70,80,90,100), null);

// Display the graph
$graph->Stroke();
?>
