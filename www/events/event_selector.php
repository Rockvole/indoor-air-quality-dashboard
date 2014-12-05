<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;

  $curr_date=Carbon::createFromDate($year,$month,$day_param, $user_timezone);
  $curr_date_start_utc=$curr_date->startOfDay()->format('U');
  $curr_date_end_utc=$curr_date->endOfDay()->format('U');

  $result=mysqli_query($conn,"SELECT * from events WHERE core_id=$id and ts>=$curr_date_start_utc and ts<=$curr_date_end_utc order by ts");
  if(mysql_errno()) {
    exit('Error: '.mysqli_error($conn));
  }
  $td_style="text-align:left;";
    
  echo "<table border=0>";
  echo "<tr><td style='$td_style'>";
  echo "<h4>".$ts_carbon->format('l, F jS Y')."</h4>";
  echo "</td></tr>";
  echo "<tr><td style='$td_style'>";
  echo "<input type='radio' name='event' onchange=\"select_event('day','Entire Day',-1);\" checked=\"checked\">";
  echo "Entire Day";
  echo "</td></tr>";
  while($row = mysqli_fetch_array($result)) {
    echo "<tr>";      
    $ts_carbon = Carbon::createFromTimeStamp($row['ts']);	
    echo "<td style='$td_style'>";
    echo "<input type='radio' name='event' onchange=\"select_event('event','".$row['name']."',".$row['id'].");\">";
    echo "<b>Event: </b>".$row['name'];
    echo "</td>";
    $found_event=true;
    echo "</tr>";
  }
  echo "<tr><td style='$td_style'>";
  echo "<input type='button' value='Show Graph' onclick='document.graph.submit();' style='padding:5px;'/>";
  echo "</td></tr>";
  echo "</table>";

?>
