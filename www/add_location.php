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
      document.cal.name.value=document.getElementById('loc_name').value;
      document.cal.submit();
    }
    function add_location(start_date) {
      document.cal.start_date.value=start_date;
      document.cal.name.value=document.getElementById('loc_name').value;
      document.cal.submit();      
    }
    function delete_location(ts) {
      document.cal.ts.value=ts;
      document.cal.submit();      
    }    
  </script>
<body>
  <h2>Add Location</h2>
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
$ts = htmlspecialchars($_GET["ts"]);
$year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
$name = htmlspecialchars($_GET["name"]);
$start_date_param = htmlspecialchars($_GET["start_date"]);
if(strlen($ts)>0) {
  
  $sql = "DELETE from locations where ts=$ts";
  if(!mysqli_query($conn,$sql)) {
     exit('Error: '.mysqli_error($conn));
  }
  echo "<div class='alerthead'>Item deleted</div>";
} else if(strlen($start_date_param)>0) {
  if(strlen($name)<=0) {
    exit("Name must be specified");
  }
  $dt = Carbon::createFromFormat($param_date_format, $start_date_param);
  $dt_utc = $dt->startOfDay()->format('U');
  
  $sql = "INSERT into locations (name, core_id, ts) VALUES ('$name', '$id', '$dt_utc')";
  if(!mysqli_query($conn,$sql)) {
     exit('Error: '.mysqli_error($conn));
  }
  echo "<div class='alerthead'>".$name." added</div>";
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
echo "<td></td>";
echo "<td><b>Please choose the location of your sensor.<br/>Examples : Living Room, Basement Bathroom</b></td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right>Name:</th>";
echo "<td><input type='text' name='name' size=40 id='loc_name' value='$name'></td>";
echo "</tr>";
echo "<tr><td>&nbsp;</td><td>";
echo "<table border=0 style='width:240px'>";
echo "<tr>";
echo "<td><input type='button' value='&lt; Previous' style='padding:2px;' onclick='change_month(\"".$prevMonth->year()->int()."\",\"".$prevMonth->int()."\")'></td>";
echo "<td width='300' align=center ><b>".$currentMonth->year()->name()."</b></td>";
echo "<td align=right><input type='button' value='Next    &gt;' style='padding:2px;' onclick='change_month(\"".$nextMonth->year()->int()."\",\"".$nextMonth->int()."\")'></td>";
echo "</tr>";
echo "</table>";

echo "<section class='year'>\n";

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

$result=mysqli_query($conn,"SELECT * from locations where core_id=$id order by ts desc");
while($row = mysqli_fetch_array($result)) {
  echo "<tr><td></td>";
  $date = Carbon::createFromTimeStamp($row['ts']);
  echo "<td style='vertical-align:middle'>" . $row['name'] . " @ ".$date->format($param_date_format);
  echo "&nbsp;&nbsp;<img src='html/delete.gif' onclick='delete_location(".$row['ts'].");' style='cursor:pointer;'>";
  echo "</td>\n";
  echo "</tr>";
}
echo "</table>";  

// ------------------------------------------------------------------- Form
echo "<form action='add_location.php' method='get' name='cal'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='year' value='$year'>";
echo "<input type='hidden' name='month' value='$month'>";
echo "<input type='hidden' name='name' value='$name'>";
echo "<input type='hidden' name='start_date' value=''>";
echo "<input type='hidden' name='ts' value=''>";
echo "</form>";

echo "</body>\n";
echo "</html>\n";
mysqli_close($conn);  
?>    

