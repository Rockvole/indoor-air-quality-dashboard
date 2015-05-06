<?php
  require_once ("Carbon/Carbon.php");
  use Carbon\Carbon;
  define('TTF_DIR', '/usr/share/fonts/truetype/msttcorefonts/');
  $MAX_RANGE_HUMIDITY      = 100;
  $MAX_RANGE_OK_HUMIDITY   = 60;
  $MAX_RANGE_GOOD_HUMIDITY = 40;  
  $MAX_RANGE_DUST          = 4500;
  $MAX_RANGE_OK_DUST       = 3000;
  $MAX_RANGE_GOOD_DUST     = 1500;  
  $MAX_RANGE_SEWER         = 600;
  $MAX_RANGE_OK_SEWER      = 400;
  $MAX_RANGE_GOOD_SEWER    = 200;
  $MAX_RANGE_HCHO          = 90;
  $MAX_RANGE_OK_HCHO       = 60;
  $MAX_RANGE_GOOD_HCHO     = 30;
  $param_date_format='Y-m-d';
  $user_date_format='l, F jS Y H:i';
  $db_name = 'iaq';
  
  $conn=mysqli_connect("", "", "", $db_name);
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
    $sensor_count=0;
    if(isset($sensor_temp)) $sensor_count++;
    if(isset($sensor_dust)) $sensor_count++; 
    if(isset($sensor_sewer)) $sensor_count++;
    if(isset($sensor_hcho)) $sensor_count++;
   
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
      exit("Unknown sensor type: $sensor_type");
    } else {
      return "Indoor Air Quality";
    }
  }
  
  function get_current_geographical($ts) {
    global $id;
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
    if(mysql_errno()) {
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
