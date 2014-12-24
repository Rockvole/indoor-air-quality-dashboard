<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;

$loc_back_state=0;
$color_toggle=0;
$event_arr = array_fill(0, 24, array_fill(0, 2, ''));

// --------------------------------------------------------------------- EVENTS
$result=mysqli_query($conn,"SELECT * from events where ts>=$start_day_utc and ts<$end_day_utc and core_id=$id order by ts");
while($row = mysqli_fetch_array($result)) {
  $event_ts = Carbon::createFromTimeStamp($row['ts']);
  $curr_hour = $event_ts->format('G');
  $event_arr[$curr_hour][0] = $row['name'];
}
// Find events before today
$result=mysqli_query($conn,"SELECT name from events where ts=".
                            "(SELECT max(ts) from events where ts<$start_day_utc and core_id=$id)");
$row = mysqli_fetch_array($result);
if(isset($row['name'])) {
  if(!is_null($event_arr[0][0]))  $event_arr[0][0]=$row['name']." (previous)";
}
cell_colors($event_arr);

$loc_arr = get_day_locations();
cell_colors($loc_arr);

echo "<hr noshade style='background-color:purple;border: 1px solid purple;border-radius: 7px 7px 7px 7px;clear:left;'/>";

echo draw_timetable(true);
echo draw_timetable(false);

// --------------------------------------------------------------------- FUNCTIONS
function draw_timetable($is_location) {
  global $date;
  
  if($is_location) $title="Location";
    else $title="Events";
  $html="";
  $html.="<div class='container'>";  
  $html.="<table border=0>";
  
  $html.="<tr>";
  $html.="<td colspan=6><h3 style='text-align:center;'>$title</h3></td>";
  $html.="</tr>";   
  $html.="<tr>";
  $html.="<td><img src='images/transparent.gif' width='40' height='1'></td>";
  $html.="<td><img src='images/transparent.gif' width='150' height='1'></td>";
  $html.="<td><img src='images/transparent.gif' width='40' height='1'></td>";
  $html.="<td><img src='images/transparent.gif' width='150' height='1'></td>";  
  $html.="<td><img src='images/transparent.gif' width='40' height='1'></td>";
  $html.="<td><img src='images/transparent.gif' width='150' height='1'></td>";  
  $html.="</tr>";  
  
  for($inner_loop=0;$inner_loop<8;$inner_loop++)
  {
    $html.="<tr>";
    for($column_loop=0;$column_loop<3;$column_loop++) 
    {
      $curr_hour=$inner_loop+($column_loop*8);
      $curr_ts_utc=$date->copy()->startOfDay()->addHours($curr_hour)->format('U');


      $html.="<td style='text-align:right;font-size:11px;'>";
      $html.=sprintf("%1$02d:00&nbsp;",$curr_hour);
      $html.="</td>";
      
      if($is_location)
        $html.=location_cell($curr_hour,$curr_ts_utc,$start_date_param,$size);
      else $html.=event_cell($curr_hour,$curr_ts_utc,$start_date_param,$size);
      
    } // $column_loop
    $html.="</tr>";
  } // $inner_loop

  $html.="</table>";
  $html.="</div>";  
  return $html;
}

function location_cell($curr_hour,$curr_ts_utc,$start_date_param,$size) {
  global $id;
  global $loc_arr;
  

  $background='background-color:#FFFCEB;';
  if($loc_arr[$curr_hour][1]==1) {
    $background='background-color:#F9F1B0;';
  } else if($loc_arr[$curr_hour][1]==2) {
    $background='background-color:#FFEB44;';
  }    
  $html ="";
  $html.="<td style='text-align:left;cursor:pointer;border:1px solid purple;$background' ";
  $html.="onclick='location.href=\"events/manage_location.php?id=$id&ts=$curr_ts_utc"."&start_date=".$start_date_param."&size=".$size."\"'>";        
  $html.=$loc_arr[$curr_hour][0];
  $html.="&nbsp;";  
  $html.="</td>";  
  return $html;
}

function event_cell($curr_hour,$curr_ts_utc,$start_date_param,$size) {
  global $id;
  global $event_arr;
  
  $background='background-color:#F0F7FD;';
  if($event_arr[$curr_hour][1]==1) {
    $background='background-color:#A9D0F8;';
  } else if($event_arr[$curr_hour][1]==2) {
    $background='background-color:#2FB3F8;';
  }    
  $html ="";
  $html.="<td style='text-align:left;cursor:pointer;border:1px solid purple;$background' ";
  $html.="onclick='location.href=\"events/manage_event.php?id=$id&ts=$curr_ts_utc"."&start_date=".$start_date_param."&size=".$size."\"'>";        
  $html.=$event_arr[$curr_hour][0];
  $html.="&nbsp;";
  //$html.="&nbsp;".$curr_hour."||ts=".$curr_ts_utc."||ce=".$curr_event."||ea=".$event_arr[$curr_hour][0]."||pe=".$prev_event."||in=".is_null($curr_event)."||sl=".strlen($curr_event);  
  $html.="</td>";  
  return $html;
}

function cell_colors(&$event_arr) {
  for($curr_hour=0;$curr_hour<24;$curr_hour++) {
    
    $prev_event=$event_arr[$curr_hour-1][0];
    $curr_event=$event_arr[$curr_hour][0];    
    if(is_null($curr_event)) { // Current event is NULL = Close Event 
      $event_arr[$curr_hour][1]=0;
    } else {
      if(strlen($curr_event)==0) {  
        $event_arr[$curr_hour][1]=$event_arr[$curr_hour-1][1];
      } else {
        if(is_null($prev_event)) {
          $event_arr[$curr_hour][1]=1;
        } else {
          if($event_arr[$curr_hour-1][1]==1) {
  	    $event_arr[$curr_hour][1]=2;
          } else {
	    $event_arr[$curr_hour][1]=1;
          }
        }
      }
    }   
    //error_log("event_arr[$curr_hour][0]=".$event_arr[$curr_hour][0]." event_arr[$curr_hour][1]=".$event_arr[$curr_hour][1]); 
  }
}

function get_day_locations() {
  global $conn;
  global $start_day_utc;
  global $end_day_utc;
  global $id;

  // ------------------------------------------------------------------- Fill positions array
  $pos_arr = array_fill(0, 24, '');  
  $result=mysqli_query($conn,"SELECT * from locations WHERE ts>=$start_day_utc AND ts<$end_day_utc AND type=2 AND core_id=$id order by ts");
  while($row = mysqli_fetch_array($result)) {
    $event_ts = Carbon::createFromTimeStamp($row['ts']);
    $curr_hour = $event_ts->format('G');
    $pos_name = $row['name'];
    $pos_arr[$curr_hour] = $pos_name;
  }
  // Check first hour is filled
  if(strlen($pos_arr[0])==0) { // First hour has no position so retrieve last one
    $pos_result=mysqli_query($conn,"SELECT name from locations where ts=".
                                   "  (SELECT max(ts) from locations where ts<$start_day_utc AND type=2 AND core_id=$id) AND type=2");
    $pos_row = mysqli_fetch_array($pos_result);
    $pos_arr[0] = $pos_row['name'];
  }  
  
  // ------------------------------------------------------------------- Fill rooms array
  $room_arr = array_fill(0, 24, '');  
  $result=mysqli_query($conn,"SELECT * from locations where ts>=$start_day_utc AND ts<$end_day_utc AND type=1 AND core_id=$id order by ts");
  while($row = mysqli_fetch_array($result)) {
    $event_ts = Carbon::createFromTimeStamp($row['ts']);
    $curr_hour = $event_ts->format('G');
    $room_name = $row['name'];
    $room_arr[$curr_hour] = $room_name;
  }
  // Check first hour is filled  
  if((strlen($room_arr[0])==0)) { // First hour has no room so retrieve last one
    $room_result=mysqli_query($conn,"SELECT name from locations where ts=".
                                    "  (SELECT max(ts) from locations where ts<$start_day_utc AND type=1 AND core_id=$id) AND type=1");
    $room_row = mysqli_fetch_array($room_result);
    $room_arr[0] = $room_row['name'];
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
