<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;

$result=mysqli_query($conn,"SELECT * from events where ts>=$start_day_utc and ts<$end_day_utc order by ts");
$name_arr = array();
$ts_arr = array();
while($row = mysqli_fetch_array($result)) {
  $name_arr[] = $row['name'];
  $ts_arr[]=$row['ts'];
}

$arr_count=count($ts_arr);
if($arr_count>0) {
  echo "<div class='container' style='clear:left;background-color:cyan;' >";
  echo "<h3 style='padding-left:0px;'>Events</h3>";
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

?>
