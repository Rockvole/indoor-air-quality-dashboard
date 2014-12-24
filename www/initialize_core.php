<?php
include 'globals.php';

$name = htmlspecialchars($_GET["name"]);
$core_id = filter_input(INPUT_GET, 'core_id', FILTER_SANITIZE_STRING);
$sensors = filter_input(INPUT_GET, 'sensors', FILTER_VALIDATE_INT);
$tz = filter_input(INPUT_GET, 'tz', FILTER_SANITIZE_STRING);

if(strlen($name)<=0) exit("Must specify name parameter");
if(strlen($core_id)<=0) exit("Must specify core_id parameter");
if(strlen($sensors)<=0) exit("Must specify sensors parameter");
if(strlen($tz)<=0) exit("Must specify tz parameter");

if(!date_default_timezone_set($tz)) {
  exit("Must specify valid php tz");	
}
$conn=mysqli_connect("", "", "", $db_name);

// Check connection
if (mysqli_connect_errno()) {
  echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
} else {
	
  $sql = "INSERT into cores (core_name, name, sensors, tz) VALUES ('$core_id', '$name', '$sensors', '$tz')";
  if(!mysqli_query($conn,$sql)) {
	  exit('Error: '.mysqli_error($conn));
  }
  echo 'Welcome ' . $name . ' ('.$core_id . ') !';
  
  mysqli_close($conn);
}

?>
