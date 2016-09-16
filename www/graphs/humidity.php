<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';

$zoom_level=$geo_row['zoom_temp_hum'];
$result=mysqli_query($conn,"SELECT * from readings WHERE group_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts"); 
$ts=Array();
$humidity=Array();
$found=false;
while($row = mysqli_fetch_array($result)) {
  $ts_str=gmdate('r', $row['ts']);
  //error_log("||ts=".$row['ts']."||ts=".$ts_str."||temp=".$row['temperature']."||humidity=".$row['humidity']);
  
  if(strlen($row['humidity'])>0) {
    $ts[]=$row['ts'];
    $humidity[]=$row['humidity'];
    $found=true;
  } else {
    if(!$found) {
      $ts[]=$row['ts'];
      $temperature[]=5;
    }
  }
}

if(!$found) {
  $name = 'no_data.png';
  $fp = fopen($name, 'rb');

  // send the right headers
  header("Content-Type: image/png");
  header("Content-Length: " . filesize($name));

  // dump the picture and stop the script
  fpassthru($fp);
  exit;
}
$humidity_plot=new LinePlot($humidity,$ts);
$humidity_plot->SetColor('dodgerblue');
$humidity_plot->SetWeight(2);
$humidity_plot->SetFillColor($line_fill_color);

$graph = new Graph($width,$height);
$graph->SetFrame(false);
if($zoom_level==0)
  $graph->SetBackgroundImage('background_h_25_75.png',BGIMG_FILLPLOT);
else
  $graph->SetBackgroundImage('background_h_40_60.png',BGIMG_FILLPLOT);
  
$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('datlin',$HUMIDITY_MIN[$zoom_level],$HUMIDITY_MAX[$zoom_level]);
$graph->Add($humidity_plot);

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
$graph->yaxis->SetTickPositions(array(0,10,20,30,40,50,60,70,80,90,100), null);

add_plotlines($start_ts, $ts);

// Display the graph
$graph->Stroke();
?>
