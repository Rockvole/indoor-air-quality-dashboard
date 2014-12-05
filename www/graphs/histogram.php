<html>
  <head>
    <title>Histograms</title>
    <link rel="stylesheet" type="text/css" href="../html/stylesheet.css">
  </head>  
  <script>
    function change_size(size) { 
      document.dash.action = "histogram.php";
      document.dash.size.value=size;
      document.dash.submit();
    }
  </script>  
  <body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include '../globals.php';

$type = $_GET["type"];
$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
$year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
$day_param = filter_input(INPUT_GET, 'day', FILTER_VALIDATE_INT);

$range_width=100;
$graph_width=1000;
$width_pix = array(300, 600, 1000);
$height_pix = array(150, 300, 500);
if(!isset($_GET["size"])) $size=1;
  else $size = htmlspecialchars($_GET["size"]);
if($size==0) $default_size_0="selected='selected'";
if($size==1) $default_size_1="selected='selected'";
if($size==2) $default_size_2="selected='selected'";

$date=Carbon::createFromDate($year,$month,$day_param);
$title=$_GET["name"];

  echo "<table border=0 width=100%>\n";
  echo "<tr><td>\n";
  if(strcmp($type,"day")==0) {
    echo "<h2>".$title." - ".$date->format('l, F jS Y')."</h2>\n";
    $start_day_utc = $date->startOfDay()->format('U');
    $end_day_utc = $date->endOfDay()->format('U');    
  } else {
    echo "<h2>".$title."</h2>\n";
  }
  echo "</td><td width='300' style='vertical-align:top'>\n";

  echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>\n";
  echo $sensor_name;  
  echo "</span>\n";
  echo "</td>\n";
  echo "<td align=right width=400>\n";
  echo "<select name='size' id='size_id' onchange='change_size(document.getElementById(\"size_id\").value);'>\n";
  echo "<option value=0 $default_size_0>Small</option>\n";
  echo "<option value=1 $default_size_1>Medium</option>\n";
  echo "<option value=2 $default_size_2>Large</option>\n";
  echo "</select>\n";
  echo "</td></tr>\n";
  echo "</table>\n";

  // ------------------------------------------------------------------- Sewer
  echo "<div class='container'>\n";  
  echo "<table border=0>\n";      
  echo "<tr>\n";
  echo "<td align=center colspan=2><h3 style='display:inline;'>Sewer Gas</h3>&nbsp;\n";
  echo "</td>\n";   
  echo "</tr>\n";
  echo "<tr>\n";  
  echo "<td>\n";
  echo "<img src='sewer.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(2);' style='cursor:pointer;'>\n";
  echo "</td>\n";
  echo "</tr>\n";
 
  echo "</table>\n";
  echo "</div>\n"; 
  // ------------------------------------------------------------------- Form
  echo "<form action='histogram.php' method='get' name='dash'>\n";
  echo "<input type='hidden' name='id' value='$id'>\n";
  echo "<input type='hidden' name='type' value='$type'>\n";
  echo "<input type='hidden' name='name' value='$title'>\n";
  echo "<input type='hidden' name='event_id' value='$event_id'>\n";
  echo "<input type='hidden' name='year' value='$year'>\n";
  echo "<input type='hidden' name='month' value='$month'>\n";
  echo "<input type='hidden' name='day' value='$day_param'>\n";
  echo "<input type='hidden' name='size' value=''>\n";  
  echo "</form>\n";
mysqli_free_result($result);
?>
  </body>  
</html>
