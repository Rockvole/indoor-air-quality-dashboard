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

if($sensor_type==0) {
  $sensor = htmlspecialchars($_GET["sensor"]);
} else $sensor=$sensor_type;
switch($sensor) {
  case 2: // Dust
    $title_name="Dust";
    $sensor_column="dust";
    $default_sensor_2="checked='checked'";
    $min_orange=$DUST_GOOD;
    $min_red=$DUST_OK;
    break;       
  case 3: // Sewer
    $title_name="VOC's / Sewer";
    $sensor_column="sewer";
    $default_sensor_3="checked='checked'";
    $min_orange=$SEWER_GOOD;
    $min_red=$SEWER_OK;
    break;  
  case 4: // Formaldehyde
    $title_name="Formaldehyde";
    $sensor_column="hcho";
    $default_sensor_4="checked='checked'";
    $min_orange=$HCHO_GOOD;
    $min_red=$HCHO_OK;
    break;   
  case 5: // Carbon Monoxide
    $title_name="Carbon Monoxide";
    $sensor_column="co";
    $default_sensor_5="checked='checked'";
    $min_orange=$CO_GOOD;
    $min_red=$CO_OK;
    break;      
  default: // Humidity
    $title_name="Humidity";
    $sensor_column="humidity";
    $default_sensor_1="checked='checked'";
    $min_orange=$HUMIDITY_GOOD;
    $min_red=$HUMIDITY_OK;
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
  echo "<table style='border-spacing: 0;width: 100%;'>";
  echo "<td><h2>Average $title_name Calendar</h2></td>"; 
  echo "<td>";
  echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>$group_name</span>";
  echo "</td>";
  if(isset($sensor_temp)) {
    echo "<td>";
    echo "<input type='radio' onclick='change_sensor(1);' $default_sensor_1>Humidity";
    echo "</td>";
  }
  if(isset($sensor_dust)) {
    echo "<td>";
    echo "<input type='radio' onclick='change_sensor(2);' $default_sensor_2>Dust";
    echo "</td>";  
  }
  if(isset($sensor_sewer)) {
    echo "<td>";
    echo "<input type='radio' onclick='change_sensor(3);' $default_sensor_3>VOC's / Sewer";
    echo "</td>";
  }
  if(isset($sensor_hcho)) {
    echo "<td>";
    echo "<input type='radio' onclick='change_sensor(4);' $default_sensor_4>Formaldehyde";
    echo "</td>";  
  }
  if(isset($sensor_co)) {
    echo "<td>";
    echo "<input type='radio' onclick='change_sensor(5);' $default_sensor_5>Carbon Monoxide";
    echo "</td>";  
  }
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

// ---------------------------------------- NEW

echo "<table border=1 class='ave-table'>\n";

// ---------------------------------------- DOM ROW
echo "<tr>";
echo "<td></td>";
for ($f = 1; $f <= 31; $f++) {
    echo "<td><img src='images/transparent.gif' width='44' height='1'><br/>".$f."</td>";
}
echo "</tr>";

foreach($currentYear->months() as $month): 
	echo "<tr>";
	echo "<th>".$month->name()."<img src='images/transparent.gif' width='1' height='40'></th>";
	for ($dom = 1; $dom <= 31; $dom++) {
		echo "<td class='ave-td'>";
		if(checkdate($month->int(),$dom,$currentYear->int())) {
		    $curr_date=Carbon::createFromDate($currentYear->int(),$month->int(),$dom);
		    $curr_date_start_utc=$curr_date->startOfDay()->format('U');
	        $curr_date_end_utc=$curr_date->endOfDay()->format('U');
	        
		    $day_str = $curr_date->formatLocalized('%A');
		    if($curr_date->isWeekend()) echo "<b>";
		    echo mb_strimwidth($day_str,0,2);
		    if($curr_date->isWeekend()) echo "</b>";
		    echo "<br/>\n".getAverage($curr_date_start_utc, $curr_date_end_utc);
	    }
		echo "</td>";
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
echo "</form>";

echo "</body>\n";
echo "</html>\n";

function getAverage($start_utc, $end_utc) {
	global $id;
	global $conn;
	global $sensor_column;
	
	error_log("id=".$id);
	$result=mysqli_query($conn,"SELECT AVG($sensor_column) as ag from readings WHERE group_id=$id and ts>=$start_utc and ts<=$end_utc");
          
	if(mysql_errno()) {
	    exit('Error: '.mysqli_error($conn));
    }
    $row = mysqli_fetch_array($result);
	$avg=round($row['ag']);
	if($avg==0) return "";
	if(strcmp($sensor_column, "humidity")==0) {
		$avg=$avg."%";
	}
	return $avg;
}


?>
