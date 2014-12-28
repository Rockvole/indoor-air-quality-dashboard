<?php
  define('TTF_DIR', '/usr/share/fonts/truetype/msttcorefonts/');
  $param_date_format='Y-m-d';
  $user_date_format='l, F jS Y H:i';
  $db_name = 'iaq';
  
  if(isset($_GET["id"])) {
    $conn=mysqli_connect("", "", "", $db_name);
    if (mysqli_connect_errno()) {
      exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    }
    $id = htmlspecialchars($_GET["id"]);  
    $sensor_type_name=get_sensor_type_name($id); 
  }
  if(!isset($_GET["size"])) $size=1;
  else $size = htmlspecialchars($_GET["size"]);
  
  // ------------------------------------------------------------------- FUNCTIONS
  function get_sensor_type_name($id) {
    global $conn;
    global $user_timezone;
    global $group_name;
    global $sensor_temp;
    global $sensor_dust;
    global $sensor_sewer;
    global $sensor_hcho;
    global $sensor_count;
    
    $result=mysqli_query($conn,"SELECT * FROM groups WHERE id=$id");
    $row = mysqli_fetch_array($result);
    $user_timezone=$row['tz'];
    $group_name=$row['name'];  
    $sensor_temp=$row['temp_hum'];
    $sensor_dust=$row['dust'];  
    $sensor_sewer=$row['sewer'];  
    $sensor_hcho=$row['hcho']; 
    $sensor_count=0;
    if(isset($sensor_temp)) $sensor_count++;
    if(isset($sensor_dust)) $sensor_count++; 
    if(isset($sensor_sewer)) $sensor_count++;
    if(isset($sensor_hcho)) $sensor_count++;
   
    if($sensor_count==1) {
      if(isset($sensor_temp)) return "Temperature & Humidity";
      if(isset($sensor_dust)) return "Dust";
      if(isset($sensor_sewer)) return "Sewer";
      if(isset($sensor_hcho)) return "Formaldehyde";
      exit("Unknown sensor type: $sensor_type");
    } else {
      return "Indoor Air Quality";
    }
  }
?>
