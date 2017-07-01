<head>
  <title>Calendar</title>
  <link rel="stylesheet" href="html/stylesheet.css" type="text/css" >
  <style>
	.ave-table {
		border-collapse: collapse;
	}
	.ave-td {
		vertical-align:top;
	}
    th {text-align:right; font-size:18px; color:blueviolet;padding: 4px 14px 4px 14px;}
    td {text-align:center; font-size:18px; padding: 4px 2px 4px 2px;}
  </style>
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
    function change_period(period) {
      document.cal.period.value=period;
      document.cal.submit();
    }
    function home_button() {
      document.cal.action = "index.php";
      document.cal.submit();
    }     
  </script>
<body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
require('calendar/calendar.php');
include 'globals.php';

$period = htmlspecialchars($_GET["period"]);
switch($period) {
  case 2: // Weekly
    $default_period_2="checked='checked'";
    break;
  default: // Daily
    $default_period_1="checked='checked'";  
    break;
}

if($sensor_type==0) {
  $sensor = htmlspecialchars($_GET["sensor"]);
} else $sensor=$sensor_type;

switch($sensor) {
  case 2: // Dust
    $title_name="Dust";
    $sensor_column="dust";
    $default_sensor_2="selected='selected'";
    $min_orange=$DUST_GOOD;
    $min_red=$DUST_OK;
    $sensor_gradient=50;
    break;       
  case 3: // Sewer
    $title_name="VOC's / Sewer";
    $sensor_column="sewer";
    $default_sensor_3="selected='selected'";
    $min_orange=$SEWER_GOOD;
    $min_red=$SEWER_OK;
    $sensor_gradient=300;
    break;  
  case 4: // Formaldehyde
    $title_name="Formaldehyde";
    $sensor_column="hcho";
    $default_sensor_4="selected='selected'";
    $min_orange=$HCHO_GOOD;
    $min_red=$HCHO_OK;
    $sensor_gradient=10;
    break;   
  case 5: // Carbon Monoxide
    $title_name="Carbon Monoxide";
    $sensor_column="co";
    $default_sensor_5="selected='selected'";
    $min_orange=$CO_GOOD;
    $min_red=$CO_OK;
    $sensor_gradient=10;
    break;      
  default: // Humidity
    $title_name="Humidity";
    $sensor_column="humidity";
    $default_sensor_1="selected='selected'";
    $min_orange=$HUMIDITY_GOOD;
    $min_red=$HUMIDITY_OK;
    $sensor_gradient=10;
    break;    
}

// get the year from the query string and sanitize it
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);

$conn=mysqli_connect("", $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
if(strlen($year)<=0) {
  $result=mysqli_query($conn,"SELECT MAX(ts) as ts from readings WHERE group_id=$id");
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
  echo "<table border=0 style='border-spacing: 0;width: 100%;'>";
  echo "<td><h2>Average $title_name Calendar</h2></td>"; 
  echo "<td>";
  echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>$group_name</span>";
  echo "</td>";
  echo "<td><input type='radio' onclick='change_period(1);' $default_period_1>Day</td>";
  echo "<td><input type='radio' onclick='change_period(2);' $default_period_2>Week</td>";
  echo "<td>";
  echo "  <select onchange='change_sensor(this.value);'>\n";
  if(isset($sensor_temp)) {
    echo "    <option value='1' $default_sensor_1>Humidity</option>\n";
  }
  if(isset($sensor_dust)) {
    echo "    <option value='2' $default_sensor_2>Dust</option>\n";
  }
  if(isset($sensor_sewer)) {
    echo "    <option value='3' $default_sensor_3>VOC's / Sewer</option>\n";
  }
  if(isset($sensor_hcho)) {
    echo "    <option value='4' $default_sensor_4>Formaldehyde</option>\n";
  }
  if(isset($sensor_co)) {
    echo "    <option value='5' $default_sensor_5>Carbon Monoxide</option>\n";
  }
  echo "  </select>\n"; 
  echo "</td>";
  echo "<td align=right><img src='images/home.png' onclick='home_button();' height=30 width=30 style='cursor:pointer;'></td>\n";  
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
  echo "</table>\n";

// --------------------------------------------------------------------- CALENDAR

echo "<table border=1 class='ave-table'>\n";

// ---------------------------------------- DOM ROW
echo "<tr>";
echo "<td></td>";
for ($f = 1; $f <= 31; $f++) {
    echo "<td><img src='images/transparent.gif' width='44' height='1'><br/>".$f."</td>";
}
echo "</tr>\n";

foreach($currentYear->months() as $month): 
	$day_array = array();
	
	// ----------------------------------------------------------------- FILL STRUCT ARRAY
	for ($dom = 1; $dom <= 31; $dom++) {
		$day_array[$dom] = new DayStruct();
		
		if(checkdate($month->int(),$dom,$currentYear->int())) {
		    $curr_date=Carbon::createFromDate($currentYear->int(),$month->int(),$dom);
		    $curr_date_start_utc=$curr_date->startOfDay()->format('U');
	        $curr_date_end_utc=$curr_date->endOfDay()->format('U');
	        $ave = getAverage($curr_date_start_utc, $curr_date_end_utc);
	        $day_array[$dom]->average = $ave;		    
		    $day_array[$dom]->color=getColorString($ave,$sensor_gradient);
		    $day_array[$dom]->dow=$curr_date->dayOfWeek;
	    } else {
			$day_array[$dom]->color="#FFFFFF";
		}
	}
    // ----------------------------------------------------------------- DAY ROW
    echo "<tr>";
	echo "<th rowspan=2>".$month->name()."</th>";
	for ($dom = 1; $dom <= 31; $dom++) {
		$curr_date=Carbon::createFromDate($currentYear->int(),$month->int(),$dom);
		$day_str = $curr_date->formatLocalized('%A');
	    echo "<td class='ave-td' style='background-color:".$day_array[$dom]->color.";'>";
	    if(isset($day_array[$dom]->dow)) {
		    if($curr_date->isWeekend()) echo "<b>";
		    echo mb_strimwidth($day_str,0,2);
		    if($curr_date->isWeekend()) echo "</b>";
	    }
		echo "</td>\n";
	}
    echo "</tr>";
    // ----------------------------------------------------------------- AVG ROW
    echo "<tr>";
    for ($dom = 1; $dom <= 31; $dom++) {
		$curr_date=Carbon::createFromDate($currentYear->int(),$month->int(),$dom);
		echo "<td class='ave-td' style='background-color:".$day_array[$dom]->color.";'>";
		echo $day_array[$dom]->average;
		echo "</td>\n";
	}
	echo "</tr>";
endforeach;

// ---------------------------------------- DOM ROW
echo "<tr>";
echo "<td></td>";
for ($f = 1; $f <= 31; $f++) {
    echo "<td>".$f."</td>";
}
echo "</tr>";

echo "</table>\n";

// ------------------------------------------------------------------- Form
echo "<form action='averages.php' method='get' name='cal'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='year' value='$year'>";
echo "<input type='hidden' name='sensor' value='$sensor'>";
echo "<input type='hidden' name='period' value='$period'>";
echo "</form>";

echo "</body>\n";
echo "</html>\n";

function getAverage($start_utc, $end_utc) {
	global $id;
	global $conn;
	global $sensor_column;
	
	$result=mysqli_query($conn,"SELECT AVG($sensor_column) as ag from readings WHERE group_id=$id and ts>=$start_utc and ts<=$end_utc");
          
	if(mysql_errno()) {
	    exit('Error: '.mysqli_error($conn));
    }
    $row = mysqli_fetch_array($result);
	$avg=round($row['ag']);
	if($avg==0) return "&nbsp;";
	if(strcmp($sensor_column, "humidity")==0) {
		$avg=$avg."%";
	}
	return $avg;
}

function getColorString($value, $gradient) {
	$remainder = $value / $gradient;
	
    if($remainder==0) return "#BEBEBE"; // Grey
	if($remainder<1)  return "#ffe300"; // Yellow
	if($remainder<2)  return "#ffda00";
	if($remainder<3)  return "#ffc800";
    if($remainder<4)  return "#ffb600";
    if($remainder<5)  return "#ffad00";
    if($remainder<6)  return "#ff9a00";
    if($remainder<7)  return "#ff8800";
    if($remainder<8)  return "#ff7600";
    if($remainder<9)  return "#ff6d00";
    if($remainder<10) return "#ff5b00";
    return "#ff4800"; // Red
}

class DayStruct {
	public $color;
	public $average;
	public $dow;
}
?>
