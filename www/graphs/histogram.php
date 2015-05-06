<html>
  <head>
    <title>Histograms</title>
    <link rel="stylesheet" type="text/css" href="../html/stylesheet.css">
  </head>  
  <script>
    function change_size(size) { 
      document.dash.action = "histogram.php";
      document.dash.size.value=size;
      document.dash.submit();
    }
    function change_date(year,month,day,direction) {
      document.dash.action = "histogram.php";
      document.dash.type.value="day";      
      document.dash.year.value=year;
      document.dash.month.value=month;
      document.dash.day.value=day;
      document.dash.direction.value=direction;
      document.dash.submit();
    }
    function back_button() {
      document.home.action = "../events/event_monthly.php";
      document.home.submit();
    } 
    function home_button() {
      document.home.action = "../index.php";
      document.home.submit();
    }     
    function go_graph() {
      document.home.action = "../dashboard.php";
      document.home.submit();
    }    
  </script>  
  <body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include '../globals.php';

$type = $_GET["type"];
$type_day=true;
if(strlen($type)>0) 
  if(strcmp($type,"day")!=0) $type_day=false; 

$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
$year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
$day_param = filter_input(INPUT_GET, 'day', FILTER_VALIDATE_INT);

$range_width=100;
$graph_width=1000;
$width_pix = array(300, 600, 1000);
$height_pix = array(150, 300, 500);

$direction_param = htmlspecialchars($_GET["direction"]);
if($size==0) $default_size_0="selected='selected'";
if($size==1) $default_size_1="selected='selected'";
if($size==2) $default_size_2="selected='selected'";

if($type_day) {
  $title="Entire Day";
  $start_date_param=$year."-".$month."-".$day_param;
  $today_ts=get_ts_today($start_date_param,$direction_param);

  if(!isset($today_ts)) {
    echo "No records found";
  } else {  
    $date=Carbon::createFromTimeStamp($today_ts);
    $prev_date=$date->copy()->subDay();
    $next_date=$date->copy()->addDay();
    $start_day_utc = $date->startOfDay()->format('U');
    $end_day_utc = $date->endOfDay()->format('U'); 
    
    $prev_year  = $prev_date->format('Y');
    $prev_month = $prev_date->format('m');
    $prev_day   = $prev_date->format('d');
    $next_year  = $next_date->format('Y');
    $next_month = $next_date->format('m');    
    $next_day   = $next_date->format('d');
  }
} else if(strcmp($type,"event")==0) {
  $title=$_GET["name"];  
  $result=mysqli_query($conn,"SELECT * from events WHERE group_id=$id order by ts");
  $get_next=false;
  while($row = mysqli_fetch_array($result)) {
    error_log("events=".$row['ts']." = ".$row['name']);
    if($get_next) {
      $end_day_utc = $row['ts'];
      $get_next=false;
    }
    if($event_id==$row['id']) {
      $start_day_utc = $row['ts'];
      $get_next=true;
    }
  }
  if(!isset($end_day_utc)) {
    $result=mysqli_query($conn,"SELECT max(ts) as ts from readings WHERE group_id=$id");  
    $row=mysqli_fetch_array($result);
    $end_day_utc=$row['ts'];
  }
  //error_log("start_day_utc=".$start_day_utc."||end_day_utc=".$end_day_utc);
} else {
  exit('Error: unknown type '.$type);
}

  // ------------------------------------------------------------------- Heading
  echo "<div style='padding:10px;'>";
  echo "<table border=0>";
  echo "<tr><td width=$range_width></td><td width =$graph_width></td><td width=100></td></tr>";
  echo "<tr><td colspan=3>";
  echo "<table border=0 width=100%>";
  echo "<tr><td>";
  echo "<h2>Histogram of ".$title."</h2>\n";
  echo "</td><td style='vertical-align:top'>";
  echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>";
  echo $group_name;  
  echo "</span>";
  echo "</td>";
  echo "<td></td>";
  echo "<td align=right><img src='../images/back.png' onclick='back_button();' height=30 width=30 style='cursor:pointer;'>\n";    
  echo "<img src='../images/home.png' onclick='home_button();' height=30 width=30 style='cursor:pointer;'></td>\n";  
  echo "</tr>";
  echo "</table>";  
  echo "<tr>";
  echo "<td colspan=2 width=$graph_width align=center>";
  if($type_day) {
    echo "<table border=0>";
    echo "<tr>";
    echo "  <td align='right'><input type='button' value='&lt; Previous' onclick='change_date($prev_year,$prev_month,$prev_day,\"prev\")'></td>";
    echo "  <td width='300' align=center >";
    echo "  <input type='button' value='".$date->format('l, F jS Y')."' onclick='go_graph();'>";
    echo "  </td>";
    echo "  <td><input type='button' value='Next    &gt;' onclick='change_date($next_year,$next_month,$next_day,\"next\")'></td>";
    echo "</tr>";
    echo "</table>";
  }
  echo "</td>";
  echo "<td width='100' align=right>";
  echo "<select name='size' id='size_id' onchange='change_size(document.getElementById(\"size_id\").value);'>";
  echo "<option value=0 $default_size_0>Small</option>";
  echo "<option value=1 $default_size_1>Medium</option>";
  echo "<option value=2 $default_size_2>Large</option>";
  echo "</select>";  
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "</div>";  
  
  if(isset($sensor_temp)) { // ----------------------------------------- Humidity
    echo "<div class='container'>";
    echo "<table border=0>";    
    echo "<tr>";
    echo "<td align=center><h3 style='display:inline;'>Humidity</h3>&nbsp;";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>";
    echo "<img src='hist_dht22.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(0);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";  
  }
  if(isset($sensor_dust)) { // ----------------------------------------- Dust  
    echo "<div class='container'>";  
    echo "<table border=0>";      
    echo "<tr>";
    echo "<td align=center colspan=2><h3 style='display:inline;'>Dust Particle Concentration (over 1 micron)</h3>&nbsp;";
    echo "</td>";  
    echo "</tr>";
    echo "<tr>";  
    echo "<td>";
    echo "<img src='hist_dust.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(1);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
 
    echo "</table>";
    echo "</div>";
  }
  if(isset($sensor_sewer)) { // ---------------------------------------- VOC's / Sewer Gas
    echo "<div class='container'>\n";  
    echo "<table border=0>\n";      
    echo "<tr>\n";
    echo "<td align=center colspan=2><h3 style='display:inline;'>VOC's / Sewer Gas</h3>&nbsp;\n";
    echo "</td>\n";   
    echo "</tr>\n";
    echo "<tr>\n";  
    echo "<td>\n";
    echo "<img src='hist_sewer.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(2);' style='cursor:pointer;'>\n";
    echo "</td>\n";
    echo "</tr>\n";

    echo "</table>\n";
    echo "</div>\n"; 
  }
  if(isset($sensor_hcho)) { // ----------------------------------------- Formaldehyde  
    echo "<div class='container'>";  
    echo "<table border=0>";      
    echo "<tr>";
    echo "<td align=center colspan=2><h3 style='display:inline;'>Formaldehyde Gas</h3>&nbsp;";
    echo "</td>";  
    echo "</tr>";
    echo "<tr>";  
    echo "<td>";
    echo "<img src='hist_wsp2110.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(3);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
 
    echo "</table>";
    echo "</div>";   
  }
  // ------------------------------------------------------------------- Home Form
  echo "<form action='../index.php' method='get' name='home'>\n";
  echo "<input type='hidden' name='id' value='$id'>\n";
  echo "<input type='hidden' name='year' value='".$date->format('Y')."'>\n";
  echo "<input type='hidden' name='month' value='".$date->format('n')."'>\n";
  echo "<input type='hidden' name='day' value='".$date->format('j')."'>\n";
  echo "<input type='hidden' name='start_date' value='".$date->format($param_date_format)."'>\n";  
  echo "<input type='hidden' name='size' value='$size'>";  
  echo "</form>\n";       
  // ------------------------------------------------------------------- Form
  echo "<form action='histogram.php' method='get' name='dash'>\n";
  echo "<input type='hidden' name='id' value='$id'>\n";
  echo "<input type='hidden' name='type' value='$type'>\n";
  echo "<input type='hidden' name='name' value='$title'>\n";
  echo "<input type='hidden' name='event_id' value='$event_id'>\n";
  echo "<input type='hidden' name='year' value='".$date->format('Y')."'>\n";
  echo "<input type='hidden' name='month' value='".$date->format('n')."'>\n";
  echo "<input type='hidden' name='day' value='".$date->format('j')."'>\n";
  echo "<input type='hidden' name='direction' value=''>\n";  
  echo "<input type='hidden' name='size' value='$size'>\n";  
  echo "</form>\n";
  include '../events/event_dayview.php';  

?>
  </body>  
</html>
