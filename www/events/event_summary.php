<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
$clear_location='clear:left';

// --------------------------------------------------------------------- EVENTS
$result=mysqli_query($conn,"SELECT * from events where ts>=$start_day_utc and ts<$end_day_utc and core_id=$id order by ts");
$name_arr = array();
$ts_arr = array();
while($row = mysqli_fetch_array($result)) {
  $name_arr[] = $row['name'];
  $ts_arr[]=$row['ts'];
}

$arr_count=count($ts_arr);
if($arr_count>0) {
  $clear_location='';
  echo "<div class='container' style='clear:left;background-color:white;' >";
  echo "<h3 style='text-align:left;'>Events</h3>";
  echo "<table border=0 style='border-spacing:6px;'>";

  for($i=0;$i<$arr_count;$i++) {
    if(isset($name_arr[$i])) {
      echo "<tr>";
      echo "<td>";
      echo $name_arr[$i];
      echo "</td>";
      echo "<td>";
      $event_ts = Carbon::createFromTimeStamp($ts_arr[$i]);
      echo $event_ts->format('H:i');
      if(($i+1)<$arr_count) {
        $event_ts = Carbon::createFromTimeStamp($ts_arr[$i+1]);
        echo "-".$event_ts->format('H:i');
      }
      echo "</td>";  
      echo "</tr>";
    }
  }
  echo "</table>";
  echo "</div>";
}

// --------------------------------------------------------------------- LOCATIONS
$result=mysqli_query($conn,"SELECT * from locations where ts>=$start_day_utc and ts<$end_day_utc and core_id=$id order by ts");
$name_arr = array();
$ts_arr = array();
while($row = mysqli_fetch_array($result)) {
  $name_arr[] = $row['room_name'];
  $ts_arr[]=$row['ts'];
}

$arr_count=count($ts_arr);
if($arr_count>0) {
  echo "<div class='container' style='background-color:white;$clear_location' >";
  echo "<h3 style='text-align:left;'>Location Changes</h3>";
  echo "<table border=0 style='border-spacing:6px;'>";

  for($i=0;$i<$arr_count;$i++) {
    if(isset($name_arr[$i])) {
      echo "<tr>";
      echo "<td>";
      echo $name_arr[$i];
      echo "</td>";
      echo "<td>";
      $event_ts = Carbon::createFromTimeStamp($ts_arr[$i]);
      echo $event_ts->format('H:i');
      echo "</td>";  
      echo "</tr>";
    }
  }
  echo "</table>";
  echo "</div>";
}

?>
