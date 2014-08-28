<?php
include 'globals.php';

if(!isset($_GET["core_id"])) exit("Must specify core_id parameter");
if(!isset($_GET["name"])) exit("Must specify name parameter");
if(!isset($_GET["tz"])) exit("Must specify tz parameter");

$core_id = htmlspecialchars($_GET["core_id"]);
$name = htmlspecialchars($_GET["name"]);
$tz = htmlspecialchars($_GET["tz"]);

if(strlen($core_id)<=0) exit("Must specify core_id parameter");
if(strlen($name)<=0) exit("Must specify name parameter");
if(strlen($tz)<=0) exit("Must specify tz parameter");

if(!date_default_timezone_set($tz)) {
  exit("Must specify valid php tz");	
}
$conn=mysqli_connect("", "", "", $db_name);

// Check connection
if (mysqli_connect_errno()) {
  echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
} else {
	
  $sql = "INSERT into cores (core_name, name, tz) VALUES ('$core_id', '$name', '$tz')";
  if(!mysqli_query($conn,$sql)) {
	  exit('Error: '.mysqli_error($conn));
  }
  echo 'Welcome ' . $name . ' ('.$core_id . ') !';
  
  mysqli_close($conn);
}

?>
