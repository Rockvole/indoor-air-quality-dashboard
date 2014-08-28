<html>
  <head>
    <title>Dashboard</title>
  </head>
  
  <body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include 'globals.php';
$param_date_format='Y-m-d';

if(!isset($_GET["id"])) exit("Must specify id parameter");
$id = htmlspecialchars($_GET["id"]);
$start_date_param = htmlspecialchars($_GET["start_date"]);
$direction_param = htmlspecialchars($_GET["direction"]);

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
  echo "<tr><td><h2>Temperature/Humidity Dashboard</h2></td>";
  echo "<td><input type='button' value='Download CSV' onclick='location.href=\"download_csv.php?id=$id\"'></td></tr>";
  echo "</table>";  

  echo "<table border=0>";
  echo "<tr>";
  echo "<td width='200'></td>";
  echo "<td align='right'><input type='button' value='&lt; Previous' onclick='location.href=\"dashboard.php?id=$id&start_date=$prev_day_str&period=day&direction=prev\"'></td>";
  echo "<td width='240' align=center ><b>".$date->format('l, F jS Y')."</b></td>";
  echo "<td><input type='button' value='Next    &gt;' onclick='location.href=\"dashboard.php?id=$id&start_date=$next_day_str&period=day&direction=next\"'></td>";
  echo "<td width='200'></td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td colspan=5>";
  echo "<img src='graphs/dht22.php?id=$id&width=1000&height=500&start_ts=$start_day_utc&end_ts=$end_day_utc' width='1000' height='500'>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
}
mysqli_free_result($result);
?>
  </body>  
</html>
