<head>
  <title>Calendar</title>
  <link rel="stylesheet" href="html/stylesheet.css" type="text/css" >
  <link rel="stylesheet" href="calendar/calendar.css" type="text/css" />
</head>
  <script>
    function change_year(year) {
      document.cal.year.value=year;
      document.cal.submit();
    }
    function change_sensor(sensor) {
      document.cal.sensor.value=sensor;
      document.cal.submit();
    }
  </script>
<body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
require('calendar/calendar.php');
include 'globals.php';

$sensor = htmlspecialchars($_GET["sensor"]);
switch($sensor) {
  case 1: // Dust
    $title_name="Dust";
    $sensor_column="dust";
    $default_sensor_1="checked='checked'";
    $min_orange=1000;
    $min_red=2000;
    break;       
  case 2: // Sewer
    $title_name="Sewer";
    $sensor_column="sewer";
    $default_sensor_2="checked='checked'";
    $min_orange=500;
    $min_red=1000;
    break;  
  case 3: // Formaldehyde
    $title_name="Formaldehyde";
    $sensor_column="hcho";
    $default_sensor_3="checked='checked'";
    $min_orange=5;
    $min_red=10;
    break;   
  default: // Humidity
    $title_name="Humidity";
    $sensor_column="humidity";
    $default_sensor_0="checked='checked'";
    $min_orange=40;
    $min_red=60;
    break;    
}

// get the year from the query string and sanitize it
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);

$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
if(strlen($year)<=0) {
  $result=mysqli_query($conn,"SELECT MAX(ts) as ts from readings WHERE core_id=$id");
  if(mysql_errno()) {
    exit('Error: '.mysqli_error($conn));
  }  
  $row = mysqli_fetch_array($result);
  if(!isset($row['ts'])) {
    echo "No records found";
  } else {
    $date = Carbon::createFromTimeStamp($row['ts']);
    $year=$date->format('Y');
  }
}

$calendar    = new calendar();
$currentYear = $calendar->year($year);
  
  echo "<table border=0 width='100%'>";
  echo "<tr><td colspan=5>";
  echo "<table border=0>";
  echo "<td><h2>$title_name Calendar</h2></td>";
  $name_result=mysqli_query($conn,"SELECT name from cores WHERE id=$id");
  $name_row = mysqli_fetch_array($name_result);
  $name=$name_row['name'];  
  echo "<td>";
  echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>$name</span>";
  echo "</td>";
  echo "<td>";
  echo "<input type='radio' onclick='change_sensor(0);' $default_sensor_0>Humidity";
  echo "</td>";
  echo "<td>";
  echo "<input type='radio' onclick='change_sensor(1);' $default_sensor_1>Dust";
  echo "</td>";  
  echo "<td>";
  echo "<input type='radio' onclick='change_sensor(2);' $default_sensor_2>Sewer";
  echo "</td>";
  echo "<td>";
  echo "<input type='radio' onclick='change_sensor(3);' $default_sensor_3>Formaldehyde";
  echo "</td>";  
  echo "</table>";
  echo "</td><tr>";
  echo "<td width='100'></td>";
  echo "<td align='right'><input type='button' value='&lt; Previous' style='padding:2px;' onclick='change_year(\"".$currentYear->prev()->int()."\")'></td>";
  echo "<td width='300' align=center ><b>".$currentYear->name()."</b></td>";
  echo "<td><input type='button' value='Next    &gt;' style='padding:2px;' onclick='change_year(\"".$currentYear->next()->int()."\")'></td>";
  echo "<td width='100' align=right>";
  echo "</td>";
  echo "</tr>";
  echo "<tr><td>&nbsp;</td></tr>";
  echo "</table>";

echo "<section class='year'>\n";
  
echo "<ul>\n";
foreach($currentYear->months() as $month): 
    echo "<li>\n";
    echo "<h2>".$month->name()."</h2>\n";
    echo "<table>\n";
    echo "<tr>\n";
    foreach($month->weeks()->first()->days() as $weekDay):
        echo "  <th>". $weekDay->shortname()."</th>\n";
    endforeach;
    echo "</tr>\n";
    foreach($month->weeks(6) as $week):
        echo"<tr>\n";
        foreach($week->days() as $day):
	    if($day->month() != $month) {
	      echo "  <td class='inactive'>".$day->int();
	    } else {
	      $curr_date=Carbon::createFromDate($day->year()->int(),$day->month()->int(),$day->int()); 
	      $curr_date_start_utc=$curr_date->startOfDay()->format('U');
	      $curr_date_end_utc=$curr_date->endOfDay()->format('U');
              $result=mysqli_query($conn,"SELECT MAX($sensor_column) as mx from readings WHERE core_id=$id and ts>=$curr_date_start_utc and ts<=$curr_date_end_utc");
	      if(mysql_errno()) {
	        exit('Error: '.mysqli_error($conn));
              }
	      $row = mysqli_fetch_array($result);
	      $max=$row['mx'];  
              if(strlen($max)<=0) {
	        error_log("no rows found");
		echo "  <td style='color:white;background:lightgrey;'>";
              }	else {    
                if($max>=$min_red) echo "  <td style='background:#FDB9A9;'>";
		  else if($max>=$min_orange) echo "  <td style='background:#FDDAA9;'>";
		    else echo "  <td style='background:#A9FEB6;'>";
		echo "<a href='dashboard.php?id=$id&start_date=".$curr_date->format($param_date_format)."' style='color:black;'>";
	      }
	      echo ($day->isToday()) ? "<strong style='color:red;'>" . $day->int() . "</strong>" : $day->int();
	      echo "</a>";
	    }
	    echo "</td>\n";
        endforeach;
        echo "</tr>\n";
    endforeach;
    echo "</table>\n";
    echo "</li>\n";
    endforeach;
  echo "</ul>\n";
echo "</section>\n";

// ------------------------------------------------------------------- Form
echo "<form action='year_cal.php' method='get' name='cal'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='year' value='$year'>";
echo "<input type='hidden' name='sensor' value='$sensor'>";
echo "</form>";

echo "</body>\n";
echo "</html>\n";

?>
