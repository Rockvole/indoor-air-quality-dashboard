<?php // content="text/plain; charset=utf-8"
include 'graph_base.php';

$zoom_level=$geo_row['zoom_sewer'];
$result=mysqli_query($conn,"SELECT * from readings WHERE group_id=$id and ts>= $start_ts and ts<= ($end_ts + 1) order by ts"); 
$ts=Array();
$sewer=Array();
$found=false;
while($row = mysqli_fetch_array($result)) {
  $ts_str=gmdate('r', $row['ts']);
  //error_log("||ts=".$row['ts']."||ts=".$ts_str."||sewer=".$row['sewer']);
  if(strlen($row['sewer'])>0) {
    //error_log("-------------------------------------");
    $ts[]=$row['ts'];
    
    if($row['ts']<1424722800 && $id==5) $sewer[]=$row['sewer']; // $sewer[]=$row['sewer']/2; // 23 Feb 2015 12:20pm - Temporary hack to deal with incorrect resistance in original IAQ shield
      else if($row['ts']<1424989200 && $id==5) $sewer[]=$row['sewer']/2; // 26 Feb 2015 2:20pm - Temporary hack to deal with ADC change
        else $sewer[]=$row['sewer'];
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
$sewer_plot=new LinePlot($sewer,$ts);
$sewer_plot->SetColor('darkgoldenrod');
$sewer_plot->SetWeight(2);
$sewer_plot->SetFillColor($line_fill_color);

$graph = new Graph($width,$height);
$graph->SetFrame(false);
if($zoom_level==0)
  $graph->SetBackgroundImage('background_h_33_66.png',BGIMG_FILLPLOT);
else  
  $graph->SetBackgroundImage('background_h_10_20.png',BGIMG_FILLPLOT);

$graph->SetBackgroundImageMix(35);
$graph->SetMargin(60,60,40,50);
$graph->SetMarginColor('white');
$graph->SetScale('datlin',$SEWER_MIN,$SEWER_MAX[$zoom_level]);
$graph->Add($sewer_plot);

$graph->ygrid->SetColor("azure3");

$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetDateFormat('g a');
$graph->xaxis->SetWeight(2);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);

$graph->yaxis->SetWeight(2);
$graph->yaxis->SetColor('darkgoldenrod');
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,$font_size-3);
$graph->yaxis->title->SetColor('darkgoldenrod');
$graph->yaxis->title->Set("VOC's / Sewer");
$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,$font_size);
$graph->yaxis->title->SetAngle(90);
$graph->yaxis->title->SetMargin(10);
$graph->yaxis->SetTickPositions(array(0,200,400,600,800,1000,1200,1400,1600,1800,2000), null);

add_plotlines($start_ts, $ts);

// Display the graph
$graph->Stroke();
?>
