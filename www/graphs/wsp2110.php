<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';

$result=mysqli_query($conn,"SELECT * from readings WHERE group_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts"); 
$ts=Array();
$hcho=Array();
$found=false;
while($row = mysqli_fetch_array($result)) {
  $ts_str=gmdate('r', $row['ts']);
  //error_log("temp=".$row['temperature']."||humidity=".$row['humidity']."||ts=".$row['ts']."||ts=".$ts_str);
  if(strlen($row['hcho'])>0) {
    $ts[]=$row['ts'];
    
    if($row['ts']<1424989200 && $id=5) $hcho[]=$row['hcho']/2; // 26 Feb 2015 2:20pm - Temporary hack to deal with ADC change
      else $hcho[]=$row['hcho'];
    $found=true;  
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
$graph->SetScale('datlin',$HCHO_MIN,$HCHO_MAX);
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
add_plotlines($start_ts, $ts);
$graph->yaxis->SetTickPositions(array(0,10,20,30,40,50,60,70,80,90), null);

// Display the graph
$graph->Stroke();
?>
