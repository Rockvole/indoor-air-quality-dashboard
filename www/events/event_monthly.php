<html>
<head>
  <title>Add Location</title>
  <link rel="stylesheet" href="../html/stylesheet.css" type="text/css" >
  <link rel="stylesheet" href="../calendar/calendar.css" type="text/css" />
</head>
  <script>
    function change_month(year,month) {
      document.cal.year.value=year;
      document.cal.month.value=month;    
      document.cal.submit();
    }
    function draw_charts(start_date) {
      document.cal.add.value='true';
      document.cal.start_date.value=start_date;
      document.cal.hour.value=document.getElementById('hour').value;
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
require('../calendar/calendar.php');
include '../globals.php';

$conn=mysqli_connect("", "", "", $db_name);

// Check connection
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 
if(!isset($_GET["id"])) exit("Must specify id parameter");
$id = htmlspecialchars($_GET["id"]);
$year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);

// initialize the calendar object
$calendar = new calendar();

// get the current month object by year and number of month
$currentMonth = $calendar->month($year, $month);

// get the previous and next month for pagination
$prevMonth = $currentMonth->prev();
$nextMonth = $currentMonth->next();
  
echo "<table border=0 class='form_table'>";
echo "<tr>";
echo "<td colspan=2><h2>Choose Event</h2></td>";
echo "<td align=right><input type='button' value='Back' style='padding:2px;' onclick='back_button()'></td>";
echo "</tr>";
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
      echo "<td onclick='alert(\"nothing\");' style='cursor:pointer;'>";
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
echo "</table>";  

// ------------------------------------------------------------------- Form
echo "<form action='event_monthly.php' method='get' name='cal'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='year' value='$year'>";
echo "<input type='hidden' name='month' value='$month'>";
echo "</form>";
// ------------------------------------------------------------------- Back
echo "<form action='../index.php' method='get' name='back'>";
echo "</form>";

echo "</body>\n";
echo "</html>\n";
mysqli_close($conn);  
?>    

