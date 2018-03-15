<?php
  require_once ("Carbon/Carbon.php");
  use Carbon\Carbon;
  define('TTF_DIR', '/usr/share/fonts/truetype/msttcorefonts/');
  $HUMIDITY_MAX[0] = 70;
  $HUMIDITY_MAX[1] = 100;
  $HUMIDITY_OK     = 60;
  $HUMIDITY_GOOD   = 40;  
  $HUMIDITY_MIN[0] = 30;
  $HUMIDITY_MIN[1] = 0;
  
  $TEMPERATURE_MAX[0] = 25;
  $TEMPERATURE_MAX[1] = 30;
  $TEMPERATURE_MIN[0] = 10;
  $TEMPERATURE_MIN[1] = 5;
  
  $DUST_MAX        = 60;
  $DUST_OK         = 30;
  $DUST_GOOD       = 20;  
  $DUST_MIN        = 0;
  
  $SEWER_MAX[0]    = 600;
  $SEWER_MAX[1]    = 2000;
  $SEWER_OK        = 400;
  $SEWER_GOOD      = 200;
  $SEWER_MIN       = 0;
  
  $HCHO_MAX        = 30;
  $HCHO_OK         = 20;
  $HCHO_GOOD       = 10;
  $HCHO_MIN        = 0;
  
  $CO_MAX          = 30;
  $CO_OK           = 20;
  $CO_GOOD         = 10;
  $CO_MIN          = 0;
  
  $param_date_format='Y-m-d';
  $user_date_format='l, F jS Y H:i';
  $db_user = 'php_iaq';
  $db_pass = 'php_iaq';
  $db_name = 'iaq';
  
  $conn=mysqli_connect("", $db_user, $db_pass, $db_name);
  if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
  }  
  
  if(isset($_GET["id"])) {
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
    global $sensor_co;
    global $sensor_count;
    global $sensor_type;
    
    $result=mysqli_query($conn,"SELECT * FROM groups WHERE id=$id");
    $row = mysqli_fetch_array($result);
    $user_timezone=$row['tz'];
    $group_name=$row['name'];  
    $sensor_temp=$row['temp_hum'];
    $sensor_dust=$row['dust'];  
    $sensor_sewer=$row['sewer'];  
    $sensor_hcho=$row['hcho']; 
    $sensor_co=$row['co']; 
    $sensor_count=0;
    if(isset($sensor_temp)) $sensor_count++;
    if(isset($sensor_dust)) $sensor_count++; 
    if(isset($sensor_sewer)) $sensor_count++;
    if(isset($sensor_hcho)) $sensor_count++;
    if(isset($sensor_co)) $sensor_count++;
   
    if($sensor_count==1) {
      if(isset($sensor_temp)) {
	    $sensor_type=0;
	    return "Temperature & Humidity";
      }
      if(isset($sensor_dust)) {
	    $sensor_type=2;
	    return "Dust";
      }
      if(isset($sensor_sewer)) {
	    $sensor_type=3;
	    return "VOC's / Sewer";
      }
      if(isset($sensor_hcho)) {
	    $sensor_type=4;
	    return "Formaldehyde";
      }
      if(isset($sensorco)) {
	    $sensor_type=5;
	    return "Carbon Monoxide";
      }
      exit("Unknown sensor type: $sensor_type");
    } else {
      return "Indoor Air Quality";
    }
  }
  
  function get_current_geographical($ts,$id) {
    global $conn;
    global $zoom_temp_hum;
    global $zoom_sewer;
    
    $result=mysqli_query($conn,"SELECT * FROM geographical WHERE group_id=$id AND ts = ".
	        	       "(SELECT MAX(ts) as ts from geographical WHERE group_id=$id AND ts <= $ts )");
    $row=mysqli_fetch_array($result);
    $zoom_temp_hum=$row['zoom_temp_hum'];
    $zoom_sewer=$row['zoom_sewer'];
    return $row;
  }
  
  function get_ts_today($start_date_param,$direction_param) {
    global $conn;
    global $param_date_format;
    global $id;
    
    if(strlen($start_date_param)<=0) { // get latest available date as user got here fresh
	$result=mysqli_query($conn,"SELECT MAX(ts) as ts from readings WHERE group_id=$id");
    } else if(strcmp($direction_param, "next")==0) { // Next button pressed
	$dt = Carbon::createFromFormat($param_date_format, $start_date_param);
	$dt_utc = $dt->startOfDay()->format('U');
	$result=mysqli_query($conn,"SELECT MIN(ts) as ts from readings WHERE group_id=$id and ts > $dt_utc");
    } else { // previous button pressed   
	$dt = Carbon::createFromFormat($param_date_format, $start_date_param);
	$dt_utc = $dt->endOfDay()->format('U');
	$result=mysqli_query($conn,"SELECT MAX(ts) as ts from readings WHERE group_id=$id and ts < $dt_utc");
    }
    if(mysqli_errno()) {
      exit('Error: '.mysqli_error($conn));
    }
    
    $today_ts=NULL;    
    $row = mysqli_fetch_array($result);
    if(isset($row['ts'])) {
      $today_ts=$row['ts'];
    }
    mysqli_free_result($result);
    return $today_ts;
  }
?>
