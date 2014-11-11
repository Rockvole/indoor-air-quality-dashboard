<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;

$background_state=0;
$ts_arr = array_fill(0, 24, NULL);
$name_arr = array_fill(0, 24, NULL);

$result=mysqli_query($conn,"SELECT name from events where ts=".
                            "(SELECT max(ts) from events where ts<$start_day_utc)");
$row = mysqli_fetch_array($result);
if(isset($row['name'])) $background_state=1;

$result=mysqli_query($conn,"SELECT * from events where ts>=$start_day_utc and ts<$end_day_utc and core_id=$id order by ts");
while($row = mysqli_fetch_array($result)) {
  $event_ts = Carbon::createFromTimeStamp($row['ts']);
  $event_hour = $event_ts->format('G');
  $ts_arr[$event_hour] = $event_ts;
  $name_arr[$event_hour] = $row['name'];
}

echo "<table border=0 width=100%>";
echo "<tr>";
for($i=0;$i<24;$i++) {
  $background='';
  if(isset($ts_arr[$i])) {
    if(!isset($name_arr[$i])) {
      $background_state=0;
    } else if($background_state==1) {
      $background_state=2;
    } else {
      $background_state=1;
    }
  }
  if($background_state==1) {
    $background='background-color:#CC6666;';
  } else if($background_state==2) {
    $background='background-color:#3399CC;';
  }  
  echo "<td style='text-align:center;font-size:11px;cursor:pointer;border:1px solid purple;$background' ";
  echo "onclick='location.href=\"events/manage_event.php?id=$id&start_date=$start_date_param&hour=$i\"'>";
  echo sprintf("%1$02d",$i);
  echo "</td>";
  
}
echo "</tr>";
echo "</table>";

?>
