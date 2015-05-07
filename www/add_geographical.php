<html>
<head>
  <title>Move Geographical Location</title>
  <link rel="stylesheet" href="html/stylesheet.css" type="text/css" >
  <link rel="stylesheet" href="calendar/calendar.css" type="text/css" />
</head>
  <script>
    function change_month(year,month) {
      document.cal.year.value=year;
      document.cal.month.value=month;
      document.cal.hour.value=document.getElementById('hour').value;      
      document.cal.name.value=document.getElementById('name').value;
      document.cal.submit();
    }
    function add_location(start_date) {
      document.cal.add.value='true';
      document.cal.start_date.value=start_date;
      document.cal.hour.value=document.getElementById('hour').value;
      document.cal.name.value=document.getElementById('name').value;
      document.cal.submit();      
    }
    function delete_location(ts) {
      document.cal.ts.value=ts;
      document.cal.submit();      
    }    
    function home_button() {
      document.home.submit();
    }
  </script>
<body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
require('calendar/calendar.php');
include 'globals.php';

$add_param = htmlspecialchars($_GET["add"]);
$ts = htmlspecialchars($_GET["ts"]);
$year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
$hour = htmlspecialchars($_GET["hour"]);
$name = htmlspecialchars($_GET["name"]);
$start_date_param = htmlspecialchars($_GET["start_date"]);
$size_param = htmlspecialchars($_GET["size"]);
if(strlen($ts)>0) {
  
  $sql = "DELETE from geographical where ts=$ts AND group_id=$id";
  if(!mysqli_query($conn,$sql)) {
     exit('Error: '.mysqli_error($conn));
  }
  echo "<div class='alerthead'>Item deleted</div>";
} else if(strlen($add_param)>0) {
  if(strlen($name)<=0) {
    exit("Location must be specified");
  }
  $dt = Carbon::createFromFormat($param_date_format, $start_date_param);
  $dt->startOfDay()->addHours($hour);
  $dt_utc = $dt->format('U');
  $sql = "INSERT into geographical (name, group_id, zoom_temp_hum, zoom_dust, zoom_sewer, zoom_hcho, ts) ".
         "VALUES ('$name', '$id', 0, 0, 0, 0, '$dt_utc')";
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
echo "<td colspan=2><h2>Move Geographical Location</h2></td>";
echo "<td align=right><img src='images/home.png' onclick='home_button();' height=30 width=30 style='cursor:pointer;'></td>\n";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><b>Please choose the location of your sensor :</b></td>";
echo "<td width=100%></td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right>Location:</th>";
echo "<td><input type='text' name='name' maxlength=40 size=40 id='name' value='$name'></td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Bob's House</td>";
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
echo "<tr><th style='text-align:left;'>Name</th>";
echo "<th style='text-align:left;'>Date</th><th style='text-align:left;'>Delete</th></td>";
$result=mysqli_query($conn,"SELECT * from geographical where group_id=$id order by ts asc");
while($row = mysqli_fetch_array($result)) {
  if(strlen($row['name'])>0) $name=$row['name'];
  echo "<tr>";
  $date = Carbon::createFromTimeStamp($row['ts']);
  echo "<td nowrap>$name</td>\n";
  echo "<td nowrap>".$date->format($user_date_format)."</td>\n";
  echo "<td width=100%>&nbsp;&nbsp;<img src='images/delete.gif' onclick='delete_location(".$row['ts'].");' style='cursor:pointer;'></td>\n";
  echo "</tr>";
}
echo "</table>";
echo "</td></tr>";
echo "</table>";  

// ------------------------------------------------------------------- Form
echo "<form action='add_geographical.php' method='get' name='cal'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='add' value=''>";
echo "<input type='hidden' name='year' value='$year'>";
echo "<input type='hidden' name='month' value='$month'>";
echo "<input type='hidden' name='hour' value='$hour'>";
echo "<input type='hidden' name='name' value='$name'>";
echo "<input type='hidden' name='size' value='$size_param'>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "<input type='hidden' name='ts' value=''>";
echo "</form>";
// ------------------------------------------------------------------- Home
echo "<form action='index.php' method='get' name='home'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='size' value='$size_param'>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "</form>";

echo "</body>\n";
echo "</html>\n";
mysqli_close($conn);  
?>    

