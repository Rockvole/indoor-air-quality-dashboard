<html>
  <head>
    <title>Add New Sensor</title>
    <link rel="stylesheet" type="text/css" href="html/stylesheet.css">    
  </head>
  
  <body>
<?php
  echo "<h2>Add New Sensor</h2>";
  
  echo "<form action='initialize_core.php' method='get'>";
  echo "<table border=0 class='form_table'>";
  
  echo "<tr>";
  echo "<th align=right>Identifying Name:<br/>(Button name on front page)</th>";
  echo "<td><input type='text' name='name' size=30></td>";
  echo "</tr>";  
  
  echo "<tr>";
  echo "<th align=right>Core Id:<br/>(e.g. 53ff70065069544807300687)</th>";
  echo "<td><input type='text' name='core_id' size=30></td>";
  echo "</tr>";
  
  echo "<tr>";
  echo "<th align=right>Time Zone:</th>";
  echo "<td>";
  echo "<select name='tz'>";
  echo "<option value=''>Please Select Timezone</option>";
  foreach(tz_list() as $t) { 
    echo "<option value='".$t['zone']."'>";
    echo $t['zone'] . " (".$t['diff_from_GMT'].")";
    echo "</option>";
  }
  echo "</select>";
  echo "</td>";
  echo "</tr>";  
  
  echo "<tr>";
  echo "<th align=right>Unit:<br/>(NOT IMPLEMENTED)</th>";
  echo "<td><input type='radio' name='unit' value='C' checked>Centigrade<br/>";
  
  echo"<input type='radio' name='unit' value='F'>Fahrenheit</td>";
  echo "</tr>";  
  
  echo "<tr>";
  echo "<th align=right></th>";
  echo "<td><input type='submit' value='Submit'></td>";
  echo "</tr>";  
  echo "</table>";
  echo "</form>";
  
function tz_list() {
  $zones_array = array();
  $timestamp = time();
  foreach(timezone_identifiers_list() as $key => $zone) {
    date_default_timezone_set($zone);
    $zones_array[$key]['zone'] = $zone;
    $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
  }
  return $zones_array;
}  
?>
  </body>  
</html>    
