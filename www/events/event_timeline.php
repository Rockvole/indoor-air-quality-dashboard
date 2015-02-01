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
if(strlen($location_id)<=0) {
  echo "<i>Cannot add a State without a valid room location for the whole day</i><br/>";
  echo "<i>Click on Events 00:00 to set a room.</i>";
} else {
  echo "<img src='images/add.png' onclick='location.href=\"events/manage_states.php?id=$id&location_id=$location_id&start_date=".$start_date_param."&size=".$size."\"' height=30 width=30 style='cursor:pointer;'>";
}
// --------------------------------------------------------------------- FUNCTIONS
function get_state_changes($state_type_id) {
  global $conn;
  global $location_id;
  global $start_day_utc;
  
  $hour_arr = array_fill(0, 24, array_fill(0, 2, NULL));
  $curr_state=NULL;
  $curr_ts=$start_day_utc;
  $end_day_ts=$start_day_utc+(60 * 60 * 23);
  $curr_hour=0;

  $sql="SELECT * from state_changes where location_id=$location_id and state_type_id=$state_type_id order by ts";
  $result=mysqli_query($conn,$sql);
  
  while($row = mysqli_fetch_array($result)) {
    $db_ts = $row['ts'];
    $db_time = Carbon::createFromTimeStamp($row['ts']);
    if($db_ts > $end_day_ts) { // db entry is in the future so finish
      break;
    } else {
      $db_state = $row['state'];
      $db_hour = $db_time->format('G');
    }
    if($db_ts >= $curr_ts) { // db timestamp is newer or same than current ts
      $curr_state=$db_state;
      $curr_hour=$db_hour;
      $hour_arr[$curr_hour][0]=$curr_state;
      $hour_arr[$curr_hour][1]=1;
    } else { // db timestamp is earlier than current ts
      $curr_state = $db_state;
      $hour_arr[$curr_hour][0]=$curr_state;
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
  
  $curr_state=0;
  $html="";
  $html.="<tr>";
  $html.="<th style='text-align:right;'>$name</th>";
  for($i=0;$i<24;$i++) {
    $border='';
    if(strlen($hour_arr[$i][0])>0) {
      $curr_state=$hour_arr[$i][0];
    }
    if($hour_arr[$i][1]==1) {
      $border="border:1px solid purple;";
    }
    if($curr_state==1) {
      $background="background-color:#EF9C47;";
    } else if($curr_state==0) {
      $background="background-color:#FDE0C1;";
    }  
    $curr_ts_utc=$date->copy()->startOfDay()->addHours($i)->format('U');
    $html.="<td style='text-align:center;font-size:11px;width:40px;cursor:pointer;$background".$border."' ";
    $html.="onclick='location.href=\"events/add_state.php?id=$id&state_type_id=$state_type_id&location_id=$location_id&ts=$curr_ts_utc"."&start_date=".$start_date_param."&size=".$size."\"'>";
    $html.=sprintf("%1$02d",$i);
    $html.="</td>";
  }
  $html.="</tr>";
  return $html;
}
?>
