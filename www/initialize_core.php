<?php
include 'globals.php';

$name = htmlspecialchars($_GET["name"]);
$tz = filter_input(INPUT_GET, 'tz', FILTER_SANITIZE_STRING);
$temp_hum = filter_input(INPUT_GET, 'temp_hum', FILTER_VALIDATE_INT);
$dust = filter_input(INPUT_GET, 'dust', FILTER_VALIDATE_INT);
$sewer = filter_input(INPUT_GET, 'sewer', FILTER_VALIDATE_INT);
$hcho = filter_input(INPUT_GET, 'hcho', FILTER_VALIDATE_INT);
$co = filter_input(INPUT_GET, 'co', FILTER_VALIDATE_INT);

if(strlen($name)<=0) exit("Must specify name parameter");
if(strlen($tz)<=0) exit("Must specify tz parameter");
if(strlen($temp_hum)<=0) exit("Must specify temp_hum parameter");
if(strlen($dust)<=0) exit("Must specify dust parameter");
if(strlen($sewer)<=0) exit("Must specify sewer parameter");
if(strlen($hcho)<=0) exit("Must specify formaldehyde parameter");
if(strlen(co)<=0) exit("Must specify carbon monoxide parameter");

if(!date_default_timezone_set($tz)) {
  exit("Must specify valid php TimeZone");	
}
$conn=mysqli_connect("", $db_user, $db_pass, $db_name);

// Check connection
if (mysqli_connect_errno()) {
  echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
} else {
	
  $sql = "INSERT into groups (name, tz) VALUES ('$name', '$tz')";
  if(!mysqli_query($conn,$sql)) {
	  exit('Error: '.mysqli_error($conn));
  }
  echo "Welcome ".$name." !<br/>\n";
  
  for($sensor=0;$sensor<5;$sensor++) {
    $core_id = filter_input(INPUT_GET, "core_id_$sensor", FILTER_SANITIZE_STRING);
    echo "Process Core Id: $core_id<br/>\n";
    if(strlen($core_id)<=0) continue;
    if($temp_hum!=$sensor && $dust!=$sensor && $sewer!=$sensor && $hcho!=$sensor) 
    {
      echo "Error: No Sensor Selected for Core Id: $core_id<br/>\n";
      continue;
    }
    $sql = "INSERT into cores (core_id) VALUES ('$core_id')";  
    if(!mysqli_query($conn,$sql)) {
      echo "Error: ".mysqli_error($conn)."<br/>\n";
      continue;
    }    
    if($result=mysqli_query($conn,"SELECT id from cores WHERE core_id='$core_id'"))
    {
      $row = mysqli_fetch_array($result);  
      $id=$row['id'];
      if($temp_hum==$sensor) {
	    $sql = "UPDATE groups SET temp_hum=$id WHERE name='$name'";
	    $result=mysqli_query($conn,$sql);
      }
      if($dust==$sensor) {
	    $sql = "UPDATE groups SET dust=$id WHERE name='$name'";
	    $result=mysqli_query($conn,$sql);
      }
      if($sewer==$sensor) {
	    $sql = "UPDATE groups SET sewer=$id WHERE name='$name'";
	    $result=mysqli_query($conn,$sql);
      }
      if($hcho==$sensor) {
	    $sql = "UPDATE groups SET hcho=$id WHERE name='$name'";
	    $result=mysqli_query($conn,$sql);
      }
      if($co==$sensor) {
	    $sql = "UPDATE groups SET co=$id WHERE name='$name'";
	    $result=mysqli_query($conn,$sql);
      }
    }
  }
  mysqli_close($conn);
}

?>
