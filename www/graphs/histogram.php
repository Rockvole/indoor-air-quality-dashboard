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
    function change_date(year,month,day,direction) {
      document.dash.action = "histogram.php";
      document.dash.type.value="day";      
      document.dash.year.value=year;
      document.dash.month.value=month;
      document.dash.day.value=day;
      document.dash.direction.value=direction;
      document.dash.submit();
    }    
  </script>  
  <body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include '../globals.php';

$type = $_GET["type"];
$type_day=true;
if(strlen($type)>0) 
  if(strcmp($type,"day")!=0) $type_day=false; 

$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
$year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
$day_param = filter_input(INPUT_GET, 'day', FILTER_VALIDATE_INT);

$range_width=100;
$graph_width=1000;
$width_pix = array(300, 600, 1000);
$height_pix = array(150, 300, 500);

$direction_param = htmlspecialchars($_GET["direction"]);
if(!isset($_GET["size"])) $size=1;
  else $size = htmlspecialchars($_GET["size"]);
if($size==0) $default_size_0="selected='selected'";
if($size==1) $default_size_1="selected='selected'";
if($size==2) $default_size_2="selected='selected'";

if($type_day) {
  if(strlen($day_param)<=0) {
    $result=mysqli_query($conn,"SELECT MAX(ts) as ts from readings WHERE core_id=$id");
  } else if(strcmp($direction_param, "next")==0) { // Next button pressed
    $dt = Carbon::createFromDate($year,$month,$day_param);
    $dt_utc = $dt->startOfDay()->format('U');
    $result=mysqli_query($conn,"SELECT MIN(ts) as ts from readings WHERE core_id=$id and ts > $dt_utc");
  } else { // previous button pressed
    $dt = Carbon::createFromDate($year,$month,$day_param);
    $dt_utc = $dt->endOfDay()->format('U');
    $result=mysqli_query($conn,"SELECT MAX(ts) as ts from readings WHERE core_id=$id and ts < $dt_utc");
  }  
  if(mysql_errno()) {
    exit('Error: '.mysqli_error($conn));
  }
  $row = mysqli_fetch_array($result);
  if(!isset($row['ts'])) {
    echo "No records found";
  } else {  
    $date=Carbon::createFromTimeStamp($row['ts']);
    $prev_date=$date->copy()->subDay();
    $next_date=$date->copy()->addDay();
    $start_day_utc = $date->startOfDay()->format('U');
    $end_day_utc = $date->endOfDay()->format('U'); 
    
    $prev_year  = $prev_date->format('Y');
    $prev_month = $prev_date->format('m');
    $prev_day   = $prev_date->format('d');
    $next_year  = $next_date->format('Y');
    $next_month = $next_date->format('m');    
    $next_day   = $next_date->format('d');
  }
}

  $title=$_GET["name"];

  // ------------------------------------------------------------------- Heading
  echo "<div style='padding:10px;'>";
  echo "<table border=0>";
  echo "<tr><td width=$range_width></td><td width =$graph_width></td><td width=100></td></tr>";
  echo "<tr><td colspan=3>";
  echo "<table border=0 width=100%>";
  echo "<tr><td>";
  echo "<h2>";
  echo "<h2>Histogram of ".$title."</h2>\n";

  echo "</h2>";
  echo "</td><td width='400' style='vertical-align:top'>";

  echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>";
  echo $sensor_name;  
  echo "</span>";
  echo "</td>";
  echo "<td align=right width=400>";
  echo "<select name='size' id='size_id' onchange='change_size(document.getElementById(\"size_id\").value);'>";
  echo "<option value=0 $default_size_0>Small</option>";
  echo "<option value=1 $default_size_1>Medium</option>";
  echo "<option value=2 $default_size_2>Large</option>";
  echo "</select>";
  echo "</td></tr>";
  echo "</table>";  
  echo "<tr>";
  echo "<td colspan=2 width=$graph_width align=center>";
  if($type_day) {
    echo "<table border=0>";
    echo "<tr>";
    echo "  <td align='right'><input type='button' value='&lt; Previous' onclick='change_date($prev_year,$prev_month,$prev_day,\"prev\")'></td>";
    echo "  <td width='300' align=center >";
    echo "  <input type='button' value='".$date->format('l, F jS Y')."' onclick='go_calendar(0);'>";
    echo "  </td>";
    echo "  <td><input type='button' value='Next    &gt;' onclick='change_date($next_year,$next_month,$next_day,\"next\")'></td>";
    echo "</tr>";
    echo "</table>";
  }
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "</div>";  

  // ------------------------------------------------------------------- Sewer
  echo "<div class='container'>\n";  
  echo "<table border=0>\n";      
  echo "<tr>\n";
  echo "<td align=center colspan=2><h3 style='display:inline;'>Sewer Gas</h3>&nbsp;\n";
  echo "</td>\n";   
  echo "</tr>\n";
  echo "<tr>\n";  
  echo "<td>\n";
  echo "<img src='hist_sewer.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(2);' style='cursor:pointer;'>\n";
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
  echo "<input type='hidden' name='direction' value=''>\n";  
  echo "<input type='hidden' name='size' value='$size'>\n";  
  echo "</form>\n";
mysqli_free_result($result);
?>
  </body>  
</html>
