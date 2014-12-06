<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta charset="UTF-8">
  <title>Choose Event</title>
  <link rel="stylesheet" href="../html/stylesheet.css" type="text/css" >
  <link rel="stylesheet" href="../calendar/calendar.css" type="text/css" />
  <link rel="stylesheet" href="../javascript/tipsy.css" type="text/css" />
  <script src="../javascript/jquery-1.11.1.min.js"></script>
  <script src="../javascript/jquery.tipsy.js"></script>
</head>
  <script>
    function change_month(year,month) {
      document.cal.year.value=year;
      document.cal.month.value=month;    
      document.cal.submit();
    }
    function select_day(day) {
      document.cal.day.value=day;
      document.cal.submit();      
    }
    function select_event(event_type, event_name, event_id) {
      document.graph.type.value=event_type;
      document.graph.name.value=event_name;
      document.graph.event_id.value=event_id;
    }
    function submit_day(day) {
      document.graph.day.value=day;
      select_event('day','Entire Day',-1);
      document.graph.submit();
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
$year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
$day_param = filter_input(INPUT_GET, 'day', FILTER_VALIDATE_INT);

// initialize the calendar object
$calendar = new calendar();

// get the current month object by year and number of month
$currentMonth = $calendar->month($year, $month);

// get the previous and next month for pagination
$prevMonth = $currentMonth->prev();
$nextMonth = $currentMonth->next();
  
echo "<table border=0 class='form_table'>\n";
echo "<tr>\n";
echo "<td colspan=2><h2>Choose Event</h2></td>\n";
echo "<td>";
echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>$sensor_name</span>";
echo "</td>";
echo "<td align=right><img src='../images/home.png' onclick='back_button();' height=30 width=30 style='cursor:pointer;'></td>\n";
echo "</tr>\n";
echo "</table>";
echo "<table border=0 style='width:240px'>\n";
echo "<tr>\n";
echo "<td><input type='button' value='&lt; Previous' style='padding:2px;' onclick='change_month(\"".$prevMonth->year()->int()."\",\"".$prevMonth->int()."\")'></td>\n";
echo "<td width='300' align=center ><b>".$currentMonth->year()->name()."</b></td>\n";
echo "<td align=right><input type='button' value='Next    &gt;' style='padding:2px;' onclick='change_month(\"".$nextMonth->year()->int()."\",\"".$nextMonth->int()."\")'></td>\n";
echo "</tr>\n";
echo "</table>\n";

echo "<section class='year' style='background:white;'>\n";

echo "<ul>\n";
echo "<li>\n";
echo "<h2>".$currentMonth->name()."</h2>\n";
    
echo "<table border=0 >\n";
echo "<tr>\n";
  foreach($currentMonth->weeks()->first()->days() as $weekDay): 
    echo "<th>".$weekDay->shortname()."</th>\n";
  endforeach; 
  echo "</tr>\n";
  foreach($currentMonth->weeks(6) as $week): 
  echo "<tr>\n";
  foreach($week->days() as $day): 
    if($day->month() != $currentMonth) {
      echo "<td class='inactive'>".$day->int()."</td>\n";
    } else {
      $curr_date=Carbon::createFromDate($day->year()->int(),$day->month()->int(),$day->int(), $user_timezone);
      $curr_date_start_utc=$curr_date->startOfDay()->format('U');
      $curr_date_end_utc=$curr_date->endOfDay()->format('U');
      // Search day for all readings to see if we will allow day to be clickable
      $result=mysqli_query($conn,"SELECT count(*) as cnt from readings WHERE core_id=$id and ts>=$curr_date_start_utc and ts<$curr_date_end_utc");
      if(mysql_errno()) {
        exit('Error: '.mysqli_error($conn));
      }
      $row = mysqli_fetch_array($result);
      if($row['cnt']>0) {
        // Search day for all events so we can display tooltips
        $result=mysqli_query($conn,"SELECT * from events WHERE core_id=$id and ts>=$curr_date_start_utc and ts<$curr_date_end_utc ".
                                   "and name is not null  order by ts");
        if(mysql_errno()) {
          exit('Error: '.mysqli_error($conn));
        }
        $found_event=false;
        $background_color='white';
        $tooltip_str="";
      
        while($row = mysqli_fetch_array($result)) {
	  if(!$found_event) {
	    $tooltip_str.="Events<br/>";
	  }
	  $ts_carbon = Carbon::createFromTimeStamp($row['ts']);	
	  error_log("name=".$row['name']."||ts=".$row['ts']."||ts=".$ts_carbon->format($param_date_format));
	  $ts[]=$row['ts'];
	  $tooltip_str.=$ts_carbon->format('H:i')." ".$row['name']."<br/>";
	  $found_event=true;
        }     
      
        if($day->isToday()) {
	  $day_html="<strong style='color:red;'>" . $day->int() . "</strong>\n";
        } else {
	  $day_html=$day->int() . "\n";
        }
        if(strlen($tooltip_str)>0) {
          echo "<td onclick='select_day(".$day->int().");' class='cal-day' style='background-color:blueviolet;color:white;cursor:pointer;' title='$tooltip_str'>\n";     
          echo $day_html;
          echo "</td>\n";
        } else {
	  echo "<td onclick=\"submit_day(".$day->int().");\" style='background-color:thistle;cursor:pointer;'>".$day_html."</td>\n";
        }
      } else echo "<td>".$day->int()."</td>\n";
    }
  endforeach;
  echo "</tr>\n";
  endforeach;
echo "</table>\n";
echo "</li>\n";
echo "<li>";
if(strlen($day_param)>0) {
  echo "<table border=0>";
  echo "<tr><td><img src='../html/transparent.gif' width='800' height='1'></td></tr>";
  echo "<tr>";
  echo "<td>";
  include 'event_selector.php';
  echo "</td>";
  echo "</tr>";
  echo "</table>";
}
echo "</li>";
echo "</ul>\n";
echo "</section>\n";


// ------------------------------------------------------------------- Form
echo "<form action='event_monthly.php' method='get' name='cal'>\n";
echo "<input type='hidden' name='id' value='$id'>\n";
echo "<input type='hidden' name='year' value='$year'>\n";
echo "<input type='hidden' name='month' value='$month'>\n";
echo "<input type='hidden' name='day' value=''>\n";
echo "</form>\n";
// ------------------------------------------------------------------- Graph Form
echo "<form action='../graphs/histogram.php' method='get' name='graph'>\n";
echo "<input type='hidden' name='id' value='$id'>\n";
echo "<input type='hidden' name='type' value='day'>\n";
echo "<input type='hidden' name='name' value='Entire Day'>\n";
echo "<input type='hidden' name='event_id' value='-1'>\n";
echo "<input type='hidden' name='year' value='".$currentMonth->year()->int()."'>\n";
echo "<input type='hidden' name='month' value='".$currentMonth->int()."'>\n";
echo "<input type='hidden' name='day' value='$day_param'>\n";
echo "</form>\n";
// ------------------------------------------------------------------- Back
echo "<form action='../index.php' method='get' name='back'>\n";
echo "</form>\n";
echo "<script type='text/javascript'>\n";

echo "$.fn.tipsy.defaults = {\n";
echo "      delayIn: 0,\n";      // delay before showing tooltip (ms)
echo "      delayOut: 0,\n";     // delay before hiding tooltip (ms)
echo "      fade: false,\n";     // fade tooltips in/out?
echo "      fallback: '',\n";    // fallback text to use when no tooltip text
echo "      gravity: 'n',\n";    // gravity
echo "      html: true,\n";      // is tooltip content HTML?
echo "      live: false,\n";     // use live event support?
echo "      offset: 0,\n";       // pixel offset of tooltip from element
echo "      opacity: 1.0,\n";    // opacity of tooltip
echo "      title: 'title',\n";  // attribute/callback containing tooltip text
echo "      trigger: 'hover'\n"; // how tooltip is triggered - hover | focus | manual
echo "    };\n";
echo "$(function() {\n";
echo "  $('.cal-day').tipsy({gravity: 'nw'});\n";
echo "});\n";
echo "</script>\n";
echo "</body>\n";
echo "</html>\n";
mysqli_close($conn);  
?>    

