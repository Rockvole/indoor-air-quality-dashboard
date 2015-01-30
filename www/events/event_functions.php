<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;

function get_day_locations() {
  global $conn;
  global $start_day_utc;
  global $end_day_utc;
  global $id;
  global $location_id;

  // ------------------------------------------------------------------- Fill positions array
  $pos_arr = array_fill(0, 24, '');  
  $result=mysqli_query($conn,"SELECT * from locations WHERE ts>=$start_day_utc AND ts<$end_day_utc AND type=2 AND group_id=$id order by ts");
  while($row = mysqli_fetch_array($result)) {
    $event_ts = Carbon::createFromTimeStamp($row['ts']);
    $curr_hour = $event_ts->format('G');
    $pos_name = $row['name'];
    $pos_arr[$curr_hour] = $pos_name;
  }
  // Check first hour is filled
  if(strlen($pos_arr[0])==0) { // First hour has no position so retrieve last one
    $pos_result=mysqli_query($conn,"SELECT name from locations where ts=".
                                   "  (SELECT max(ts) from locations where ts<$start_day_utc AND type=2 AND group_id=$id) AND type=2 AND group_id=$id");
    $pos_row = mysqli_fetch_array($pos_result);
    $pos_arr[0] = $pos_row['name'];
  }  
  
  // ------------------------------------------------------------------- Fill rooms array
  $room_arr = array_fill(0, 24, '');  
  $result=mysqli_query($conn,"SELECT * from locations where ts>=$start_day_utc AND ts<$end_day_utc AND type=1 AND group_id=$id order by ts");
  while($row = mysqli_fetch_array($result)) {
    $event_ts = Carbon::createFromTimeStamp($row['ts']);
    $curr_hour = $event_ts->format('G');
    $room_name = $row['name'];
    $room_arr[$curr_hour] = $room_name;
    if($curr_hour==0) $location_id=$row['id'];
  }
  // Check first hour is filled  
  if((strlen($room_arr[0])==0)) { // First hour has no room so retrieve last one
    $room_result=mysqli_query($conn,"SELECT * from locations where ts=".
                                    "  (SELECT max(ts) from locations where ts<$start_day_utc AND type=1 AND group_id=$id) AND type=1 AND group_id=$id");
    $room_row = mysqli_fetch_array($room_result);
    $room_arr[0] = $room_row['name'];
    $location_id = $room_row['id'];    
  }  
  
  // Copy rooms and positions into location array
  $loc_arr = array_fill(0, 24, array_fill(0, 2, ''));  
  for($curr_hour=0;$curr_hour<24;$curr_hour++) {
    if(strlen($room_arr[$curr_hour])>0) {
      if(strlen($pos_arr[$curr_hour])>0) {
        $loc_arr[$curr_hour][0] = $room_arr[$curr_hour]." (".$pos_arr[$curr_hour].")";
      } else $loc_arr[$curr_hour][0] = $room_arr[$curr_hour];
      $prev_room=$room_arr[$curr_hour];
      $prev_pos=$pos_arr[$curr_hour];
    } else {
      if(strlen($pos_arr[$curr_hour])>0) {
        $loc_arr[$curr_hour][0] = $prev_room." (".$pos_arr[$curr_hour].")";
      }
    }
  }
  return $loc_arr;
}
?>
