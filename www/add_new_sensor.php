<html>
  <head>
    <title>Add New Sensor</title>
    <link rel="stylesheet" type="text/css" href="html/stylesheet.css">    
  </head>
  
  <body>
<?php
  echo "<h2>Add New Sensor Group</h2>";
  
  echo "<form action='initialize_core.php' method='get'>";
  echo "<table border=0 class='form_table'>";
  
  echo "<tr>";
  echo "<th align=right>Identifying Name:<br/>(Name on front page)</th>";
  echo "<td><input type='text' name='name' size=30></td>";
  echo "<td style='font-size:110%;font-style:italic;'>e.g. Johns Sensors 1"; 
  echo "</td>";
  echo "</tr>";  

  echo "<tr>";
  echo "<th align=right>Password:<br/>(NOT IMPLEMENTED)</th>";
  echo "<td><input type='text' name='password' size=30></td>";
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
  echo "<td></td>";
  echo "<td colspan=3>";
  echo "<table border=0>";
  echo "<tr><th>Core Id</th><th width=100>Temperature<br/>& Humidity</th><th width=100>Dust</th><th width=100>Sewer</th><th width=100>Formaldehyde</th></tr>";
  for($sensor=0;$sensor<5;$sensor++) {
    $check_str="";
    echo "<tr>";
    if($sensor==0) {
      echo "<td height=40>&lt;NOT ASSIGNED&gt;</td>";
      $check_str="checked='checked'";
    } else {
      echo "<td height=40>";
      echo "<input type='text' name='core_id_$sensor' size=30>";
      echo "</td>";
    }
    echo "<td align=center><input type='radio' name='temp_hum' $check_str value=$sensor></td>";
    echo "<td align=center><input type='radio' name='dust' $check_str value=$sensor></td>";
    echo "<td align=center><input type='radio' name='sewer' $check_str value=$sensor></td>";
    echo "<td align=center><input type='radio' name='hcho' $check_str value=$sensor></td>";
    echo "</tr>";
  }
  echo "<tr>";
  echo "<td style='font-size:110%;font-style:italic;'>e.g. 53ff70065069544807300687</td>"; 
  echo "</tr>";  
  echo "</table>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<th align=right></th>";
  echo "<td><input type='submit' value='Submit'></td>";
  echo "</tr>";  
  echo "</table>";
  echo "<input type='hidden' name='group_id' value='34'>";
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
