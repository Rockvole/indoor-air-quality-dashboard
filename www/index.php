<html>
  <head>
    <title>Welcome Page</title>
    <link rel="stylesheet" type="text/css" href="html/stylesheet.css">    
    <style>
      th {text-align:right;}
      td {padding: 4px 14px 4px 14px}
    </style>
  </head>
  <script>
    function click_button(id, action) {
      if(action==null)
        document.aq.action = "year_cal.php";
      else document.aq.action = action;
      
      document.aq.id.value=id;
      document.aq.submit();
    }
  </script>
  <body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include 'globals.php';

$result=mysqli_query($conn,"SELECT * from groups");

echo "<table border=0 width='100%'><tr>";
echo "<td><h2>Sensor Groups</h2></td>";
echo "<td><input type='button' value='Add New Sensor Group' onclick='click_button(null,\"add_new_sensor.php\");'></td></tr>";
echo "</tr></table>\n";
echo "<br/>";

echo "<form action='year_cal.php' method='get' name='aq'>\n";
echo "<input type='hidden' name='id' value='0'>\n";

// Icons from thenounproject
echo "<table border=0 >\n";
echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td>";
echo "<th style='text-align:left;'>Last Reading</th></tr>";
while($row = mysqli_fetch_array($result)) {
  $result_group=mysqli_query($conn,"SELECT max(ts) as ts from readings where group_id=".$row['id']);
  echo "<tr>\n";
  echo "<th style='font-size:18px;'>".$row['name']."</th>\n";
  echo "<td><img src='images/calendar.png' onclick='click_button(".$row['id'].");' height=40 width=40 style='cursor:pointer;'></td>";
  echo "<td><img src='images/graph.png' onclick='click_button(".$row['id'].",\"dashboard.php\");' height=40 width=40 style='cursor:pointer;'></td>";
  echo "<td><img src='images/barchart.png' onclick='click_button(".$row['id'].",\"events/event_monthly.php\");' height=40 width=40 style='cursor:pointer;'></td>";
  echo "<td><img src='images/location.png' onclick='click_button(".$row['id'].",\"add_geographical.php\");' height=40 width=40 style='cursor:pointer;'></td>";  
  echo "<td><img src='images/download.png' onclick='click_button(".$row['id'].",\"transfer/download.php\");' height=40 width=40 style='cursor:pointer;'></td>";
  $name=get_sensor_type_name($row['id']);
  echo "<td>";
  $row_group=mysqli_fetch_array($result_group);
  if(!is_null($row_group['ts'])) {
    $curr_date=Carbon::createFromTimeStamp($row_group['ts']);
    $curr_date->setTimezone($user_timezone);
    echo $curr_date->format("F jS Y H:i")."<br/>(".$user_timezone.")";
  } else echo "&lt;NONE&gt;";
  echo "</td>";
  echo "<td>";
  if($sensor_count==1) {
    echo "(".$name.")";
  }
  echo "</td>";  
  echo "</tr>\n";

}
echo "</table>\n";
echo "</form>\n";
   
mysqli_close($conn);   
?>
  </body>  
</html>


