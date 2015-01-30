<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;

$background_state=0;
$ts_arr = array_fill(0, 24, NULL);
$name_arr = array_fill(0, 24, NULL);

$result=mysqli_query($conn,"SELECT * from state_type where location_id=$location_id order by name");

echo "<h3 style='clear:left;'>States</h3>";
echo "<table border=0>";
while($row = mysqli_fetch_array($result)) {  
  $hour_arr = get_state_changes($row['id']);
  echo get_event_row($row['id'],$row['name'],$hour_arr);  
}
echo "</table>\n"; 
echo "<img src='images/add.png' onclick='location.href=\"events/manage_states.php?id=$id&location_id=$location_id&start_date=".$start_date_param."&size=".$size."\"' height=30 width=30 style='cursor:pointer;'>";

// --------------------------------------------------------------------- FUNCTIONS
function get_state_changes($state_type_id) {
  global $conn;
  global $location_id;
  global $start_day_utc;
  
  $hour_arr = array_fill(0, 24, '');
  $curr_state=NULL;
  $curr_ts=$start_day_utc;
  $end_day_ts=$start_day_utc+(60 * 60 * 23);
  $curr_hour=0;

  $result=mysqli_query($conn,"SELECT * from state_changes where location_id=$location_id and state_type_id=$state_type_id order by ts");
  while($row = mysqli_fetch_array($result)) {
    $db_ts = $row['ts'];
    $db_time = Carbon::createFromTimeStamp($row['ts']);
    if($db_ts > $end_day_ts) { // db entry is in the future
      $db_ts = $end_day_ts;
      $db_state = $curr_state;
      $db_hour=23;
    } else {
      $db_state = $row['state'];
      $db_hour = $db_time->format('G');
    }
    
    if($db_ts < $curr_ts) { // db timestamp is earlier than current ts
      $curr_state = $db_state;
    } else if($db_ts == $curr_ts) { // db timestamp is the same as current ts
      $curr_state = $db_state;
      $hour_arr[$curr_hour]=$curr_state;
    } else if($db_ts > $curr_ts) { // db timestamp is newer than current ts
      $curr_state=$db_state;
      $curr_hour=$db_hour;
      $hour_arr[$curr_hour]=$curr_state;
    }
  }
  return $hour_arr;
}

function get_event_row($state_type_id,$name,$hour_arr) {
  global $date;
  global $id;
  global $size;
  global $location_id;
  global $start_date_param;
  
  $curr_state=2;
  $html="";
  $html.="<tr>";
  $html.="<th style='text-align:right;'>$name</th>";
  for($i=0;$i<24;$i++) {
    $background='';
    if(strlen($hour_arr[$i])>0) {
      if($hour_arr[$i]==0) $curr_state=0;
        else if($hour_arr[$i]==1) $curr_state=1;
    }
    if($curr_state==1) {
      $background='background-color:#EF9C47;';
    } else if($curr_state==0) {
      $background='background-color:#FDE0C1;';
    }  
    $curr_ts_utc=$date->copy()->startOfDay()->addHours($i)->format('U');
    $html.="<td style='text-align:center;font-size:11px;width:40px;cursor:pointer;border:1px solid purple;$background' ";
    $html.="onclick='location.href=\"events/add_state.php?id=$id&state_type_id=$state_type_id&location_id=$location_id&ts=$curr_ts_utc"."&start_date=".$start_date_param."&size=".$size."\"'>";
    $html.=sprintf("%1$02d",$i);
    $html.="</td>";
  }
  $html.="</tr>";
  return $html;
}
?>
