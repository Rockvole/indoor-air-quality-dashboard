<html>
  <head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="html/stylesheet.css">
  </head>
  <script>
    function change_size(size) {
      document.dash.action = "dashboard.php";
      document.dash.size.value=size;
      document.dash.submit();
    }
    function change_date(date,direction) {
      document.dash.action = "dashboard.php";
      document.dash.start_date.value=date;
      document.dash.direction.value=direction;
      document.dash.submit();
    }
    function go_calendar(sensor) {
      document.dash.action = "year_cal.php";
      document.dash.sensor.value=sensor;
      document.dash.submit();
    }
  </script>
  <body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include 'globals.php';
$range_width=100;
$graph_width=1000;
$width_pix = array(300, 600, 1000);
$height_pix = array(150, 300, 500);

if(!isset($_GET["id"])) exit("Must specify id parameter");
$id = htmlspecialchars($_GET["id"]);
$start_date_param = htmlspecialchars($_GET["start_date"]);
$direction_param = htmlspecialchars($_GET["direction"]);
if(!isset($_GET["size"])) $size=2;
  else $size = htmlspecialchars($_GET["size"]);
if($size==0) $default_size_0="selected='selected'";
if($size==1) $default_size_1="selected='selected'";
if($size==2) $default_size_2="selected='selected'";

$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 

if(strlen($start_date_param)<=0) {
	$result=mysqli_query($conn,"SELECT MAX(ts) as ts from readings WHERE core_id=$id");
} else if(strcmp($direction_param, "next")==0) { // Next button pressed
	$dt = Carbon::createFromFormat($param_date_format, $start_date_param);
	$dt_utc = $dt->startOfDay()->format('U');
	$result=mysqli_query($conn,"SELECT MIN(ts) as ts from readings WHERE core_id=$id and ts > $dt_utc");
} else { // previous button pressed
	$dt = Carbon::createFromFormat($param_date_format, $start_date_param);
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
  $date = Carbon::createFromTimeStamp($row['ts']);
  $start_day_utc = $date->startOfDay()->format('U');
  $prev_day_str = $date->copy()->subDay()->format($param_date_format);
  $next_day_str = $date->copy()->addDay()->format($param_date_format);
  $end_day_utc = $date->endOfDay()->format('U');
  
  echo "<div style='padding:10px;'>";
  echo "<table border=0>";
  echo "<tr><td width=$range_width></td><td width =$graph_width></td><td width=100></td></tr>";
  echo "<tr><td colspan=2>";
  echo "<table border=0>";
  echo "<tr><td>";
  echo "<h2>Indoor Air Quality Dashboard</h2>";
  echo "</td><td width='400' style='vertical-align:top'>";
  $loc_result=mysqli_query($conn,"SELECT MAX(ts) as ts from locations WHERE core_id=$id and ts <= $start_day_utc");
  $loc_row = mysqli_fetch_array($loc_result);
  $name_result=mysqli_query($conn,"SELECT name from locations WHERE core_id=$id and ts = ".$loc_row['ts']);
  $name_row = mysqli_fetch_array($name_result);
  $location=$name_row['name'];
  echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:blueviolet;vertical-align:top;'>(";
  if(strlen($location)>0)
    echo $location;
  else echo "Unknown location";  
  echo ")</span>";
  echo "<img src='html/location.png' onclick='location.href=\"add_location.php?id=$id&year=".$date->format('Y')."&month=".$date->format('n')."\"' style='cursor:pointer;'>";
  echo "</td></tr>";
  echo "</table>";
  echo "<td align=right><input type='button' value='Download CSV' onclick='location.href=\"download_csv.php?id=$id\"'></td></tr>";
  echo "<tr>";
  echo "<td colspan=2 width=$graph_width align=center>";
  echo "<table border=0>";
  echo "<tr>";
  echo "  <td align='right'><input type='button' value='&lt; Previous' onclick='change_date(\"$prev_day_str\",\"prev\")'></td>";
  echo "  <td width='300' align=center >";
  echo "  <input type='button' value='".$date->format('l, F jS Y')."' onclick='go_calendar(0);'>";
  echo "  </td>";
  echo "  <td><input type='button' value='Next    &gt;' onclick='change_date(\"$next_day_str\",\"next\")'></td>";
  echo "</tr>";
  echo "</table>";
  echo "</td>";
  echo "<td width='100' align=right>";
  echo "<select name='size' id='size_id' onchange='change_size(document.getElementById(\"size_id\").value);'>";
  echo "<option value=0 $default_size_0>Small</option>";
  echo "<option value=1 $default_size_1>Medium</option>";
  echo "<option value=2 $default_size_2>Large</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "</div>";
  
  // ------------------------------------------------------------------- Temperature / Humidity
  echo "<div class='container'>";
  echo "<table border=0>";    
  echo "<tr>";
  echo "<td align=center colspan=2><h3>Temperature & Humidity</h3></td>";
  echo "</tr>";
  echo "<tr>";
  if($size==2) {
    echo "<td rowspan=2 width=$range_width style='height:100%;'>";
    echo "  <div style='height:100%;overflow:auto;'>";  
    echo "  <table style='width:100%;height:100%;' border=0>";
    echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
    echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
    echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
    echo "  </table>";
    echo "  </div>";  
    echo "</td>"; 
  }
  echo "<td>";
  echo "<img src='graphs/dht22.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(0);' style='cursor:pointer;'>";
  echo "</td>";
  echo "<td>";
  echo "<img src='health/mask.png' onclick='location.href=\"health/mold.html\"' style='cursor:pointer;'>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "</div>";
  // ------------------------------------------------------------------- Ozone and Chlorine
  echo "<div class='container'>";  
  echo "<table border=0>";      
  echo "<tr>";
  echo "<td align=center colspan=2><h3>Ozone and Chlorine Gas</h3></td>";
  echo "</tr>";
  echo "<tr>";  
  if($size==2) {  
    echo "<td rowspan=2 width=$range_width style='height:100%'>";
    echo "  <div style='height:100%;overflow:auto;'>";  
    echo "  <table style='width:100%;height:100%' border=0>";
    echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
    echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
    echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
    echo "  </table>";
    echo "  </div>";  
    echo "</td>";
  }
  echo "<td>";
  echo "<img src='graphs/mq131.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(1);' style='cursor:pointer;'>";
  echo "</td>";
  echo "</tr>";
 
  echo "</table>";
  echo "</div>";  
  // ------------------------------------------------------------------- Sewer
  echo "<div class='container'>";  
  echo "<table border=0>";      
  echo "<tr>";
  echo "<td align=center colspan=2><h3>Sewer Gas</h3></td>";
  echo "</tr>";
  echo "<tr>";  
  if($size==2) {  
    echo "<td rowspan=2 width=$range_width style='height:100%'>";
    echo "  <div style='height:100%;overflow:auto;'>";  
    echo "  <table style='width:100%;height:100%' border=0>";
    echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
    echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
    echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
    echo "  </table>";
    echo "  </div>";  
    echo "</td>";
  }
  echo "<td>";
  echo "<img src='graphs/sewer.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(1);' style='cursor:pointer;'>";
  echo "</td>";
  echo "</tr>";
 
  echo "</table>";
  echo "</div>";  
  // ------------------------------------------------------------------- Form
  echo "<form action='dashboard.php' method='get' name='dash'>";
  echo "<input type='hidden' name='id' value='$id'>";
  echo "<input type='hidden' name='start_date' value='$start_date_param'>";
  echo "<input type='hidden' name='end_date' value='$end_date_param'>";
  echo "<input type='hidden' name='period' value='day'>";
  echo "<input type='hidden' name='direction' value=''>";
  echo "<input type='hidden' name='size' value='$size'>";
  echo "<input type='hidden' name='sensor' value=''>";
  echo "</form>";
}
mysqli_free_result($result);
?>
  </body>  
</html>
