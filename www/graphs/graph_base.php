<?php
include '../globals.php';
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ("jpgraph/jpgraph_date.php");
require_once ("jpgraph/jpgraph_bar.php");
require_once ("jpgraph/jpgraph_plotline.php");

if(!isset($_GET["id"])) {
  error_log("Must specify id parameter");
  exit();
}
if(!isset($_GET["width"])) {
  error_log("Must specify width parameter");
  exit();
}
if(!isset($_GET["height"])) {
  error_log("Must specify height parameter");
  exit();
}
if(!isset($_GET["start_ts"])) {
  error_log("Must specify start_ts parameter");
  exit();
}
if(!isset($_GET["end_ts"])) {
  error_log("Must specify end_ts parameter");
  exit();
}

$id = htmlspecialchars($_GET["id"]);
$width = htmlspecialchars($_GET["width"]);
$height = htmlspecialchars($_GET["height"]);
$start_ts = htmlspecialchars($_GET["start_ts"]);
$end_ts = htmlspecialchars($_GET["end_ts"]);
$geo_row = get_current_geographical($end_ts,$id);

$font_size=11;
$line_fill_color='white@0.65';

function add_plotlines($start_ts, $ts_arr) {
  global $graph;
  $last_ts=end($ts_arr);
  $first_ts=reset($ts_arr);
  
  $line_pos=$start_ts+(3600*8);
  if($first_ts<$line_pos && $last_ts>$line_pos) {
    $line8 = new PlotLine(VERTICAL,$line_pos,'azure3',1);
    $graph->AddLine($line8);
  }
  $line_pos=$start_ts+(3600*16);
  if($first_ts<$line_pos && $last_ts>$line_pos) {
    $line16 = new PlotLine(VERTICAL,$line_pos,'azure3',1);
    $graph->AddLine($line16);
  }
}
?>
