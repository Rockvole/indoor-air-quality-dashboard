<html>
  <head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="html/stylesheet.css">
  </head>
  <script>
    function change_size(size) {
      document.dash.size.value=size;
      document.dash.submit();
    }
    function change_date(date,direction) {
      document.dash.start_date.value=date;
      document.dash.direction.value=direction;
      document.dash.submit();
    }
  </script>
  <body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include 'globals.php';
$width_pix = array(300, 600, 1000);
$height_pix = array(150, 300, 500);
$param_date_format='Y-m-d';

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
  
  echo "<table border=0 width='100%'>";
  echo "<tr><td colspan=4><h2>Temperature/Humidity Dashboard</h2></td>";
  echo "<td align=right><input type='button' value='Download CSV' onclick='location.href=\"download_csv.php?id=$id\"'></td></tr>";
  echo "<tr>";
  echo "<td width='100'></td>";
  echo "<td align='right'><input type='button' value='&lt; Previous' onclick='change_date(\"$prev_day_str\",\"prev\")'></td>";
  echo "<td width='300' align=center ><b>".$date->format('l, F jS Y')."</b></td>";
  echo "<td><input type='button' value='Next    &gt;' onclick='change_date(\"$next_day_str\",\"next\")'></td>";
  echo "<td width='100' align=right>";
  echo "<select name='size' id='size_id' onchange='change_size(document.getElementById(\"size_id\").value);'>";
  echo "<option value=0 $default_size_0>Small</option>";
  echo "<option value=1 $default_size_1>Medium</option>";
  echo "<option value=2 $default_size_2>Large</option>";
  echo "</select>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  
  // ------------------------------------------------------------------- Temperature / Humidity
  echo "<div class='container'>";
  echo "<table border=0>";    
  echo "<tr>";
  echo "<td align=center><h3>Temperature & Humidity</h3></td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td>";
  echo "<img src='graphs/dht22.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]'>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  // ------------------------------------------------------------------- Form
  echo "<form action='dashboard.php' method='get' name='dash'>";
  echo "<input type='hidden' name='id' value='$id'>";
  echo "<input type='hidden' name='start_date' value='$start_date_param'>";
  echo "<input type='hidden' name='end_date' value='$end_date_param'>";
  echo "<input type='hidden' name='period' value='day'>";
  echo "<input type='hidden' name='direction' value=''>";
  echo "<input type='hidden' name='size' value='$size'>";
  echo "</form>";
}
mysqli_free_result($result);
?>
  </body>  
</html>
