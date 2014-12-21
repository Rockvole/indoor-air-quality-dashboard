<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;

$loc_back_state=0;
$color_toggle=0;
$event_arr = array_fill(0, 24, array_fill(0, 2, ''));
$loc_arr = array_fill(0, 24, array_fill(0, 2, ''));

$result=mysqli_query($conn,"SELECT * from events where ts>=$start_day_utc and ts<$end_day_utc and core_id=$id order by ts");
while($row = mysqli_fetch_array($result)) {
  $event_ts = Carbon::createFromTimeStamp($row['ts']);
  $curr_hour = $event_ts->format('G');
  if($row['event_type']==1) {
    $event_arr[$curr_hour][0] = $row['name'];
  } else $loc_arr[$curr_hour][0] = $row['name'];
}
// Find events before today
$result=mysqli_query($conn,"SELECT name from events where ts=".
                            "(SELECT max(ts) from events where ts<$start_day_utc and core_id=$id and event_type=1)");
$row = mysqli_fetch_array($result);
if(isset($row['name'])) {
  if(!is_null($event_arr[0][0]))  $event_arr[0][0]=$row['name']." (previous)";
}
cell_colors($event_arr);

// Find locations before today
$result=mysqli_query($conn,"SELECT name from events where ts=".
                            "(SELECT max(ts) from events where ts<$start_day_utc and core_id=$id and event_type=2)");
$row = mysqli_fetch_array($result);
if(isset($row['name'])) {
  if(!is_null($loc_arr[0][0]))  $loc_arr[0][0]=$row['name']." (previous)";
}
cell_colors($loc_arr);

$curr_hour=0;
echo "<table border=0 style='clear:left;'>";
echo "<tr>";
for($i=0;$i<3;$i++) {
  echo "<td><img src='images/transparent.gif' width='70' height='1'></td>";
  echo "<td><img src='images/transparent.gif' width='150' height='1'></td>";
  echo "<td><img src='images/transparent.gif' width='150' height='1'></td>";
}
echo "</tr>";
echo "<tr>";
for($i=0;$i<3;$i++)
  echo "<th>&nbsp;</th><th>Location</th><th>Event</th>";
echo "</tr>";  
for($outer_loop=0;$outer_loop<8;$outer_loop++) {
  echo "<tr>";
  for($inner_loop=0;$inner_loop<=16;$inner_loop=$inner_loop+8)
  {
    $curr_hour=$inner_loop+$outer_loop;
    $curr_ts_utc=$date->copy()->startOfDay()->addHours($curr_hour)->format('U');
    echo "<td width=50 style='text-align:right;font-size:11px;'>";
    echo sprintf("%1$02d:00&nbsp;",$curr_hour);
    echo "</td>";
          
    echo location_cell($curr_hour,$curr_ts_utc,$start_date_param,$size);
      
    echo event_cell($curr_hour,$curr_ts_utc,$start_date_param,$size);
  } // $inner_loop
  echo "</tr>";
} // $outer_loop
echo "</table>";

function location_cell($curr_hour,$curr_ts_utc,$start_date_param,$size) {
  global $id;
  global $loc_arr;
  
  $background='background-color:#CEE3F8;';
  if($loc_arr[$curr_hour][1]==1) {
    $background='background-color:#CC6666;';
  } else if($loc_arr[$curr_hour][1]==2) {
    $background='background-color:#3399CC;';
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
  
  $background='background-color:#FDF9DB;';
  if($event_arr[$curr_hour][1]==1) {
    $background='background-color:#F9B96E;';
  } else if($event_arr[$curr_hour][1]==2) {
    $background='background-color:#FBE212;';
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
    if(true) {
      error_log("event_arr[$curr_hour][0]=".$event_arr[$curr_hour][0]." event_arr[$curr_hour][1]=".$event_arr[$curr_hour][1]); 
    }
  }
}
?>
