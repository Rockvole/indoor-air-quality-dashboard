<html>
<head>
  <title>Add Location</title>
  <link rel="stylesheet" href="html/stylesheet.css" type="text/css" >
  <link rel="stylesheet" href="calendar/calendar.css" type="text/css" />
</head>
  <script>
    function change_month(year,month) {
      document.cal.year.value=year;
      document.cal.month.value=month;
      document.cal.hour.value=document.getElementById('hour').value;      
      document.cal.location_name.value=document.getElementById('location_name').value;
      document.cal.room_name.value=document.getElementById('room_name').value;
      document.cal.submit();
    }
    function add_location(start_date) {
      document.cal.add.value='true';
      document.cal.start_date.value=start_date;
      document.cal.hour.value=document.getElementById('hour').value;
      document.cal.location_name.value=document.getElementById('location_name').value;
      document.cal.room_name.value=document.getElementById('room_name').value;
      document.cal.submit();      
    }
    function delete_location(ts) {
      document.cal.ts.value=ts;
      document.cal.submit();      
    }    
    function back_button() {
      document.back.submit();
    }
  </script>
<body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
require('calendar/calendar.php');
include 'globals.php';

$conn=mysqli_connect("", "", "", $db_name);

// Check connection
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 
if(!isset($_GET["id"])) exit("Must specify id parameter");
$id = htmlspecialchars($_GET["id"]);
$add_param = htmlspecialchars($_GET["add"]);
$ts = htmlspecialchars($_GET["ts"]);
$year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
$hour = htmlspecialchars($_GET["hour"]);
$loc_name = htmlspecialchars($_GET["location_name"]);
$room_name = htmlspecialchars($_GET["room_name"]);
$start_date_param = htmlspecialchars($_GET["start_date"]);
$size_param = htmlspecialchars($_GET["size"]);
if(strlen($ts)>0) {
  
  $sql = "DELETE from locations where ts=$ts";
  if(!mysqli_query($conn,$sql)) {
     exit('Error: '.mysqli_error($conn));
  }
  echo "<div class='alerthead'>Item deleted</div>";
} else if(strlen($add_param)>0) {
  if(strlen($room_name)<=0) {
    exit("Room must be specified");
  }
  $dt = Carbon::createFromFormat($param_date_format, $start_date_param);
  $dt->startOfDay()->addHours($hour);
  $dt_utc = $dt->format('U');
  $sql_loc = !empty($loc_name) ? "'$loc_name'" : "NULL";
  $sql = "INSERT into locations (location_name, room_name, core_id, ts) VALUES ($sql_loc, '$room_name', '$id', '$dt_utc')";
  if(!mysqli_query($conn,$sql)) {
     exit('Error: '.mysqli_error($conn));
  }
  echo "<div class='alerthead'>".$room_name." added</div>";
}

// initialize the calendar object
$calendar = new calendar();

// get the current month object by year and number of month
$currentMonth = $calendar->month($year, $month);

// get the previous and next month for pagination
$prevMonth = $currentMonth->prev();
$nextMonth = $currentMonth->next();
  
echo "<table border=0 class='form_table'>";
echo "<tr>";
echo "<td colspan=2><h2>Add Location</h2></td>";
echo "<td align=right><input type='button' value='Back' style='padding:2px;' onclick='back_button()'></td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><b>Please choose the location of your sensor :</b></td>";
echo "<td width=100%></td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right>Location:</th>";
echo "<td><input type='text' name='location_name' maxlength=40 size=40 id='location_name' value='$loc_name'></td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Bob's House<br/>(Leave blank if sensor will not change location)</td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right style='vertical-align:top'>Room:</th>";
echo "<td style='vertical-align:top'><input type='text' name='room_name' maxlength=60 size=40 id='room_name' value='$room_name'></td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Living Room<br/><br/>";
echo "Include more detailed info if needed.<br/>";
echo "e.g. Master Bedroom (On window-sill)";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right>Hour:</th>";
echo "<td>";
echo "<select name='hour' id='hour'>";
for($i=0;$i<24;$i++) {
  $sel="";
  if($i==$hour) $sel="selected='selected'";
  echo "<option value='$i' $sel>$i</option>";
}
echo "</select>";
echo "</td>";
echo "<td style='font-size:110%;font-style:italic;'>The hour when the sensor was moved</td>";
echo "</tr>";
echo "<tr><td>&nbsp;</td>";

echo "<td colspan=2>";
echo "<table border=0 style='width:240px'>";
echo "<tr>";
echo "<td><input type='button' value='&lt; Previous' style='padding:2px;' onclick='change_month(\"".$prevMonth->year()->int()."\",\"".$prevMonth->int()."\")'></td>";
echo "<td width='300' align=center ><b>".$currentMonth->year()->name()."</b></td>";
echo "<td align=right><input type='button' value='Next    &gt;' style='padding:2px;' onclick='change_month(\"".$nextMonth->year()->int()."\",\"".$nextMonth->int()."\")'></td>";
echo "</tr>";
echo "</table>";

echo "<section class='year' style='background:white;'>\n";

echo "<ul>\n";
echo "<li>\n";
echo "<h2>".$currentMonth->name()."</h2>\n";
    
echo "<table border=0 >";
echo "<tr>";
  foreach($currentMonth->weeks()->first()->days() as $weekDay): 
    echo "<th>".$weekDay->shortname()."</th>";
  endforeach; 
  echo "</tr>";
  foreach($currentMonth->weeks(6) as $week): 
  echo "<tr>";
  foreach($week->days() as $day): 
    if($day->month() != $currentMonth) {
      echo "<td class='inactive'>".$day->int()."</td>";
    } else {
      $curr_date=Carbon::createFromDate($day->year()->int(),$day->month()->int(),$day->int());
      echo "<td onclick='add_location(\"".$curr_date->format($param_date_format)."\");' style='cursor:pointer;'>";
      echo ($day->isToday()) ? "<strong style='color:red;'>" . $day->int() . "</strong>" : $day->int();
      echo "</td>";
    }
  endforeach;
  echo "</tr>";
  endforeach;
echo "</table>";
echo "</li>";
echo "</ul>";
echo "</section>\n";
echo "</td>";
echo "</tr>";
echo "<tr><td></td><td colspan=2>";
echo "<table border=0 style='border-spacing:6px;'>";
echo "<tr><th style='text-align:left;'>Location</th><th style='text-align:left;'>Room</th>";
echo "<th style='text-align:left;'>Date</th><th style='text-align:left;'>Delete</th></td>";
$result=mysqli_query($conn,"SELECT * from locations where core_id=$id order by ts asc");
while($row = mysqli_fetch_array($result)) {
  if(strlen($row['location_name'])>0) $location=$row['location_name'];
  echo "<tr>";
  $date = Carbon::createFromTimeStamp($row['ts']);
  echo "<td nowrap>$location</td>\n";
  echo "<td nowrap>" . $row['room_name'] . "</td>";
  echo "<td nowrap>".$date->format($user_date_format)."</td>\n";
  echo "<td width=100%>&nbsp;&nbsp;<img src='html/delete.gif' onclick='delete_location(".$row['ts'].");' style='cursor:pointer;'></td>\n";
  echo "</tr>";
}
echo "</table>";
echo "</td></tr>";
echo "</table>";  

// ------------------------------------------------------------------- Form
echo "<form action='add_location.php' method='get' name='cal'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='add' value=''>";
echo "<input type='hidden' name='year' value='$year'>";
echo "<input type='hidden' name='month' value='$month'>";
echo "<input type='hidden' name='hour' value='$hour'>";
echo "<input type='hidden' name='location_name' value='$location_name'>";
echo "<input type='hidden' name='room_name' value='$room_name'>";
echo "<input type='hidden' name='size' value='$size_param'>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "<input type='hidden' name='ts' value=''>";
echo "</form>";
// ------------------------------------------------------------------- Back
echo "<form action='dashboard.php' method='get' name='back'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='size' value='$size_param'>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "</form>";

echo "</body>\n";
echo "</html>\n";
mysqli_close($conn);  
?>    

