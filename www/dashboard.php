<html>
  <head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="html/stylesheet.css">
  </head>
  <script>
    function change_size(size) {
      document.dash.action = "dashboard.php";
      document.dash.size.value=size;
      document.dash.submit();
    }
    function change_date(date,direction) {
      document.dash.action = "dashboard.php";
      document.dash.start_date.value=date;
      document.dash.direction.value=direction;
      document.dash.submit();
    }
    function change_zoom(zoom_type) {
      document.dash.action = "zoom_level_change.php";
      document.dash.zoom_type.value=zoom_type;
      document.dash.submit();
    }
    function go_calendar(sensor) {
      document.dash.action = "year_cal.php";
      document.dash.sensor.value=sensor;
      document.dash.submit();
    }
    function back_button() {
      document.home.action = "year_cal.php";
      document.home.submit();
    } 
    function home_button() {
      document.home.action = "index.php";
      document.home.submit();
    }    
    function go_graph() {
      document.home.action = "graphs/histogram.php";
      document.home.submit();
    }    
  </script>
  <body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include 'globals.php';
$range_width=100;
$graph_width=1000;
$width_pix = array(300, 600, 1000);
$height_pix = array(150, 300, 500);

$start_date_param = htmlspecialchars($_GET["start_date"]);
$direction_param = htmlspecialchars($_GET["direction"]);
if($size==0) $default_size_0="selected='selected'";
if($size==1) $default_size_1="selected='selected'";
if($size==2) $default_size_2="selected='selected'";

$today_ts=get_ts_today($start_date_param,$direction_param);

if(!isset($today_ts)) {
  echo "No records found";
} else {
  $date = Carbon::createFromTimeStamp($today_ts);
  $start_day_utc = $date->startOfDay()->format('U');
  $prev_day_str = $date->copy()->subDay()->format($param_date_format);
  $next_day_str = $date->copy()->addDay()->format($param_date_format);
  $end_day_utc = $date->endOfDay()->format('U');
  $geo_row = get_current_geographical($end_day_utc,$id);  

  echo "<div style='padding:10px;'>";
  echo "<table border=0>";
  echo "<tr><td width=$range_width></td><td width =$graph_width></td><td width=100></td></tr>";
  echo "<tr><td colspan=3>";
  echo "<table border=0 width=100%>";
  echo "<tr><td>";
  echo "<h2>$sensor_type_name Dashboard</h2>";
  echo "</td><td width='400' style='vertical-align:top'>";

  $geo_name=$geo_row['name'];
  echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>";
  echo $group_name;  
  if(strlen($geo_name)>0) 
    echo " - ".$geo_name;
  echo "</span>";
  echo "</td>";
  echo "<td align=right><img src='images/back.png' onclick='back_button();' height=30 width=30 style='cursor:pointer;'>\n";    
  echo "<img src='images/home.png' onclick='home_button();' height=30 width=30 style='cursor:pointer;'></td>\n";  
  echo "</tr>";
  echo "</table>";  
  echo "<tr>";
  echo "<td colspan=2 width=$graph_width align=center>";
  echo "<table border=0>";
  echo "<tr>";
  echo "  <td align='right'><input type='button' value='&lt; Previous' onclick='change_date(\"$prev_day_str\",\"prev\")'></td>";
  echo "  <td width='300' align=center >";
  echo "  <input type='button' value='".$date->format('l, F jS Y')."' onclick='go_graph();'>";
  echo "  </td>";
  echo "  <td><input type='button' value='Next    &gt;' onclick='change_date(\"$next_day_str\",\"next\")'></td>";
  echo "</tr>";
  echo "</table>";
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
  
  if(isset($sensor_temp)) { // ----------------------------------------- Temperature / Humidity
/*
    echo "<div class='container'>";
    echo "<table border=0>";    
    echo "<tr>";
    if($size==2) {
      echo "<td></td>";
    }
    echo "<td>";
    
    echo "<table border=0 width=100%>";
    echo "<tr>";
    echo "<td>";
    $zoom_image=($geo_row['zoom_temp_hum']==0) ? "zoom_small.png" : "zoom_large.png";
    echo "<img src='images/$zoom_image' onclick='change_zoom(1);' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";
    echo "<td align=center><h3 style='display:inline;'>Temperature & Humidity</h3>&nbsp;";
    echo "<img src='health/mask.png' onclick='location.href=\"health/mold.html\"' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    if($size==2) {
      echo "<td rowspan=2 width=$range_width style='height:100%;'>";
      echo "  <div style='height:100%;overflow:auto;'>";  
      echo "  <table style='width:100%;height:100%;' border=0>";
      echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
      echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
      echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
      echo "  </table>";
      echo "  </div>";  
      echo "</td>"; 
    }
    echo "<td>";
    echo "<img src='graphs/dht22.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(1);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";
*/
	// ----------------------------------------------------------------- Humidity
    echo "<div class='container'>";
    echo "<table border=0>";    
    echo "<tr>";
    if($size==2) {
      echo "<td></td>";
    }
    echo "<td>";
    
    echo "<table border=0 width=100%>";
    echo "<tr>";
    echo "<td width='50px'>";
    $zoom_image=($geo_row['zoom_temp_hum']==0) ? "zoom_small.png" : "zoom_large.png";
    echo "<img src='images/$zoom_image' onclick='change_zoom(1);' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";
    echo "<td align=center><h3 style='display:inline;'>Humidity</h3>&nbsp;";
    echo "<img src='health/mask.png' onclick='location.href=\"health/mold.html\"' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    if($size==2) {
      echo "<td rowspan=2 width=$range_width style='height:100%;'>";
      echo "  <div style='height:100%;overflow:auto;'>";  
      echo "  <table style='width:100%;height:100%;' border=0>";
      echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
      echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
      echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
      echo "  </table>";
      echo "  </div>";  
      echo "</td>"; 
    }
    echo "<td>";
    echo "<img src='graphs/humidity.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(1);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";    
    // ----------------------------------------------------------------- Temperature
    echo "<div class='container'>";
    echo "<table border=0>";    
    echo "<tr>";
    if($size==2) {
      echo "<td></td>";
    }
    echo "<td>";
    
    echo "<table border=0 width=100%>";
    echo "<tr>";
    echo "<td width='50px'>";
    $zoom_image=($geo_row['zoom_temp_hum']==0) ? "zoom_small.png" : "zoom_large.png";
    echo "<img src='images/$zoom_image' onclick='change_zoom(1);' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";
    echo "<td align=center><h3 style='display:inline;'>Temperature</h3>&nbsp;";
    echo "<img src='health/mask.png' onclick='location.href=\"health/mold.html\"' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    if($size==2) {
      echo "<td rowspan=2 width=$range_width style='height:100%;'>";
      echo "  <div style='height:100%;overflow:auto;'>";  
      echo "  <table style='width:100%;height:100%;' border=0>";
      echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
      echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
      echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
      echo "  </table>";
      echo "  </div>";  
      echo "</td>"; 
    }
    echo "<td>";
    echo "<img src='graphs/temperature.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(1);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";        
  }
  if(isset($sensor_dust)) { // ----------------------------------------- Dust
    echo "<div class='container'>";  
    echo "<table border=0>";      
    echo "<tr>";
    if($size==2) {
      echo "<td></td>";
    }  
    echo "<td>";
    
    echo "<table border=0 width=100%>";
    echo "<tr>";
    echo "<td>"; 
    //echo "<img src='images/zoom_large.png' onclick='change_zoom(2);' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";    
    echo "<td align=center colspan=2><h3 style='display:inline;'>Dust Particle Concentration (over 1 micron)</h3>&nbsp;";
    echo "<img src='health/mask.png' onclick='location.href=\"health/dust.html\"' width=30 height=30 style='cursor:pointer;'>";    
    echo "</td>";
    echo "</tr>";
    echo "</table>";
        
    echo "</td>";  
    echo "</tr>";
    echo "<tr>";  
    if($size==2) {  
      echo "<td rowspan=2 width=$range_width style='height:100%'>";
      echo "  <div style='height:100%;overflow:auto;'>";  
      echo "  <table style='width:100%;height:100%' border=0>";
      echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
      echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
      echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
      echo "  </table>";
      echo "  </div>";  
      echo "</td>";
    }
    echo "<td>";
    echo "<img src='graphs/dust.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(2);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
 
    echo "</table>";
    echo "</div>";   
  }
  if(isset($sensor_sewer)) { // ---------------------------------------- VOC's / Sewer
    echo "<div class='container'>";  
    echo "<table border=0>";      
    echo "<tr>";
    if($size==2) {
      echo "<td></td>";
    }  
    echo "<td>";
    
    echo "<table border=0 width=100%>";
    echo "<tr>";
    echo "<td>";
    $zoom_image=($geo_row['zoom_sewer']==0) ? "zoom_small.png" : "zoom_large.png";
    echo "<img src='images/$zoom_image' onclick='change_zoom(3);' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";    
    echo "<td align=center colspan=2><h3 style='display:inline;'>VOC's / Sewer Gas</h3>&nbsp;";
    echo "<img src='health/mask.png' onclick='location.href=\"health/tgs2602.html\"' width=30 height=30 style='cursor:pointer;'>";    
    echo "</td>";
    echo "</tr>";
    echo "</table>";
        
    echo "</td>";   
    echo "</tr>";
    echo "<tr>";  
    if($size==2) {  
      echo "<td rowspan=2 width=$range_width style='height:100%'>";
      echo "  <div style='height:100%;overflow:auto;'>";  
      echo "  <table style='width:100%;height:100%' border=0>";
      echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
      echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
      echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
      echo "  </table>";
      echo "  </div>";  
      echo "</td>";
    }
    echo "<td>";
    echo "<img src='graphs/sewer.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(3);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
 
    echo "</table>";
    echo "</div>"; 
  }
  if(isset($sensor_hcho)) { // ----------------------------------------- Formaldehyde
    echo "<div class='container'>";  
    echo "<table border=0>";      
    echo "<tr>";
    if($size==2) {
      echo "<td></td>";
    } 
    echo "<td>";
    
    echo "<table border=0 width=100%>";
    echo "<tr>";
    echo "<td>";
    //echo "<img src='images/zoom_large.png' onclick='change_zoom(4);' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";
    echo "<td align=center colspan=2><h3 style='display:inline;'>Formaldehyde Gas</h3>&nbsp;";
    echo "<img src='health/mask.png' onclick='location.href=\"health/formaldehyde.html\"' width=30 height=30 style='cursor:pointer;'>";    
    echo "</td>";
    echo "</tr>";
    echo "</table>";
        
    echo "</td>";  
    echo "</tr>";
    echo "<tr>";  
    if($size==2) {  
      echo "<td rowspan=2 width=$range_width style='height:100%'>";
      echo "  <div style='height:100%;overflow:auto;'>";  
      echo "  <table style='width:100%;height:100%' border=0>";
      echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
      echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
      echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
      echo "  </table>";
      echo "  </div>";  
      echo "</td>";
    }
    echo "<td>";
    echo "<img src='graphs/wsp2110.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(4);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
 
    echo "</table>";
    echo "</div>";    
  }
  if(isset($sensor_co)) { // ----------------------------------------- Dust
    echo "<div class='container'>";  
    echo "<table border=0>";      
    echo "<tr>";
    if($size==2) {
      echo "<td></td>";
    }  
    echo "<td>";
    
    echo "<table border=0 width=100%>";
    echo "<tr>";
    echo "<td>"; 
    //echo "<img src='images/zoom_large.png' onclick='change_zoom(2);' width=30 height=30 style='cursor:pointer;'>";  
    echo "</td>";    
    echo "<td align=center colspan=2><h3 style='display:inline;'>Carbon Monoxide</h3>&nbsp;";
    //echo "<img src='health/mask.png' onclick='location.href=\"health/dust.html\"' width=30 height=30 style='cursor:pointer;'>";    
    echo "</td>";
    echo "</tr>";
    echo "</table>";
        
    echo "</td>";  
    echo "</tr>";
    echo "<tr>";  
    if($size==2) {  
      echo "<td rowspan=2 width=$range_width style='height:100%'>";
      echo "  <div style='height:100%;overflow:auto;'>";  
      echo "  <table style='width:100%;height:100%' border=0>";
      echo "  <tr><td align=right><font color=red>Bad</font></td></tr>";    
      echo "  <tr><td align=right><font color=orange>Okay</font></td></tr>";
      echo "  <tr><td align=right><font color=green>Good</font></td></tr>";
      echo "  </table>";
      echo "  </div>";  
      echo "</td>";
    }
    echo "<td>";
    echo "<img src='graphs/co.php?id=$id&width=$width_pix[$size]&height=$height_pix[$size]&start_ts=$start_day_utc&end_ts=$end_day_utc' width='$width_pix[$size]' height='$height_pix[$size]' onclick='go_calendar(2);' style='cursor:pointer;'>";
    echo "</td>";
    echo "</tr>";
 
    echo "</table>";
    echo "</div>";   
  }  
  // ------------------------------------------------------------------- Home Form
  echo "<form action='index.php' method='get' name='home'>\n";
  echo "<input type='hidden' name='id' value='$id'>\n";
  echo "<input type='hidden' name='year' value='".$date->format('Y')."'>\n";
  echo "<input type='hidden' name='month' value='".$date->format('n')."'>\n";
  echo "<input type='hidden' name='day' value='".$date->format('j')."'>\n";  
  echo "<input type='hidden' name='size' value='$size'>";  
  echo "</form>\n";     
  // ------------------------------------------------------------------- Form
  echo "<form action='dashboard.php' method='get' name='dash'>";
  echo "<input type='hidden' name='id' value='$id'>";
  echo "<input type='hidden' name='start_date' value='$start_date_param'>";
  echo "<input type='hidden' name='end_date' value='$end_date_param'>";
  echo "<input type='hidden' name='period' value='day'>";
  echo "<input type='hidden' name='direction' value='$direction_param'>";
  echo "<input type='hidden' name='size' value='$size'>";
  echo "<input type='hidden' name='sensor' value=''>";
  echo "<input type='hidden' name='zoom_type' value=''>";
  echo "</form>";
  echo "<hr/>";  
  include 'events/event_summary.php';  
  include 'events/event_dayview.php';    
  include 'events/event_timeline.php';
}
?>
  </body>  
</html>
